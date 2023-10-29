<?php

namespace App\Http\Controllers;

use App\Models\KasUangJalan;
use App\Models\KasBesar;
use App\Models\Rekening;
use App\Models\GroupWa;
use App\Models\Vehicle;
use App\Models\Vendor;
use App\Models\Customer;
use App\Models\VendorUangJalan;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\StarSender;

class FormKasUangJalanController extends Controller
{
    public function masuk()
    {
        $nomor = KasUangJalan::whereNotNull('nomor_kode_kas_uang_jalan')->latest()->first();

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

        $kuj = KasUangJalan::latest()->orderBy('id', 'desc')->first();
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
        $data['transfer_ke'] = substr($rekening->nama_rekening, 0, 15);
        $data['bank'] = $rekening->nama_bank;
        $data['no_rekening'] = $rekening->nomor_rekening;


        $store = KasUangJalan::create($data);

        $data['saldo'] = $kb->saldo - $data['nominal_transaksi'];
        $data['jenis_transaksi_id'] = 2;
        $data['modal_investor_terakhir'] = $kb->modal_investor_terakhir;

        $store2 = KasBesar::create($data);

        $group = GroupWa::where('untuk', 'kas-besar')->first();
        $pesan =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Form Permintaan Kas Uang Jalan*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    "*KUJ".sprintf("%02d",$data['nomor_kode_kas_uang_jalan'])."*\n\n".
                    "Nilai : *Rp. ".number_format($data['nominal_transaksi'], 0, ',', '.').",-*\n\n".
                    "Ditransfer ke rek:\n\n".
                    "Bank      : ".$data['bank']."\n".
                    "Nama    : ".$data['transfer_ke']."\n".
                    "No. Rek : ".$data['no_rekening']."\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Besar : \n".
                    "Rp. ".number_format($store2->saldo, 0, ',', '.')."\n\n".
                    "Sisa Saldo Kas Uang Jalan : \n".
                    "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                    "Terima kasih 🙏🙏🙏\n";
        $send = new StarSender($group->nama_group, $pesan);
        $res = $send->sendGroup();

        return redirect()->route('billing.index')->with('success', 'Data Berhasil Ditambahkan');

    }

    public function keluar()
    {
        $nomor = KasUangJalan::whereNotNull('nomor_uang_jalan')->latest()->first();
        $vehicle = Vehicle::where('status', 'aktif')->get();
        $customer = Customer::where('status', 1)->get();

        if($nomor == null){
            $nomor = 1;
        }else{
            $nomor = $nomor->nomor_uang_jalan + 1;
        }

        return view('billing.kas-uang-jalan.keluar', [
            'nomor' => $nomor,
            'vehicle' => $vehicle,
            'customer' => $customer,
        ]);
    }

    public function get_vendor(Request $request)
    {
        $vehicle = Vehicle::join('vendors', 'vendors.id', 'vehicles.vendor_id')->find($request->id);
        $data = $vehicle;
        return response()->json($data);
    }

    public function get_rute(Request $request)
    {
        $customer = Customer::find($request->id);
        $data = $customer->rute;
        return response()->json($data);
    }

    public function get_uang_jalan(Request $request)
    {
        $uang_jalan = VendorUangJalan::where('vendor_id', $request->vendor_id)
                        ->where('rute_id', $request->rute_id)
                        ->first();

        $data = $uang_jalan;

        return response()->json($data);
    }

    public function keluar_store(Request $request)
    {

        // dd($request->all());
        $data = $request->validate([
            'customer_id' => 'required',
            'vehicle_id' => 'required',
            'rute_id' => 'required',
            'p_vendor' => 'required',
            'nominal_transaksi' => 'required',
            'transfer_ke' => 'required',
            'bank' => 'required',
            'no_rekening' => 'required',
        ]);

        $data['nominal_transaksi'] = str_replace('.', '', $data['nominal_transaksi']);
        $data['jenis_transaksi_id'] = 2;
        $data['tanggal'] = date('Y-m-d');
        $data['vendor_id'] = $data['p_vendor'];

        $nomor = KasUangJalan::whereNotNull('nomor_uang_jalan')->latest()->orderBy('id', 'desc')->first();

        if($nomor == null){
            $data['nomor_uang_jalan'] = 1;
        }else{
            $data['nomor_uang_jalan'] = $nomor->nomor_uang_jalan + 1;
        }

        $last = KasUangJalan::latest()->orderBy('id', 'desc')->first();

        if($last->saldo < $data['nominal_transaksi'] || $last == null){
            return redirect()->back()->with('error', 'Saldo Kas Uang Jalan Tidak Cukup');
        } else {
            $data['saldo'] = $last->saldo - $data['nominal_transaksi'];
        }


        $store = KasUangJalan::create($data);
        $transaksi['kas_uang_jalan_id'] = $store->id;
        Transaksi::create($transaksi);
        Vehicle::find($data['vehicle_id'])->update(['status' => 'proses']);

        $group = GroupWa::where('untuk', 'kas-uang-jalan')->first();
        $pesan =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Form Pengeluaran Uang Jalan*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    "*UJ".sprintf("%02d",$data['nomor_uang_jalan'])."*\n\n".
                    "Nomor Lambung : ".Vehicle::find($data['vehicle_id'])->nomor_lambung."\n".
                    "Vendor : ".$store->vendor->nama."\n\n".
                    "Tambang : ".$store->customer->singkatan."\n".
                    "Rute : ".$store->rute->nama."\n\n".
                    "Nilai :  *Rp. ".number_format($data['nominal_transaksi'], 0, ',', '.').",-*\n\n".
                    "Ditransfer ke rek:\n\n".
                    "Bank     : ".$data['bank']."\n".
                    "Nama    : ".$data['transfer_ke']."\n".
                    "No. Rek : ".$data['no_rekening']."\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Uang Jalan : \n".
                    "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                    "Terima kasih 🙏🙏🙏\n";
        $send = new StarSender($group->nama_group, $pesan);
        $res = $send->sendGroup();

        return redirect()->route('billing.index')->with('success', 'Data Berhasil Ditambahkan');


    }
}
