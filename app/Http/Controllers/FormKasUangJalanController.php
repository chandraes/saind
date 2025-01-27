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
use Carbon\Carbon;

class FormKasUangJalanController extends Controller
{
    public function masuk()
    {
        $nomor = KasUangJalan::whereNotNull('nomor_kode_kas_uang_jalan')->latest()->orderBy('id', 'desc')->first();

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
        $kb = KasBesar::latest()->orderBy('id', 'desc')->first();
        $rekening = Rekening::where('untuk', 'kas-uang-jalan')->first();

        if ($kb == null || $kb->saldo < $data['nominal_transaksi']) {
            return redirect()->back()->with('error', 'Saldo Kas Besar Tidak Cukup');
        }

        $lastNomor = KasUangJalan::whereNotNull('nomor_kode_kas_uang_jalan')->latest()->orderBy('id', 'desc')->first();

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
        $nomor = KasUangJalan::whereNotNull('nomor_uang_jalan')->latest()->orderBy('id', 'desc')->first();
        $vehicle = Vehicle::where('status', 'aktif')->where('do_count', '<', 2)->get();
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
        $vehicle = Vehicle::join('vendors', 'vendors.id', 'vehicles.vendor_id')
                                ->select('vehicles.*', 'vendors.nama as nama_vendor', 'vendors.id as id_vendor')
                                ->find($request->id);
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

        $vendor = $data['p_vendor'];

        $data['nominal_transaksi'] = str_replace('.', '', $data['nominal_transaksi']);
        $data['transfer_ke'] = substr($data['transfer_ke'], 0, 15);
        $data['jenis_transaksi_id'] = 2;
        $data['tanggal'] = date('Y-m-d');
        $data['vendor_id'] = $vendor;

        $auth = ['admin', 'su'];

        if (!in_array(auth()->user()->role, $auth)) {
            $check = VendorUangJalan::where('vendor_id', $vendor)
                                    ->where('rute_id', $data['rute_id'])
                                    ->first()->hk_uang_jalan ?? 0;
            if($check != $data['nominal_transaksi']){
                return redirect()->back()->with('error', 'Nominal Uang Jalan Tidak Sesuai');
            }
        }

        unset($data['p_vendor']);

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

        // cek lock uj vehicle lalu cek tanggal kimper dan sim
        $dbVehicle = Vehicle::find($data['vehicle_id']);

        if ($dbVehicle->lock_uj == 1) {
            $today = date('Y-m-d');
            $kimperExpired = $dbVehicle->tanggal_kimper < $today;
            $simExpired = $dbVehicle->tanggal_sim < $today;
            $kimperNotSet = is_null($dbVehicle->tanggal_kimper);
            $simNotSet = is_null($dbVehicle->tanggal_sim);

            if ($kimperExpired || $simExpired || $kimperNotSet || $simNotSet) {
                $m = ($kimperExpired || $simExpired) ? 'KIMPER atau SIM sudah kadaluarsa! ' : 'Tanggal kadaluarsa KIMPER atau SIM belum diinput! ';
                return redirect()->back()->with('error', $m);
            }
        }

        try {
            DB::beginTransaction();

            $store = KasUangJalan::create($data);
            $transaksi['kas_uang_jalan_id'] = $store->id;

            Transaksi::create($transaksi);
            Vehicle::find($data['vehicle_id'])->update(['status' => 'proses']);

            DB::commit();
        } catch (\Throwable $th) {
            //throw $th;

            DB::rollback();
            return redirect()->back()->with('error', 'Data Gagal Ditambahkan. '. $th->getMessage());
        }

        $additionalMessage = '';
        $today = Carbon::today();
        $nextMonth = $today->copy()->addMonth();

        $additionalMessage = '';

        // Check if dates are input
        if ($dbVehicle->tanggal_kimper == null) {
            $additionalMessage .= "Tanggal KIMPER belum diinput. \n\n";
        } elseif (Carbon::parse($dbVehicle->tanggal_kimper)->lessThan($today)) {
            $additionalMessage .= 'KIMPER sudah expired sejak ' . Carbon::parse($dbVehicle->tanggal_kimper)->format('d-m-Y') . ".\n\n ";
        } elseif (Carbon::parse($dbVehicle->tanggal_kimper)->lessThanOrEqualTo($nextMonth)) {
            $additionalMessage .= 'KIMPER akan kadaluarsa pada ' . Carbon::parse($dbVehicle->tanggal_kimper)->format('d-m-Y') . ".\n\n ";
        }

        if ($dbVehicle->tanggal_sim == null) {
            $additionalMessage .= 'Tanggal SIM belum diinput. ';
        } elseif (Carbon::parse($dbVehicle->tanggal_sim)->lessThan($today)) {
            $additionalMessage .= 'SIM sudah expired sejak ' . Carbon::parse($dbVehicle->tanggal_sim)->format('d-m-Y') . ".\n\n ";
        } elseif (Carbon::parse($dbVehicle->tanggal_sim)->lessThanOrEqualTo($nextMonth)) {
            $additionalMessage .= 'SIM akan kadaluarsa pada ' . Carbon::parse($dbVehicle->tanggal_sim)->format('d-m-Y') .".\n\n ";
        }

        if ($dbVehicle->tanggal_pajak_stnk == null) {
            $additionalMessage .= 'Tanggal Pajak STNK belum diinput. ';
        } elseif (Carbon::parse($dbVehicle->tanggal_pajak_stnk)->lessThan($today)) {
            $additionalMessage .= 'Pajak STNK sudah expired sejak ' . Carbon::parse($dbVehicle->tanggal_pajak_stnk)->format('d-m-Y') . ".\n\n ";
        } elseif (Carbon::parse($dbVehicle->tanggal_pajak_stnk)->lessThanOrEqualTo($nextMonth)) {
            $additionalMessage .= 'Pajak STNK akan kadaluarsa pada ' . Carbon::parse($dbVehicle->tanggal_pajak_stnk)->format('d-m-Y') . ".\n\n ";
        }

        if ($dbVehicle->tanggal_kir == null) {
            $additionalMessage .= 'Tanggal KIR belum diinput. ';
        } elseif (Carbon::parse($dbVehicle->tanggal_kir)->lessThan($today)) {
            $additionalMessage .= 'KIR sudah expired sejak ' . Carbon::parse($dbVehicle->tanggal_kir)->format('d-m-Y') . ".\n\n ";
        } elseif (Carbon::parse($dbVehicle->tanggal_kir)->lessThanOrEqualTo($nextMonth)) {
            $additionalMessage .= 'KIR akan kadaluarsa pada ' . Carbon::parse($dbVehicle->tanggal_kir)->format('d-m-Y') . ".\n\n ";
        }

        if ($additionalMessage != '') {
            // tambahkan "==========================\n" pada awal pesan
            $additionalMessage = "==========================\n" . $additionalMessage;
            // tambankan "\n" pada akhir pesan
        }

        $dbWa = new GroupWa();
        $group = $dbWa->where('untuk', 'kas-uang-jalan')->first();

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
                    $additionalMessage.
                    "Terima kasih 🙏🙏🙏\n";

        $send = $dbWa->sendWa($group->nama_group, $pesan);

        $dbVendor = Vendor::find($vendor);

        if ($dbVendor->no_hp != null || $dbVendor->no_hp != '' || $dbVendor->no_hp != '-') {
            $pesanVendor =  "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
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
                            $additionalMessage.
                            "Terima kasih 🙏🙏🙏\n";

            $sendVendor = $dbWa->sendWa($dbVendor->no_hp, $pesanVendor);
        }

        return redirect()->route('billing.index')->with('success', 'Data Berhasil Ditambahkan');


    }
}
