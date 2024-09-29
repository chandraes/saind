<?php

namespace App\Models;

use App\Models\db\Kreditor;
use App\Models\Rekap\BungaInvestor;
use App\Services\StarSender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class KasBesar extends Model
{
    use HasFactory;
    protected $guarded = [];

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
        $data['pph'] = $kreditor->apa_pph == 1 ? $data['nominal'] * 0.02 : 0;
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
                    'transfer_ke' => substr($data['transfer_ke'], 0, 15),
                    'bank' => $data['bank'],
                    'no_rekening' => $data['no_rekening'],
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

            // DB::commit();

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
