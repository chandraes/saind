<?php

namespace App\Http\Controllers;

use App\Models\KasBesar;
use App\Models\Rekening;
use Illuminate\Http\Request;
use App\Services\StarSender;
use App\Models\GroupWa;

class FormLainController extends Controller
{
    public function masuk()
    {
        $rekening = Rekening::where('untuk', 'kas-besar')->first();

        return view('billing.lain-lain.masuk', [
            'rekening' => $rekening,
        ]);
    }

    public function masuk_store(Request $request)
    {
        $data = $request->validate([
            'uraian' => 'required',
            'nominal_transaksi' => 'required',
        ]);

        $rekening = Rekening::where('untuk', 'kas-besar')->first();

        $data['transfer_ke'] = substr($rekening->nama_rekening, 0, 15);
        $data['no_rekening'] = $rekening->nomor_rekening;
        $data['bank'] = $rekening->nama_bank;

        $data['nominal_transaksi'] = str_replace('.', '', $data['nominal_transaksi']);
        $data['jenis_transaksi_id'] = 1;
        $data['tanggal'] = date('Y-m-d');
        $data['lain_lain'] = 1;

         // Saldo terakhir
        $last = KasBesar::latest()->orderBy('id', 'desc')->first();
        if($last == null){
            $data['modal_investor_terakhir']= 0;
            $data['saldo'] = $data['nominal_transaksi'];
        }else{
            $data['saldo'] = $last->saldo + $data['nominal_transaksi'];
            $data['modal_investor_terakhir']= $last->modal_investor_terakhir;
        }

        $db = new KasBesar;
        $store = $db->create($data);

         // check if store success
         if(!$store){
            return redirect()->back()->with('error', 'Data gagal disimpan');
        }

        $dbWa = new GroupWa();

        // $profit = $db->calculateProfitBulanan(date('m'), date('Y'));

        $group = $dbWa->where('untuk', 'kas-besar')->first();
        $pesan ="🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                "*Form Lain2 (Dana Masuk)*\n".
                 "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                 "Uraian :  ".$data['uraian']."\n".
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
                //  "Profit Bersih: \n".
                //     "Rp. ".$profit."\n\n".
                "Terima kasih 🙏🙏🙏\n";

        $send = $dbWa->sendWa($group->nama_group, $pesan);

        return redirect()->route('billing.index')->with('success', 'Data berhasil disimpan');
    }

    public function keluar()
    {
        return view('billing.lain-lain.keluar');
    }

    public function keluar_store(Request $request)
    {
        $data = $request->validate([
            'uraian' => 'required',
            'nominal_transaksi' => 'required',
            'transfer_ke' => 'required',
            'bank' => 'required',
            'no_rekening' => 'required',
        ]);

        $data['transfer_ke'] = substr($data['transfer_ke'], 0, 15);

        $data['nominal_transaksi'] = str_replace('.', '', $data['nominal_transaksi']);
        $data['jenis_transaksi_id'] = 2;
        $data['tanggal'] = date('Y-m-d');

        $data['lain_lain'] = 1;

         // Saldo terakhir
        $last = KasBesar::latest()->orderBy('id', 'desc')->first();

        if($last == null){
            $data['modal_investor_terakhir']= 0;
            $data['saldo'] = $data['nominal_transaksi'];
        }else{

            if ($last->saldo < $data['nominal_transaksi']) {
                return redirect()->back()->with('error', 'Saldo tidak cukup');
            }

            $data['saldo'] = $last->saldo - $data['nominal_transaksi'];
            $data['modal_investor_terakhir']= $last->modal_investor_terakhir;
        }

        $db = new KasBesar;

        $store = $db->create($data);

         // check if store success
         if(!$store){
            return redirect()->back()->with('error', 'Data gagal disimpan');
        }

        $dbWa = new GroupWa();

        // $profit = $db->calculateProfitBulanan(date('m'), date('Y'));

        $group = $dbWa->where('untuk', 'kas-besar')->first();
        $pesan ="🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                "*Form Lain2 (Dana Keluar)*\n".
                 "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                 "Uraian :  ".$data['uraian']."\n".
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
                //  "Profit Bersih: \n".
                //     "Rp. ".$profit."\n\n".
                "Terima kasih 🙏🙏🙏\n";

        $send = $dbWa->sendWa($group->nama_group, $pesan);

        return redirect()->route('billing.index')->with('success', 'Data berhasil disimpan');
    }
}
