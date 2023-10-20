<?php

namespace App\Http\Controllers;

use App\Models\KasBesar;
use App\Models\Rekening;
use App\Models\GroupWa;
use Illuminate\Http\Request;
use App\Services\StarSender;

class FormKasBesarController extends Controller
{
    public function masuk()
    {
        $nomor = KasBesar::whereNotNull('nomor_kode_deposit')->latest()->first();

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

        $data['uraian'] = 'Deposit';
        $rekening = Rekening::where('untuk', 'kas-besar')->first();

        $data['transfer_ke'] = substr($rekening->nama_rekening, 0, 15);
        $data['no_rekening'] = $rekening->nomor_rekening;
        $data['bank'] = $rekening->nama_bank;

        // Nomor Kode Kas Besar Terakhir
        $lastNomor = KasBesar::whereNotNull('nomor_kode_deposit')->latest()->first();

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
        $last = KasBesar::latest()->first();
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

        $group = GroupWa::where('untuk', 'kas-besar')->first();
        $pesan ="🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                "*Form Permintaan Deposit*\n".
                 "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                 "D".sprintf("%02d",$data['nomor_kode_deposit'])."\n".
                 "Nilai :  Rp. ".number_format($data['nominal_transaksi'], 0, ',', '.')."\n\n".
                 "Ditransfer ke rek:\n\n".
                "Bank     : ".$data['bank']."\n".
                "Nama    : ".$data['transfer_ke']."\n".
                "No. Rek : ".$data['no_rekening']."\n\n".
                "==========================\n".
                "Sisa Saldo Kas Besar : \n".
                "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                "Total Modal Investor : \n".
                "Rp. ".number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".

                "Terima kasih 🙏🙏🙏\n";
        $send = new StarSender($group->nama_group, $pesan);
        $res = $send->sendGroup();

        return redirect()->route('billing.index')->with('success', 'Data berhasil disimpan');
    }

    public function keluar()
    {


        $rekening = Rekening::where('untuk', 'kas-besar')->first();

        return view('billing.kas-besar.keluar');
    }

    public function keluar_store(Request $request)
    {
        $data = $request->validate([
            'transfer_ke' => 'required',
            'bank' => 'required',
            'no_rekening' => 'required',
            'nominal_transaksi' => 'required',
        ]);

        $data['uraian'] = 'Withdraw';
        $data['nominal_transaksi'] = str_replace('.', '', $data['nominal_transaksi']);
        $data['jenis_transaksi_id'] = 2;
        $data['tanggal'] = date('Y-m-d');
        $data['transfer_ke'] = substr($data['transfer_ke'], 0, 15);


        $last = KasBesar::latest()->first();
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
        $group = GroupWa::where('untuk', 'kas-besar')->first();

        $pesan =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Form Pengembalian Deposit*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    "Nilai :  Rp. ".number_format($data['nominal_transaksi'], 0, ',', '.')."\n\n".
                    "Ditransfer ke rek:\n\n".
                    "Bank     : ".$data['bank']."\n".
                    "Nama    : ".$data['transfer_ke']."\n".
                    "No. Rek : ".$data['no_rekening']."\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Besar : \n".
                    "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                    "Total Modal Investor : \n".
                    "Rp. ".number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
                    "Terima kasih 🙏🙏🙏\n";
        $send = new StarSender($group->nama_group, $pesan);
        $res = $send->sendGroup();

        return redirect()->route('billing.index')->with('success', 'Data berhasil disimpan');

    }


}
