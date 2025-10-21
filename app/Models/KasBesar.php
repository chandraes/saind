<?php

namespace App\Models;

use App\Models\db\Kreditor;
use App\Models\Rekap\BungaInvestor;
use App\Services\StarSender;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class KasBesar extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function generateNomorTagihan() : int
    {
        return $this->max('nomor_kode_tagihan') + 1;
    }

    public function lastKasBesar()
    {
        return $this->orderBy('id', 'desc')->first();
    }

    public function jenis_transaksi()
    {
        return $this->belongsTo(JenisTransaksi::class);
    }

    public function getTanggalAttribute($value)
    {
        return date('d-m-Y', strtotime($value));
    }

    public function setTanggalAttribute($value)
    {
        $this->attributes['tanggal'] = date('Y-m-d', strtotime($value));
    }

    public function saldoTerakhir()
    {
        return $this->orderBy('id', 'desc')->first()->saldo ?? 0;
    }

    public function modalInvestorTerakhir()
    {
        return $this->orderBy('id', 'desc')->first()->modal_investor_terakhir ?? 0;
    }

    public function insert_bypass($data)
    {
        $data['tanggal'] = date('Y-m-d');
        $data['nominal_transaksi'] = str_replace('.', '', $data['nominal_transaksi']);

        if($data['jenis_transaksi_id'] == 1){
            $data['saldo'] = $this->lastKasBesar()->saldo + $data['nominal_transaksi'];
        } elseif($data['jenis_transaksi_id'] == 2){

            $data['saldo'] = $this->lastKasBesar()->saldo - $data['nominal_transaksi'];
        }

        $data['transfer_ke'] = '-';
        $data['bank'] = '-';
        $data['no_rekening'] = '-';

        $data['modal_investor_terakhir'] = $this->lastKasBesar()->modal_investor_terakhir;

        $store = $this->create($data);

        return $store;

    }

    public function keluarStore($data)
    {
        $data['nominal_transaksi'] = str_replace('.', '', $data['nominal_transaksi']);

        if($data['nominal_transaksi'] > $this->saldoTerakhir()){
            return [
                'status' => 'error',
                'message' => 'Saldo kas besar tidak cukup!',
            ];
        }

        $data['tanggal'] = date('Y-m-d');
        $data['jenis_transaksi_id'] = 2;
        $data['saldo'] = $this->saldoTerakhir() - $data['nominal_transaksi'];
        $data['transfer_ke'] = substr($data['transfer_ke'], 0, 15);
        $data['modal_investor_terakhir'] = $this->modalInvestorTerakhir();

        $store = $this->create($data);

        return $store;

    }

    public function cost_operational($data)
    {
        $data['cost_operational'] = 1;

        $data['uraian'] = CostOperational::find($data['cost_operational_id'])->nama;

        unset($data['cost_operational_id']);

        $data['nominal_transaksi'] = str_replace('.', '', $data['nominal_transaksi']);
        $data['jenis_transaksi_id'] = 2;
        $data['saldo'] = $this->saldoTerakhir() - $data['nominal_transaksi'];
        $data['transfer_ke'] = substr($data['transfer_ke'], 0, 15);

        if ($this->saldoTerakhir() < $data['nominal_transaksi']) {
            return [
                'status' => 'error',
                'message' => 'Saldo kas besar tidak mencukupi!! Sisa Saldo : Rp. '.number_format($this->saldoTerakhir(), 0, ',', '.'),
            ];
        }

        $data['modal_investor_terakhir'] = $this->modalInvestorTerakhir();
        $data['tanggal'] = date('Y-m-d');

        try {
            DB::beginTransaction();

            $store = $this->create($data);

            $pesan =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                        "*Form Cost Operational*\n".
                        "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                        "Uraian : ".$store->uraian."\n".
                        "Nilai :  *Rp. ".number_format($store->nominal_transaksi, 0, ',', '.')."*\n\n".
                        "Ditransfer ke rek:\n\n".
                        "Bank      : ".$store->bank."\n".
                        "Nama    : ".$store->transfer_ke."\n".
                        "No. Rek : ".$store->no_rekening."\n\n".
                        "==========================\n".
                        "Sisa Saldo Kas Besar : \n".
                        "Rp. ".number_format($this->saldoTerakhir(), 0, ',', '.')."\n\n".
                        "Total Modal Investor : \n".
                        "Rp. ".number_format($this->modalInvestorTerakhir(), 0, ',', '.')."\n\n".
                        "Terima kasih 🙏🙏🙏\n";

            DB::commit();

            $tujuan = GroupWa::where('untuk', 'kas-besar')->first()->nama_group;

            $this->sendWa($tujuan, $pesan);

            return [
                'status' => 'success',
                'message' => 'Berhasil menambahkan data',
            ];

        } catch (\Throwable $th) {

                DB::rollback();

                return [
                    'status' => 'error',
                    'message' => $th->getMessage(),
                ];

        }
    }

    public function cost_operational_masuk($data)
    {
        $data['cost_operational'] = 1;
        $rekening = Rekening::where('untuk', 'kas-besar')->first();
        $data['nominal_transaksi'] = str_replace('.', '', $data['nominal_transaksi']);
        $data['jenis_transaksi_id'] = 1;
        $data['saldo'] = $this->saldoTerakhir() + $data['nominal_transaksi'];

        $data['transfer_ke'] = substr($rekening->nama_rekening, 0, 15);
        $data['bank'] = $rekening->nama_bank;
        $data['no_rekening'] = $rekening->nomor_rekening;

        $data['modal_investor_terakhir'] = $this->modalInvestorTerakhir();
        $data['tanggal'] = date('Y-m-d');
        // dd($data);
        try {
            DB::beginTransaction();

            $store = $this->create($data);

            $pesan =    "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                        "*Form Cost Operational*\n".
                        "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                        "Uraian : ".$store->uraian."\n".
                        "Nilai :  *Rp. ".number_format($store->nominal_transaksi, 0, ',', '.')."*\n\n".
                        "Ditransfer ke rek:\n\n".
                        "Bank      : ".$store->bank."\n".
                        "Nama    : ".$store->transfer_ke."\n".
                        "No. Rek : ".$store->no_rekening."\n\n".
                        "==========================\n".
                        "Sisa Saldo Kas Besar : \n".
                        "Rp. ".number_format($this->saldoTerakhir(), 0, ',', '.')."\n\n".
                        "Total Modal Investor : \n".
                        "Rp. ".number_format($this->modalInvestorTerakhir(), 0, ',', '.')."\n\n".
                        "Terima kasih 🙏🙏🙏\n";

            DB::commit();

            $tujuan = GroupWa::where('untuk', 'kas-besar')->first()->nama_group;

            $this->sendWa($tujuan, $pesan);

            return [
                'status' => 'success',
                'message' => 'Berhasil menambahkan data',
            ];

        } catch (\Throwable $th) {

                DB::rollback();

                return [
                    'status' => 'error',
                    'message' => $th->getMessage(),
                ];

        }
    }

    public function bunga_investor($data)
    {
        $kreditor = Kreditor::find($data['kreditor_id']);
        $data['nominal'] = str_replace('.', '', $data['nominal_transaksi']);
        $data['pph'] = $kreditor->apa_pph == 1 ? $data['nominal'] * 0.15 : 0;
        $data['total'] = $data['nominal'] - $data['pph'];

        $saldo = $this->saldoTerakhir();
        $pesan = [];

        if($data['total'] > $saldo){
            return [
                'status' => 'error',
                'message' => 'Saldo kas besar tidak mencukupi!! Sisa Saldo : Rp. '.number_format($saldo, 0, ',', '.'),
            ];
        }

        try {
            DB::beginTransaction();

            $storeBunga = BungaInvestor::create([
                'kreditor_id' => $data['kreditor_id'],
                'nominal' => $data['nominal'],
                'pph' => $data['pph'],
                'total' => $data['total'],
            ]);

            $kas = [
                'tanggal' => date('Y-m-d'),
                'uraian' => 'Bunga Kreditur '.$kreditor->nama,
                'jenis_transaksi_id' => 2,
                'nominal_transaksi' => $storeBunga->total,
                'saldo' => $this->saldoTerakhir() - $storeBunga->total,
                'transfer_ke' => substr($data['transfer_ke'], 0, 15),
                'bank' => $data['bank'],
                'no_rekening' => $data['no_rekening'],
                'modal_investor_terakhir' => $this->modalInvestorTerakhir(),

            ];

            $storeKas = $this->create($kas);

            $pesan[] =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                        "*Form Bunga Kreditur*\n".
                        "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                        "Nama Kreditur : ".$kreditor->nama."\n\n".
                        "Nilai      : *Rp. ".number_format($storeBunga->total, 0, ',', '.')."*\n\n".
                        "Ditransfer ke rek:\n\n".
                        "Bank      : ".$storeKas->bank."\n".
                        "Nama    : ".$storeKas->transfer_ke."\n".
                        "No. Rek : ".$storeKas->no_rekening."\n\n".
                        "==========================\n".
                        "Sisa Saldo Kas Besar : \n".
                        "Rp. ".number_format($this->saldoTerakhir(), 0, ',', '.')."\n\n".
                        "Total Modal Investor : \n".
                        "Rp. ".number_format($this->modalInvestorTerakhir(), 0, ',', '.')."\n\n".
                        "Terima kasih 🙏🙏🙏\n";

            if ($kreditor->apa_pph == 1) {

                $kasPph = [
                    'tanggal' => date('Y-m-d'),
                    'uraian' => 'PPH Bunga Kreditur '.$kreditor->nama,
                    'jenis_transaksi_id' => 2,
                    'nominal_transaksi' => $storeBunga->pph,
                    'saldo' => $this->saldoTerakhir() - $storeBunga->pph,
                    'transfer_ke' => substr("Pajak", 0, 15),
                    'bank' => "Pajak",
                    'no_rekening' => "Rekening Pajak",
                    'modal_investor_terakhir' => $this->modalInvestorTerakhir(),

                ];

                $storePph = $this->create($kasPph);

                $pesan[] =   "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                            "*Form PPh Bunga Kreditur*\n".
                            "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                            "Nilai PPH        : *Rp. ".number_format($storePph->nominal_transaksi, 0, ',', '.')."*\n\n".
                            "Ditransfer ke rek:\n\n".
                            "Bank      : ".$storePph->bank."\n".
                            "Nama    : ".$storePph->transfer_ke."\n".
                            "No. Rek : ".$storePph->no_rekening."\n\n".
                            "==========================\n".
                            "Sisa Saldo Kas Besar : \n".
                            "Rp. ".number_format($this->saldoTerakhir(), 0, ',', '.')."\n\n".
                            "Total Modal Investor : \n".
                            "Rp. ".number_format($this->modalInvestorTerakhir(), 0, ',', '.')."\n\n".
                            "Terima kasih 🙏🙏🙏\n";
            }

            DB::commit();

            $tujuan = GroupWa::where('untuk', 'kas-besar')->first()->nama_group;

            foreach ($pesan as $key => $value) {
                $this->sendWa($tujuan, $value);
            }

            return [
                'status' => 'success',
                'message' => 'Berhasil menambahkan data',
            ];

        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();

            return [
                'status' => 'error',
                'message' => "Gagal Menyimpan Data. " . $th->getMessage(),
            ];
        }
    }

    public function calculateProfitBulanan($bulan, $tahun)
    {
        $data = Transaksi::with(['kas_uang_jalan', 'kas_uang_jalan.vehicle', 'kas_uang_jalan.vendor'])
                            ->join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->join('vehicles as v', 'v.id', 'kuj.vehicle_id')
                            ->select('transaksis.*', 'kuj.tanggal as tanggal', 'v.nomor_lambung as nomor_lambung')
                            ->whereMonth('tanggal_bongkar', $bulan)
                            ->whereYear('tanggal_bongkar', $tahun)
                            ->where('transaksis.void', 0)
                            ->get();

        $startDate = Carbon::createFromDate($tahun, $bulan, 1)->startOfDay();
        $endDate = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->endOfDay();

        $invoiceData = InvoiceTagihan::whereBetween('updated_at', [$startDate, $endDate])
                        ->where('lunas', 1)
                        ->select(DB::raw('SUM(penyesuaian) as penyesuaian, SUM(penalty) as penalty'))
                        ->first();

        $penyesuaian = $invoiceData->penyesuaian ?? 0;
        $penalty = $invoiceData->penalty ?? 0;

        $bungaInvestor = BungaInvestor::whereMonth('created_at', $bulan)
                            ->whereYear('created_at', $tahun)
                            ->sum('nominal');

        $pengeluaran_kas_kecil = KasBesar::whereMonth('tanggal', $bulan)
                            ->whereYear('tanggal', $tahun)
                            ->whereNotNull('nomor_kode_kas_kecil')
                            ->sum('nominal_transaksi');

        $coTransactions = $this->whereMonth('tanggal', $bulan)
                            ->whereYear('tanggal', $tahun)
                            ->where('cost_operational', 1)
                            ->whereIn('jenis_transaksi_id', [1, 2])
                            ->get()
                            ->groupBy('jenis_transaksi_id');

        $lainTransactions = $this->whereMonth('tanggal', $bulan)
                            ->whereYear('tanggal', $tahun)
                            ->where('lain_lain', 1)
                            ->whereIn('jenis_transaksi_id', [1, 2])
                            ->get()
                            ->groupBy('jenis_transaksi_id');

        $pengeluaran_lain = $lainTransactions->has(2) ? $lainTransactions[2]->sum('nominal_transaksi') : 0;
        $pemasukan_lain = $lainTransactions->has(1) ? $lainTransactions[1]->sum('nominal_transaksi') : 0;

        $pengeluaran_co = $coTransactions->has(2) ? $coTransactions[2]->sum('nominal_transaksi') : 0;
        $pemasukan_co = $coTransactions->has(1) ? $coTransactions[1]->sum('nominal_transaksi') : 0;

        $total_lain = $pengeluaran_lain - $pemasukan_lain;
        $total_co = $pengeluaran_co - $pemasukan_co;

        $gaji = RekapGaji::where('bulan', $bulan)
                            ->where('tahun', $tahun)
                            ->first();

        $total_gaji_bersih = $gaji ? $gaji->rekap_gaji_detail->sum('pendapatan_bersih') : 0;
        $grand_total_bersih = $data->sum('profit') - ($pengeluaran_kas_kecil+$total_gaji_bersih+$total_co+$bungaInvestor+$penalty+$total_lain) + $penyesuaian;


        return number_format($grand_total_bersih, 0, ',', '.');
    }

    public function sendWa($tujuan, $pesan)
    {
        $storeWa = PesanWa::create([
            'pesan' => $pesan,
            'tujuan' => $tujuan,
            'status' => 0,
        ]);

        $send = new StarSender($tujuan, $pesan);
        $res = $send->sendGroup();

        if ($res == 'true') {
            $storeWa->update(['status' => 1]);
        }

    }


}
