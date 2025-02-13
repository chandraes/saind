<?php

namespace App\Http\Controllers;

use App\Models\KasBesar;
use App\Models\Rekening;
use App\Models\GroupWa;
use App\Models\Legalitas\LegalitasDokumen;
use Illuminate\Http\Request;
use App\Services\StarSender;
use Carbon\Carbon;

class FormKasBesarController extends Controller
{
    public function masuk()
    {
        $nomor = KasBesar::whereNotNull('nomor_kode_deposit')->latest()->orderBy('id', 'desc')->first();

        if($nomor == null){
            $nomor = 1;
        }else{
            $nomor = $nomor->nomor_kode_deposit + 1;
        }

        $rekening = Rekening::where('untuk', 'kas-besar')->first();

        return view('billing.kas-besar.masuk', [
            'nomor' => $nomor,
            'rekening' => $rekening,
        ]);
    }

    public function masuk_store(Request $request)
    {
        $data = $request->validate([
            'nominal_transaksi' => 'required',
        ]);

        $rekening = Rekening::where('untuk', 'kas-besar')->first();

        $data['transfer_ke'] = substr($rekening->nama_rekening, 0, 15);
        $data['no_rekening'] = $rekening->nomor_rekening;
        $data['bank'] = $rekening->nama_bank;

        // Nomor Kode Kas Besar Terakhir
        $lastNomor = KasBesar::whereNotNull('nomor_kode_deposit')->latest()->orderBy('id', 'desc')->first();

        if($lastNomor == null)
        {
            $data['nomor_kode_deposit'] = 1;
        } else {
            $data['nomor_kode_deposit'] = $lastNomor->nomor_kode_deposit + 1;
        }

        $data['nominal_transaksi'] = str_replace('.', '', $data['nominal_transaksi']);
        $data['jenis_transaksi_id'] = 1;
        $data['tanggal'] = date('Y-m-d');
        $data['modal_investor']= -$data['nominal_transaksi'];

        // Saldo terakhir
        $last = KasBesar::latest()->orderBy('id', 'desc')->first();
        if($last == null){
            $data['modal_investor_terakhir']= -$data['nominal_transaksi'];
            $data['saldo'] = $data['nominal_transaksi'];
        }else{
            $data['saldo'] = $last->saldo + $data['nominal_transaksi'];
            $data['modal_investor_terakhir']= $last->modal_investor_terakhir - $data['nominal_transaksi'];
        }

        $store = KasBesar::create($data);

        // check if store success
        if(!$store){
            return redirect()->back()->with('error', 'Data gagal disimpan');
        }

        // check if there is legalitas that 45 day again expired
        $checkLegalitas = LegalitasDokumen::whereNotNull('tanggal_expired')
                        ->where('tanggal_expired', '<', Carbon::now()->addDays(45))->get();

        $addPesan = '';

        if($checkLegalitas->count() > 0){
            $addPesan = "\n==========================\nWARNING : \n";
            $no = 1;
            foreach($checkLegalitas as $legalitas){
                $addPesan .= $no++.". ".$legalitas->nama." - ".date('d-m-Y', strtotime($legalitas->tanggal_expired))."\n";
            }
        }



        $group = GroupWa::where('untuk', 'kas-besar')->first();
        $pesan ="🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                "*Form Permintaan Deposit*\n".
                 "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                 "*D".sprintf("%02d",$data['nomor_kode_deposit'])."*\n\n".
                 "Nilai :  *Rp. ".number_format($data['nominal_transaksi'], 0, ',', '.')."*\n\n".
                 "Ditransfer ke rek:\n\n".
                "Bank     : ".$data['bank']."\n".
                "Nama    : ".$data['transfer_ke']."\n".
                "No. Rek : ".$data['no_rekening']."\n\n".
                "==========================\n".
                "Sisa Saldo Kas Besar : \n".
                "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                "Total Modal Investor : \n".
                "Rp. ".number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
                "Terima kasih 🙏🙏🙏\n".
                $addPesan;

        $send = new StarSender($group->nama_group, $pesan);
        $res = $send->sendGroup();

        return redirect()->route('billing.index')->with('success', 'Data berhasil disimpan');
    }

    public function keluar()
    {


        $rekening = Rekening::where('untuk', 'withdraw')->first();

        return view('billing.kas-besar.keluar', [
            'rekening' => $rekening,
        ]);
    }

    public function keluar_store(Request $request)
    {
        $data = $request->validate([
            'nominal_transaksi' => 'required',
        ]);

        $rekening = Rekening::where('untuk', 'withdraw')->first();

        $data['uraian'] = 'Withdraw';
        $data['nominal_transaksi'] = str_replace('.', '', $data['nominal_transaksi']);
        $data['jenis_transaksi_id'] = 2;
        $data['tanggal'] = date('Y-m-d');
        $data['transfer_ke'] = substr($rekening->nama_rekening, 0, 15);
        $data['no_rekening'] = $rekening->nomor_rekening;
        $data['bank'] = $rekening->nama_bank;


        $last = KasBesar::latest()->orderBy('id', 'desc')->first();
        
        if($last == null){
            $data['saldo'] = 0 - $data['nominal_transaksi'];
            $data['modal_investor'] = $data['nominal_transaksi'];
            $data['modal_investor_terakhir']= $data['nominal_transaksi'];

        }else{
            $data['saldo'] = $last->saldo - $data['nominal_transaksi'];
            $data['modal_investor'] = $data['nominal_transaksi'];
            $data['modal_investor_terakhir']= $last->modal_investor_terakhir + $data['nominal_transaksi'];

            if ($last->saldo < $data['nominal_transaksi']) {
                return redirect()->back()->with('error', 'Saldo tidak cukup');
            }
        }

        $store = KasBesar::create($data);

        // check if store success
        if(!$store){
            return redirect()->back()->with('error', 'Data gagal disimpan');
        }

        $checkLegalitas = LegalitasDokumen::whereNotNull('tanggal_expired')
        ->where('tanggal_expired', '<', Carbon::now()->addDays(45))->get();

        $addPesan = '';

        if($checkLegalitas->count() > 0){
            $addPesan = "\n==========================\nWARNING : \n";
            $no = 1;
            foreach($checkLegalitas as $legalitas){
            $addPesan .= $no++.". ".$legalitas->nama." - ".date('d-m-Y', strtotime($legalitas->tanggal_expired))."\n";
            }
        }

        $dbWa = new GroupWa();
        $group = $dbWa->where('untuk', 'kas-besar')->first();

        $pesan =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Form Pengembalian Deposit*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    "Nilai :  *Rp. ".number_format($data['nominal_transaksi'], 0, ',', '.')."*\n\n".
                    "Ditransfer ke rek:\n\n".
                    "Bank     : ".$data['bank']."\n".
                    "Nama    : ".$data['transfer_ke']."\n".
                    "No. Rek : ".$data['no_rekening']."\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Besar : \n".
                    "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                    "Total Modal Investor : \n".
                    "Rp. ".number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
                    "Terima kasih 🙏🙏🙏\n".
                    $addPesan;

        $send = $dbWa->sendWa($group->nama_group, $pesan);

        return redirect()->route('billing.index')->with('success', 'Data berhasil disimpan');

    }


}
