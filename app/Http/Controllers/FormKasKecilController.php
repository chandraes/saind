<?php

namespace App\Http\Controllers;

use App\Models\KasKecil;
use App\Models\Rekening;
use App\Models\KasBesar;
use App\Models\GroupWa;
use Illuminate\Http\Request;
use App\Services\WaStatus;
use Illuminate\Support\Facades\DB;
use App\Services\StarSender;

class FormKasKecilController extends Controller
{
    public function masuk()
    {
        $nomor = KasKecil::latest()->first();

        if($nomor == null){
            $nomor = 1;
        }else{
            $nomor = $nomor->nomor_kode_kas_kecil + 1;
        }

        $rekening = Rekening::where('untuk', 'kas-kecil')->first();

        return view('billing.kas-kecil.masuk', [
            'nomor' => $nomor,
            'rekening' => $rekening,
        ]);
    }

    public function masuk_store()
    {
        $kk = KasKecil::latest()->first();
        $kb = KasBesar::latest()->first();
        $rekening = Rekening::where('untuk', 'kas-kecil')->first();

        if ($kb == null || $kb->saldo < 1000000) {
            return redirect()->back()->with('error', 'Saldo Kas Besar Tidak Cukup');
        }

        if($kk == null){
            $data['nomor_kode_kas_kecil'] = 1;
            $data['saldo'] = 1000000;
        }else{
            $data['nomor_kode_kas_kecil']= $kk->nomor_kode_kas_kecil + 1;
            $data['saldo'] = $kk->saldo + 1000000;
        }

        $data['tanggal'] = date('Y-m-d');
        $data['uraian'] = 'Permintaan Dana';
        $data['nominal_transaksi'] = 1000000;
        // make $rekening->nama_rekening max 15 char
        $data['transfer_ke'] = substr($rekening->nama_rekening, 0, 15);
        $data['bank'] = $rekening->nama_bank;
        $data['no_rekening'] = $rekening->nomor_rekening;

        DB::transaction(function () use ($data, $kb) {
            $data['jenis_transaksi_id'] = 1;
            KasKecil::create($data);

            $data['saldo'] = $kb->saldo - 1000000;
            $data['jenis_transaksi_id'] = 2;
            $data['nominal_transaksi'] = 1000000;
            $data['modal_investor_terakhir'] = $kb->modal_investor_terakhir;


            KasBesar::create($data);

        });
        $group = GroupWa::where('untuk', 'kas-kecil')->first();
        $pesan =    "==========================\n".
                    "*Form Permintaan Kas Kecil*\n".
                    "==========================\n\n".
                    "KK".sprintf("%02d",$data['nomor_kode_kas_kecil'])."\n".
                    "Nilai : Rp. 1.000.000,-\n\n".
                    "Ditransfer ke rek:\n\n".
                    "Bank     : ".$data['bank']."\n".
                    "Nama    : ".$data['transfer_ke']."\n".
                    "No. Rek : ".$data['no_rekening']."\n\n".
                    "==========================\n\n".
                    "Terima kasih 🙏🙏🙏\n";
        $send = new StarSender($group->nama_group, $pesan);
        $res = $send->sendGroup();

        return redirect()->route('billing.index')->with('success', 'Data Berhasil Ditambahkan');

    }

    public function keluar()
    {
        return view('billing.kas-kecil.keluar');
    }

    public function keluar_store(Request $request)
    {
        $data = $request->validate([
            'nominal_transaksi' => 'required',
            'uraian' => 'required',
            'tipe' => 'required',
            'transfer_ke' => 'nullable',
            'bank' => 'nullable',
            'no_rekening' => 'nullable',
        ]);

        $data['tanggal'] = date('Y-m-d');
        $data['nominal_transaksi'] = str_replace('.', '', $data['nominal_transaksi']);

        $kk = KasKecil::latest()->first();

        if($kk == null || $kk->saldo < $data['nominal_transaksi']){
            return redirect()->back()->with('error', 'Saldo Kas Kecil Tidak Cukup');
        }

        $data['saldo'] = $kk->saldo - $data['nominal_transaksi'];
        $data['jenis_transaksi_id'] = 2;

        if($data['tipe'] == '1'){
            $data['transfer_ke'] = 'Cash';
            unset($data['bank']);
            unset($data['no_rekening']);
        } elseif($data['tipe'] == '2') {
            $data['transfer_ke'] = substr($data['transfer_ke'], 0, 15);
        }

        unset($data['tipe']);

        $store = KasKecil::create($data);

        // check if store success

        if(!$store){
            return redirect()->back()->with('error', 'Data gagal disimpan');
        }

        return redirect()->route('billing.index')->with('success', 'Data berhasil disimpan');

    }
}
