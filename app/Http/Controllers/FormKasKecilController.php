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
        $nomor = KasKecil::whereNotNull('nomor_kode_kas_kecil')->latest()->orderBy('id', 'desc')->first();

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
        $kk = KasKecil::whereNotNull('nomor_kode_kas_kecil')->latest()->orderBy('id', 'desc')->first();
        $kb = KasBesar::latest()->orderBy('id', 'desc')->first();
        $rekening = Rekening::where('untuk', 'kas-kecil')->first();

        if ($kb == null || $kb->saldo < 1000000) {
            return redirect()->back()->with('error', 'Saldo Kas Besar Tidak Cukup');
        }

        if($kk == null){
            $data['nomor_kode_kas_kecil'] = 1;

        }else{
            $data['nomor_kode_kas_kecil']= $kk->nomor_kode_kas_kecil + 1;
        }

        $last = KasKecil::latest()->orderBy('id', 'desc')->first();

        if($last == null){
            $data['saldo'] = 1000000;
        }else{
            $data['saldo'] = $last->saldo + 1000000;
        }

        $data['tanggal'] = date('Y-m-d');

        $data['nominal_transaksi'] = 1000000;
        // make $rekening->nama_rekening max 15 char
        $data['transfer_ke'] = substr($rekening->nama_rekening, 0, 15);
        $data['bank'] = $rekening->nama_bank;
        $data['no_rekening'] = $rekening->nomor_rekening;


        $data['jenis_transaksi_id'] = 1;

        $store = KasKecil::create($data);

        $data['saldo'] = $kb->saldo - 1000000;
        $data['jenis_transaksi_id'] = 2;
        $data['nominal_transaksi'] = 1000000;
        $data['modal_investor_terakhir'] = $kb->modal_investor_terakhir;

        $store2 = KasBesar::create($data);

        $group = GroupWa::where('untuk', 'kas-kecil')->first();
        $pesan =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Form Permintaan Kas Kecil*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    "*KK".sprintf("%02d",$data['nomor_kode_kas_kecil'])."*\n\n".
                    "Nilai : *Rp. 1.000.000,-*\n\n".
                    "Ditransfer ke rek:\n\n".
                    "Bank     : ".$data['bank']."\n".
                    "Nama    : ".$data['transfer_ke']."\n".
                    "No. Rek : ".$data['no_rekening']."\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Besar : \n".
                    "Rp. ".number_format($store2->saldo, 0, ',', '.')."\n\n".
                    "Sisa Saldo Kas Kecil : \n".
                    "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
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

        $kk = KasKecil::latest()->orderBy('id', 'desc')->first();

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

        if(!$store){
            return redirect()->back()->with('error', 'Data gagal disimpan');
        }

        $group = GroupWa::where('untuk', 'team')->first();

        if ($data['transfer_ke'] == 'Cash') {
            $pesan =    "==========================\n".
                        "*Form Pengeluaran Kas Kecil*\n".
                        "==========================\n\n".
                        "Uraian: ".$data['uraian']."\n\n".
                        "Nilai : *Rp. ".number_format($data['nominal_transaksi'])."*\n\n".
                        "Cash\n\n".
                        "==========================\n".
                        "Sisa Saldo Kas Kecil : \n".
                        "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                        "Terima kasih 🙏🙏🙏\n";
        } else {
            $pesan =    "==========================\n".
                        "*Form Pengeluaran Kas Kecil*\n".
                        "==========================\n\n".
                        "Uraian: ".$data['uraian']."\n\n".
                        "Nilai : *Rp. ".number_format($data['nominal_transaksi'])."*\n\n".
                        "Ditransfer ke rek:\n\n".
                        "Bank     : ".$data['bank']."\n".
                        "Nama    : ".$data['transfer_ke']."\n".
                        "No. Rek : ".$data['no_rekening']."\n\n".
                        "==========================\n".
                        "Sisa Saldo Kas Kecil : \n".
                        "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                        "Terima kasih 🙏🙏🙏\n";
        }

        $send = new StarSender($group->nama_group, $pesan);
        $res = $send->sendGroup();

        return redirect()->route('billing.index')->with('success', 'Data berhasil disimpan');

    }

    public function void()
    {
        $month = date('m');
        $year = date('Y');
        $data = KasKecil::where('jenis_transaksi_id', 2)->where('void', 0)->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->get();
        return view('billing.kas-kecil.void', [
            'data' => $data,
        ]);
    }

    public function get_void(Request $request)
    {
        $data = KasKecil::find($request->id);

        return response()->json([
            'data' => $data,
        ]);
    }

    public function void_store(Request $request)
    {
        $data = $request->validate([
            'kas_kecil_id' => 'required',
        ]);

        $kk = KasKecil::find($data['kas_kecil_id']);

        if(!$kk){
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }

        $data['tanggal'] = date('Y-m-d');
        $data['nominal_transaksi'] = $kk->nominal_transaksi;
        $data['jenis_transaksi_id'] = 1;

        $data['uraian'] = 'Void '.$kk->uraian;
        $data['transfer_ke'] = "Void";

        $last = KasKecil::latest()->orderBy('id', 'desc')->first();

        if($last == null){
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }else{
            $data['saldo'] = $last->saldo + $data['nominal_transaksi'];
        }

        unset($data['kas_kecil_id']);

        $store = KasKecil::create($data);

        $kk->update(['void' => 1]);

        if(!$store){
            return redirect()->back()->with('error', 'Data gagal disimpan');
        }

        $group = GroupWa::where('untuk', 'team')->first();

        $pesan =    "==========================\n".
                        "*Form Void Kas Kecil*\n".
                        "==========================\n\n".
                        "Uraian: ".$data['uraian']."\n\n".
                        "Nilai : *Rp. ".number_format($data['nominal_transaksi'])."*\n\n".
                        "==========================\n".
                        "Sisa Saldo Kas Kecil : \n".
                        "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                        "Terima kasih 🙏🙏🙏\n";

        $send = new StarSender($group->nama_group, $pesan);
        $res = $send->sendGroup();

        return redirect()->route('billing.index')->with('success', 'Data berhasil disimpan');


    }

}
