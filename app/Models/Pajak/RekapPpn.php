<?php

namespace App\Models\Pajak;

use App\Models\GroupWa;
use App\Models\KasBesar;
use App\Models\Pajak\PpnKeluaran;
use App\Models\Pajak\PpnMasukan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RekapPpn extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $appends = ['tanggal', 'nf_nominal', 'nf_saldo'];

    public function generateMasukanId()
    {
        $id = $this->max('masukan_id') + 1;
        return $id;
    }

    public function generateKeluaranId()
    {
        $id = $this->max('keluaran_id') + 1;
        return $id;
    }

    public function dataTahun()
    {
        return $this->selectRaw('YEAR(created_at) as tahun')->groupBy('tahun')->get();
    }

    public function rekapByMonth($month, $year)
    {
        return $this->whereMonth('created_at', $month)->whereYear('created_at', $year)->get();
    }

    public function rekapByMonthSebelumnya($month, $year)
    {
        $data = $this->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if (!$data) {
            $data = $this->where('created_at', '<', Carbon::create($year, $month, 1))
                    ->orderBy('id', 'desc')
                    ->first();
        }

        return $data;
    }

    public function rekapMasukanDetail()
    {
        return $this->hasMany(RekapMasukanDetail::class, 'masukan_id', 'masukan_id');
    }

    public function rekapKeluaranDetail()
    {
        return $this->hasMany(RekapKeluaranDetail::class, 'keluaran_id', 'keluaran_id');
    }

    public function getTanggalAttribute()
    {
        return Carbon::parse($this->created_at)->format('d-m-Y');
    }

    public function getNfNominalAttribute()
    {
        return number_format($this->nominal, 0, ',', '.');
    }

    public function getNfSaldoAttribute()
    {
        return number_format($this->saldo, 0, ',', '.');
    }

    public function saldoTerakhir()
    {
        return $this->orderBy('id', 'desc')->first()->saldo ?? 0;
    }

    public function keranjang_masukan_lanjut($penyesuaian = 0)
    {
        $db = new PpnMasukan();

        $data = $db->where('keranjang', 1)->where('selesai', 0)->get();

        $total = $data->sum('nominal') + $penyesuaian;

        try {
            DB::beginTransaction();

            $create = $this->create([
                'masukan_id' => $this->generateMasukanId(),
                'nominal' => $total,
                'saldo' => $this->saldoTerakhir() + $total,
                'penyesuaian' => $penyesuaian,
                'jenis' => 1,
                'uraian' => 'PPN Masukan',
            ]);

            foreach ($data as $item) {

                $create->rekapMasukanDetail()->create([
                    'masukan_id' => $create->masukan_id,
                    'ppn_masukan_id' => $item->id,
                ]);

                $item->update([
                    'selesai' => 1,
                    'keranjang' => 0,
                ]);
            }

            DB::commit();

            return [
                'status' => 'success',
                'message' => 'Berhasil menyimpan data',
            ];

        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();

            return [
                'status' => 'error',
                'message' => 'Gagal menyimpan data. '. $th->getMessage(),
            ];
        }
    }

    public function keranjang_keluaran_lanjut($penyesuaian = 0)
    {
        $db = new PpnKeluaran();

        $data = $db->where('onhold', 0)->where('keranjang', 1)->where('selesai', 0)->get();

        $total = $data->where('dipungut', 1)->sum('nominal') + $penyesuaian;

        $saldo = $this->saldoTerakhir() - $total;


        try {
            DB::beginTransaction();

            $dbKasBesar = new KasBesar();
            $waState = 0;

            $create = $this->create([
                'keluaran_id' => $this->generateKeluaranId(),
                'nominal' => $total,
                'saldo' => $saldo,
                'penyesuaian' => $penyesuaian,
                'jenis' => 0,
                'uraian' => 'PPN Keluaran',
            ]);

            if ($saldo < 0) {

                $nominalKasBesar = abs($saldo);

                $saldoKasBesar = $dbKasBesar->saldoTerakhir();

                if ($saldoKasBesar < $nominalKasBesar) {
                    return [
                        'status' => 'error',
                        'message' => 'Saldo Kas Besar tidak mencukupi',
                    ];
                }

                $store = $dbKasBesar->create([
                    'tanggal' => date('Y-m-d'),
                    'uraian' => 'Pembayaran PPN',
                    'jenis_transaksi_id' => 2,
                    'nominal_transaksi' => $nominalKasBesar,
                    'saldo' => $dbKasBesar->saldoTerakhir() - $nominalKasBesar,
                    'no_rekening' => 'Pajak',
                    'transfer_ke' => 'Pajak',
                    'bank' => 'Pajak',
                    'modal_investor_terakhir' => $dbKasBesar->modalInvestorTerakhir(),
                ]);

                $this->create([
                    'nominal' => $nominalKasBesar,
                    'saldo' => $this->saldoTerakhir() + $nominalKasBesar,
                    'jenis' => 1,
                    'uraian' => 'Kas Besar',
                ]);

                $waState = 1;

                $pesan = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                        "*Form PPN*\n".
                        "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                        "Uraian : ".$store->uraian."\n".
                        "Nilai :  *Rp. ".number_format($store->nominal_transaksi, 0, ',', '.')."*\n\n".
                        "Ditransfer ke rek:\n\n".
                        "Bank      : ".$store->bank."\n".
                        "Nama    : ".$store->transfer_ke."\n".
                        "No. Rek : ".$store->no_rekening."\n\n".
                        "==========================\n".
                        "Sisa Saldo Kas Besar: \n".
                        "Rp. ".number_format($dbKasBesar->saldoTerakhir(), 0, ',', '.')."\n\n".
                        "Total Modal Investor : \n".
                        "Rp. ".number_format($dbKasBesar->modalInvestorTerakhir(), 0, ',', '.')."\n\n".
                        "Terima kasih 🙏🙏🙏\n";

            }

            foreach ($data as $item) {

                $create->rekapKeluaranDetail()->create([
                    'keluaran_id' => $create->keluaran_id,
                    'ppn_keluaran_id' => $item->id,
                ]);

                $item->update([
                    'selesai' => 1,
                    'keranjang' => 0,
                ]);
            }

            DB::commit();

            if ($waState == 1) {
                $tujuan = GroupWa::where('untuk', 'kas-besar')->first()->nama_group;

                $dbKasBesar->sendWa($tujuan, $pesan);
            }

            return [
                'status' => 'success',
                'message' => 'Berhasil menyimpan data',
            ];

        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();

            return [
                'status' => 'error',
                'message' => 'Gagal menyimpan data. '. $th->getMessage(),
            ];
        }
    }
}
