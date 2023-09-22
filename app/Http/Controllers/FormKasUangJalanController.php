<?php

namespace App\Http\Controllers;

use App\Models\KasUangJalan;
use App\Models\KasBesar;
use App\Models\Rekening;
use App\Models\GroupWa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\StarSender;

class FormKasUangJalanController extends Controller
{
    public function masuk()
    {
        $nomor = KasUangJalan::latest()->first();

        if($nomor == null){
            $nomor = 1;
        }else{
            $nomor = $nomor->nomor_kode_kas_uang_jalan + 1;
        }

        $rekening = Rekening::where('untuk', 'kas-uang-jalan')->first();
        return view('billing.kas-uang-jalan.masuk', [
            'nomor' => $nomor,
            'rekening' => $rekening,
        ]);
    }

    public function masuk_store(Request $request)
    {
        $data = $request->validate([
            'nominal_transaksi' => 'required',
        ]);

        $data['nominal_transaksi'] = str_replace('.', '', $data['nominal_transaksi']);

        $kuj = KasUangJalan::latest()->first();
        $kb = KasBesar::latest()->first();
        $rekening = Rekening::where('untuk', 'kas-uang-jalan')->first();

        if ($kb == null || $kb->saldo < $data['nominal_transaksi']) {
            return redirect()->back()->with('error', 'Saldo Kas Besar Tidak Cukup');
        }

        $lastNomor = KasUangJalan::whereNotNull('nomor_kode_kas_uang_jalan')->latest()->first();

        if($lastNomor == null){
            $data['nomor_kode_kas_uang_jalan'] = 1;
        }else{
            $data['nomor_kode_kas_uang_jalan'] = $lastNomor->nomor_kode_kas_uang_jalan + 1;
        }

        if($kuj == null){
            $data['saldo'] = $data['nominal_transaksi'];
        }else{
            $data['saldo'] = $kuj->saldo + $data['nominal_transaksi'];
        }

        $data['tanggal'] = date('Y-m-d');
        $data['jenis_transaksi_id'] = 1;
        $data['uraian'] = 'Permintaan Dana';
        $data['transfer_ke'] = substr($rekening->nama_rekening, 0, 15);
        $data['bank'] = $rekening->nama_bank;
        $data['no_rekening'] = $rekening->nomor_rekening;

        DB::transaction(function () use ($data, $kb) {

            KasUangJalan::create($data);

            $data['saldo'] = $kb->saldo - $data['nominal_transaksi'];
            $data['jenis_transaksi_id'] = 2;
            $data['modal_investor_terakhir'] = $kb->modal_investor_terakhir;

            KasBesar::create($data);
        });
        $group = GroupWa::where('untuk', 'kas-uang-jalan')->first();
        $pesan = "*Form Kas Uang Jalan*\n\n".
                 "Nomor Kode Kas Kecil : KUJ".sprintf("%02d",$data['nomor_kode_kas_uang_jalan'])."\n".
                 "Permintaan Dana Sebesar Rp. ".number_format($data['nominal_transaksi'], 0, ',', '.').",-\n";
        $send = new StarSender($group->nama_group, $pesan);
        $res = $send->sendGroup();

        return redirect()->route('billing.index')->with('success', 'Data Berhasil Ditambahkan');

    }

    public function keluar()
    {
        $nomor = KasUangJalan::whereNotNull('nomor_uang_jalan')->latest()->first();

        if($nomor == null){
            $nomor = 1;
        }else{
            $nomor = $nomor->nomor_kode_kas_uang_jalan + 1;
        }


        return view('billing.kas-uang-jalan.keluar', [
            'nomor' => $nomor,
        ]);
    }

    public function keluar_store(Request $request)
    {

    }
}
