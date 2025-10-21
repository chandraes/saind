<?php

namespace App\Models\Pajak;

use App\Models\GroupWa;
use App\Models\KasBesar;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RekapPphVendor extends Model
{
    use HasFactory;
    protected $guarded = ['id'];


    public function details()
    {
        return $this->hasMany(RekapPphVendorDetail::class);
    }

      public function getTanggalAttribute()
    {
        return Carbon::parse($this->created_at)->format('Y-m-d');
    }

    public function getNfNominalAttribute()
    {
        return number_format($this->nominal, 0, ',', '.');
    }

    public function getNfPenyesuaianAttribute()
    {
        return number_format($this->penyesuaian, 0, ',', '.');
    }

    public function getNfTotalAttribute()
    {
        return number_format($this->total, 0, ',', '.');
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

    public function keranjang_pph_vendor_lanjut($data)
    {
        $db = new PphSimpan();
        $dbKasBesar = new KasBesar();
        $saldoKasBesar = $dbKasBesar->saldoTerakhir();

        $keranjang = $db->where('onhold', 0)->where('keranjang', 1)->where('selesai', 0)->get();

        $total = $keranjang->sum('nominal') + $data['penyesuaian'];

        if ($saldoKasBesar < $total) {
            return [
                'status' => 'error',
                'message' => 'Saldo Kas Besar tidak mencukupi',
            ];
        }

        try {
            DB::beginTransaction();

            $create = $this->create([
                'nominal' => $total,
                'penyesuaian' => $data['penyesuaian'],
                'total' => $total,
                'uraian' => $data['uraian'],
            ]);

            $store = $dbKasBesar->create([
                'tanggal' => date('Y-m-d'),
                'uraian' => $data['uraian'],
                'jenis_transaksi_id' => 2,
                'nominal_transaksi' => $total,
                'saldo' => $dbKasBesar->saldoTerakhir() - $total,
                'no_rekening' => '0218222270',
                'transfer_ke' => 'SUMATERA ALAM',
                'bank' => 'BCA',
                'modal_investor_terakhir' => $dbKasBesar->modalInvestorTerakhir(),
            ]);


            $pesan = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Form PPh*\n".
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



            foreach ($keranjang as $item) {

                $create->details()->create([
                    'pph_simpan_id' => $item->id,
                ]);

                $item->update([
                    'selesai' => 1,
                    'keranjang' => 0,
                ]);
            }

            DB::commit();

            $tujuan = GroupWa::where('untuk', 'kas-besar')->first()->nama_group;

            $dbKasBesar->sendWa($tujuan, $pesan);

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
