<?php

namespace App\Http\Controllers;

use App\Models\KasUangJalan;
use App\Models\KasBesar;
use App\Models\Rekening;
use App\Models\GroupWa;
use App\Models\Vehicle;
use App\Models\Vendor;
use App\Models\Customer;
use App\Models\CustomerTagihan;
use App\Models\Konfigurasi;
use App\Models\Pengaturan;
use App\Models\Rute;
use App\Models\VendorUangJalan;
use App\Models\Transaksi;
use App\Models\UjDitahan;
use App\Models\UjDitahanDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\StarSender;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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

        $db = new KasBesar;

        try {
            //code...
            DB::beginTransaction();

            $store = KasUangJalan::create($data);

            $data['saldo'] = $kb->saldo - $data['nominal_transaksi'];
            $data['jenis_transaksi_id'] = 2;
            $data['modal_investor_terakhir'] = $kb->modal_investor_terakhir;

            $store2 = $db->create($data);

            DB::commit();
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return redirect()->back()->with('error', 'Data Gagal Ditambahkan. '. $th->getMessage());
        }


        // $profit = $db->calculateProfitBulanan(date('m'), date('Y'));
        $dbWa = new GroupWa();

        $group = $dbWa->where('untuk', 'kas-besar')->first();

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
                    // "Profit Bersih: \n".
                    // "Rp. ".$profit."\n\n".
                    "Terima kasih 🙏🙏🙏\n";

        $send = $dbWa->sendWa($group->nama_group, $pesan);


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

        $konfigurasi = Konfigurasi::where('kode', 'nota-muat')->first()->status ?? 0;
        $limitValue = Pengaturan::where('untuk', 'limit-tonase-muat')->first()->nilai ?? 0;

        return view('billing.kas-uang-jalan.keluar', [
            'nomor' => $nomor,
            'vehicle' => $vehicle,
            'customer' => $customer,
            'konfigurasi' => $konfigurasi,
            'limitValue' => $limitValue,
        ]);
    }

    public function get_vendor(Request $request)
    {
        $vehicle = Vehicle::join('vendors', 'vendors.id', 'vehicles.vendor_id')
                                ->select('vehicles.*', 'vendors.nama as nama_vendor', 'vendors.id as id_vendor', 'vendors.limit_tonase as limit_tonase')
                                ->find($request->id);

        if ($vehicle->uj_ditahan == 1 && $vehicle->driver) {
            $vehicle->transfer_ke = $vehicle->driver->nama_rek; // Sesuaikan nama kolom di tabel driver Anda
            $vehicle->bank = $vehicle->driver->bank;               // Sesuaikan nama kolom
            $vehicle->no_rekening = $vehicle->driver->no_rek; // Sesuaikan nama kolom
        }

        $data = $vehicle;
        return response()->json($data);
    }

    public function get_rute(Request $request)
    {
        $customer = Customer::find($request->id);
        $data = [
                'rute' => $customer->rute,
                'gt_muat' => $customer->gt_muat,
            ];
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

            'nota_muat' => 'required',
            'tonase' => 'required|numeric',
            'tanggal_muat' => 'required',
            'gross_muat' => 'nullable|numeric',
            'tarra_muat' => 'nullable|numeric',
        ]);

        // dd($data);


        $data['tonase'] = str_replace(',', '.', $data['tonase']);
        if (isset($data['gross_muat'])) $data['gross_muat'] = str_replace(',', '.', $data['gross_muat']);
        if (isset($data['tarra_muat'])) $data['tarra_muat'] = str_replace(',', '.', $data['tarra_muat']);

        $konfigurasi = Konfigurasi::where('kode', 'nota-muat')->first()->status ?? 0;

        if ($konfigurasi == 1) {
            // check if tanggal muat is older than 2 day from today, if true then return error
            $tanggalMuat = strtotime($data['tanggal_muat']);
            $today = strtotime(date('Y-m-d'));

            $diff = $today - $tanggalMuat;
            $diff = $diff / (60 * 60 * 24);

            if ($diff > 2) {
                return redirect()->back()->withInput()->with('error', 'Tanggal muat tidak boleh lebih dari H-2 dari hari ini!!');
            }

        }

        $customerTagihan = CustomerTagihan::where('customer_id', $data['customer_id'])->where('rute_id', $data['rute_id'])->first();
        if (!$customerTagihan) {
            return redirect()->back()->withInput()->with('error', 'Tagihan untuk Customer dan Rute ini belum diatur di database!');
        }
        $dbCustomer = Customer::find($data['customer_id']);
        $dbRute = $customerTagihan->rute;

        $data['harga_customer'] = $customerTagihan->harga_tagihan;

        $dbVendor = Vendor::find($data['p_vendor']);

        $pembayaranVendor = $dbVendor->pembayaran;

        if ($pembayaranVendor == 'opname') {
            $data['harga_vendor'] = $customerTagihan->opname;
        } elseif ($pembayaranVendor == 'titipan') {
            $data['harga_vendor'] = $customerTagihan->titipan;
        } elseif ($pembayaranVendor == 'titipan_khusus') {
            $data['harga_vendor'] = $customerTagihan->titipan_khusus;
        }

        if ($dbCustomer->csr == 1) {
            $jarak = $dbRute->jarak;
            if ($jarak > 50) {
                $data['harga_csr'] = $dbCustomer->harga_csr_atas;
            } else {
                $data['harga_csr'] = $dbCustomer->harga_csr_bawah;
            }
        } else {
            $data['harga_csr'] = 0;
        }

        $minTonase = Pengaturan::where('untuk', 'limit-tonase-muat')->first()->nilai ?? 0;

        if (Auth::user()->role != 'admin' && $dbVendor->limit_tonase == 1 && $request->tonase < $minTonase) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal! Tonase muat minimum untuk vendor ini adalah ' . $minTonase . ' Ton.');
        }

        $data['tanggal_muat'] = date('Y-m-d', strtotime($data['tanggal_muat']));
        $data['status'] = 2;

        $vendor = $data['p_vendor'];
        $kendaraan = Vehicle::find($data['vehicle_id']);

        $data['nominal_transaksi'] = str_replace('.', '', $data['nominal_transaksi']);
        $data['transfer_ke'] = substr($data['transfer_ke'], 0, 15);
        $data['jenis_transaksi_id'] = 2;
        $data['tanggal'] = date('Y-m-d');
        $data['vendor_id'] = $vendor;

        $nominalDitahan = 0;

        $auth = ['admin', 'su'];

        if (!in_array(Auth::user()->role, $auth)) {

            if ($kendaraan->uj_ditahan == 1) {
                // Jika UJ Ditahan: Harga dari rutes.uang_jalan - rutes.uj_ditahan
                $rute = Rute::find($data['rute_id']);

                $uang_jalan_kotor = $rute->uang_jalan ?? 0;
                $potongan = $rute->uj_ditahan ?? 0;

                $expectedNominal = $uang_jalan_kotor - $potongan;
            } else {
                // Jika Normal: Harga dari VendorUangJalan
                $expectedNominal = VendorUangJalan::where('vendor_id', $vendor)
                                        ->where('rute_id', $data['rute_id'])
                                        ->first()->hk_uang_jalan ?? 0;
            }

            if ($expectedNominal < 0) {
                $expectedNominal = 0;
            }

            if($expectedNominal != $data['nominal_transaksi']){
                return redirect()->back()->with('error', 'Nominal Uang Jalan Tidak Sesuai dengan Sistem!');
            }

        }

        if($kendaraan->uj_ditahan == 1){
            $nominalDitahan = Rute::find($data['rute_id'])->uj_ditahan ?? 0;
        }

        unset($data['p_vendor']);

        $nomor = KasUangJalan::whereNotNull('nomor_uang_jalan')->latest()->orderBy('id', 'desc')->first();

        if($nomor == null){
            $data['nomor_uang_jalan'] = 1;
        }else{
            $data['nomor_uang_jalan'] = $nomor->nomor_uang_jalan + 1;
        }

        $last = KasUangJalan::latest()->orderBy('id', 'desc')->first();

        if(!$last && $data['nominal_transaksi'] > 0){
            return redirect()->back()->with('error', 'Saldo Kas Uang Jalan Tidak Cukup');
        } elseif($last && $last->saldo < $data['nominal_transaksi']) {
            return redirect()->back()->with('error', 'Saldo Kas Uang Jalan Tidak Cukup');
        } else {
            $data['saldo'] = $last ? $last->saldo - $data['nominal_transaksi'] : 0;
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

        // check tanggal pajak stnk, jika null maka return back error, else if jika tanggal 30 pajak stnk 30 hari lagi akan expired dan auth()->user()->role != admin, maka return redirect back
        $nextMonth = Carbon::today()->addMonth();

        if ($dbVehicle->tanggal_pajak_stnk == null) {
            return redirect()->back()->with('error', 'Tanggal Pajak STNK belum diinput!');
        } elseif (Carbon::parse($dbVehicle->tanggal_pajak_stnk)->lessThanOrEqualTo($nextMonth) && Auth::user()->role != 'admin') {
            return redirect()->back()->withInput()->with('error', 'Pajak STNK kadaluarsa pada ' . Carbon::parse($dbVehicle->tanggal_pajak_stnk)->format('d-m-Y') . '! Silahkan hubungin Admin!');
        }


        try {
            DB::beginTransaction();

            $store = KasUangJalan::create([
                'tanggal' => $data['tanggal'],
                'vendor_id' => $data['vendor_id'],
                'vehicle_id' => $data['vehicle_id'],
                'nomor_uang_jalan' => $data['nomor_uang_jalan'],
                'customer_id' => $data['customer_id'],
                'rute_id' => $data['rute_id'],
                'jenis_transaksi_id' => $data['jenis_transaksi_id'],
                'nominal_transaksi' => $data['nominal_transaksi'] + $nominalDitahan,
                'saldo' => $data['saldo'],
                'transfer_ke' => $data['transfer_ke'],
                'bank' => $data['bank'],
                'no_rekening' => $data['no_rekening'],
            ]);


            $transaksi = Transaksi::create([
                'kas_uang_jalan_id' => $store->id,
                'harga_customer' => $data['harga_customer'],
                'harga_vendor' => $data['harga_vendor'],
                'harga_csr' => $data['harga_csr'],
                'tanggal_muat' => $data['tanggal_muat'],
                'nota_muat' => $data['nota_muat'],
                'tonase' => $data['tonase'],
                'gross_muat' => isset($data['gross_muat']) ? $data['gross_muat'] : 0,
                'tarra_muat' => isset($data['tarra_muat']) ? $data['tarra_muat'] : 0,
                'status' => $data['status'],
                ]);

            Vehicle::find($data['vehicle_id'])->update(['status' => 'proses']);

            // =========================================================
            // PENCATATAN UJ DITAHAN (MASTER & DETAIL)
            // =========================================================
            if ($kendaraan->uj_ditahan == 1 && $nominalDitahan > 0) {
                // Ambil bulan dan tahun dari tanggal transaksi (bisa dari $data['tanggal'])
                $bulanTrx = date('n', strtotime($data['tanggal']));
                $tahunTrx = date('Y', strtotime($data['tanggal']));

                // 1. Catat/Update ke Tabel Master (uj_ditahans)
                // firstOrCreate akan mencari kecocokan. Jika tidak ada, baris baru dibuat.
                $ujMaster = UjDitahan::firstOrCreate(
                    [
                        'bulan' => $bulanTrx,
                        'tahun' => $tahunTrx,
                        'vehicle_id' => $data['vehicle_id'],
                    ],
                    [
                        'total_masuk' => 0,
                        'total_keluar' => 0,
                        'saldo' => 0,
                    ]
                );

                // Tambahkan nominal masuk dan saldo
                $ujMaster->increment('total_masuk', $nominalDitahan);
                $ujMaster->increment('saldo', $nominalDitahan);
                $rekeningUjDitahan = Rekening::where('untuk', 'uang-jalan-ditahan')->first();

                // 2. Catat Histori ke Tabel Detail (uj_ditahan_details)
                UjDitahanDetail::create([
                    'uj_ditahan_id' => $ujMaster->id,
                    'transaksi_id'  => $transaksi->id,
                    // Silakan sesuaikan jika Anda menyimpan driver_id di tabel Vehicle, misalnya:
                    'driver_id'     => $kendaraan->driver_id ?? null,
                    // 'driver_id'     => null,
                    'jenis'         => 'masuk',
                    'nominal'       => $nominalDitahan,
                    'keterangan'    => 'UJ Ditahan (Trx UJ' . sprintf("%02d", $data['nomor_uang_jalan']) . ')',
                    'bank'          => $rekeningUjDitahan->nama_bank ?? null,
                    'no_rekening'   => $rekeningUjDitahan->nomor_rekening ?? null,
                    'nama_rekening' => $rekeningUjDitahan->nama_rekening ?? null,
                ]);
            }
            // =========================================================

            DB::commit();
        } catch (\Throwable $th) {
            //throw $th;

            DB::rollback();
            return redirect()->back()->with('error', 'Data Gagal Ditambahkan. '. $th->getMessage());
        }

        $additionalMessage = '';
        $today = Carbon::today();
        $nextMonth = $today->copy()->addMonth();
        $monthAndHalf = $today->copy()->addDays(45);

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
        } elseif (Carbon::parse($dbVehicle->tanggal_pajak_stnk)->lessThanOrEqualTo($monthAndHalf)) {
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

        $pesan2 = '';

        if($kendaraan->uj_ditahan == 1){

            $rekeningUjDitahan = Rekening::where('untuk', 'uang-jalan-ditahan')->first();

            $totalUjDitahan = UjDitahan::where('saldo', '>', '0')->sum('saldo');

            $pesan2 =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Form Uang Jalan Ditahan*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    "*UJ".sprintf("%02d",$data['nomor_uang_jalan'])."*\n\n".
                    "Nomor Lambung : ".Vehicle::find($data['vehicle_id'])->nomor_lambung."\n".
                    "Vendor : ".$store->vendor->nama."\n\n".
                    "Tambang : ".$store->customer->singkatan."\n".
                    "Rute : ".$store->rute->nama."\n\n".
                    "Nilai :  *Rp. ".number_format($nominalDitahan, 0, ',', '.').",-*\n\n".
                    "Ditransfer ke rek:\n\n".
                    "Bank     : ".$rekeningUjDitahan['nama_bank']."\n".
                    "Nama    : ".$rekeningUjDitahan['nama_rekening']."\n".
                    "No. Rek : ".$rekeningUjDitahan['nomor_rekening']."\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Uang Jalan : \n".
                    "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                    "Grand Total UJ Ditahan : \n".
                    "Rp. ".number_format($totalUjDitahan, 0, ',', '.')."\n\n".
                    // $additionalMessage.
                    "Terima kasih 🙏🙏🙏\n";
        }

        $send = $dbWa->sendWa($group->nama_group, $pesan);

        if($pesan2 != ''){

            $send2 = $dbWa->sendWa($group->nama_group, $pesan2);

            if($dbVehicle->driver && $dbVehicle->driver->no_hp != null && $dbVehicle->driver->no_hp != '' && $dbVehicle->driver->no_hp != '-' && $dbVehicle->driver->no_hp != '0' && strlen($dbVehicle->driver->no_hp) >= 10){

                $pesanDriver =  "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
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

                $ujDitahanVehicle = UjDitahan::where('vehicle_id', $data['vehicle_id'])->where('saldo', '>', 0)->sum('saldo');

                $pesanDriver2 =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Form Uang Jalan Ditahan*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    "*UJ".sprintf("%02d",$data['nomor_uang_jalan'])."*\n\n".
                    "Nomor Lambung : ".Vehicle::find($data['vehicle_id'])->nomor_lambung."\n".
                    "Vendor : ".$store->vendor->nama."\n\n".
                    "Tambang : ".$store->customer->singkatan."\n".
                    "Rute : ".$store->rute->nama."\n\n".
                    "Nilai :  *Rp. ".number_format($nominalDitahan, 0, ',', '.').",-*\n\n".
                    "Ditransfer ke rek:\n\n".
                    "Bank     : ".$rekeningUjDitahan['nama_bank']."\n".
                    "Nama    : ".$rekeningUjDitahan['nama_rekening']."\n".
                    "No. Rek : ".$rekeningUjDitahan['nomor_rekening']."\n\n".
                    "==========================\n".
                    // "Sisa Saldo Kas Uang Jalan : \n".
                    // "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                    "Grand Total UJ Ditahan : \n".
                    "Rp. ".number_format($ujDitahanVehicle, 0, ',', '.')."\n\n".
                    $additionalMessage.
                    "Terima kasih 🙏🙏🙏\n";
                // delete all non numeric and space characters from $dbVehicle->driver->no_hp

                $hpDriver = $dbVehicle->driver->no_hp = preg_replace('/\D/', '', $dbVehicle->driver->no_hp);

                $dbWa->sendWa($hpDriver, $pesanDriver2);
                $dbWa->sendWa($hpDriver, $pesanDriver);
            }

        }

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
                            "Nilai :  *Rp. ".number_format($data['nominal_transaksi']+$nominalDitahan, 0, ',', '.').",-*\n\n".
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

     public function pengembalian()
    {
        $db = new KasUangJalan();
        $saldo = $db->saldoTerakhir();
        $rekening = Rekening::where('untuk', 'kas-besar')->first();

        return view('billing.kas-uang-jalan.pengembalian', [
            'saldo' => $saldo,
            'rekening' => $rekening,
        ]);
    }

    public function pengembalian_store(Request $request)
    {
        $data = $request->validate([
            'nominal_transaksi' => 'required',
        ]);

        $db = new KasUangJalan();

        $req = $db->pengembalian($data);

        if($req['status'] == 'error'){
            return redirect()->back()->withInput()->with('error', $req['message']);
        }

        return redirect()->route('billing.index')->with($req['status'], $req['message']);
    }

     public function penyesuaian()
    {
        $rekening = Rekening::where('untuk', 'kas-uang-jalan')->first();
        $batasan = Pengaturan::where('untuk', 'kas-uang-jalan')->first()->nilai;

        return view('billing.kas-uang-jalan.penyesuaian', [
            'rekening' => $rekening,
            'batasan' => $batasan,
        ]);
    }

    public function penyesuaian_store(Request $request)
    {
        $data = $request->validate([
            'uraian' => 'required',
            'nominal_transaksi' => 'required',
            'tipe' => 'required',
            'transfer_ke' => 'required',
            'bank' => 'required',
            'no_rekening' => 'required',
        ]);

        $db = new KasUangJalan();

        $req = $db->penyesuaian($data);

        if($req['status'] == 'error'){
            return redirect()->back()->withInput()->with('error', $req['message']);
        }

        return redirect()->route('billing.index')->with($req['status'], $req['message']);
    }
}
