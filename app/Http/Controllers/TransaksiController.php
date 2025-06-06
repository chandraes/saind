<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\KasUangJalan;
use App\Models\InvoiceTagihan;
use App\Models\InvoiceTagihanDetail;
use App\Models\InvoiceBayar;
use App\Models\InvoiceBayarDetail;
use App\Models\InvoiceBonus;
use App\Models\InvoiceBonusDetail;
use App\Models\InvoiceCsr;
use App\Models\InvoiceCsrDetail;
use App\Models\Sponsor;
use App\Models\GroupWa;
use App\Models\Konfigurasi;
use App\Models\Pajak\PphPerusahaan;
use App\Models\Pajak\PphSimpan;
use App\Models\Pajak\PpnKeluaran;
use App\Models\Pajak\PpnMasukan;
use App\Services\StarSender;
use App\Models\Rekening;
use App\Models\PasswordKonfirmasi;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    public function index()
    {
        $data = Transaksi::join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')
                            ->leftJoin('vehicles as v', 'kuj.vehicle_id', 'v.id')
                            ->select('transaksis.*', 'kuj.customer_id as customer_id', 'v.vendor_id as vendor_id')
                            ->where('transaksis.void', 0)->get();

        $customer = Customer::all();

        $invoice = InvoiceTagihan::where('lunas', 0)->count();
        $bayar = InvoiceBayar::where('lunas', 0)->count();
        $bonus = InvoiceBonus::where('lunas', 0)->count();
        $invoice_csr = InvoiceCsr::where('lunas', 0)->count();

        $vendor = Transaksi::join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')
                                    ->where('status', 3)
                                    ->where('transaksis.bayar', 0)
                                    ->where('transaksis.void', 0)
                                    ->get()->unique('vendor_id');

        $sponsor = Transaksi::join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')
                                    ->join('vendors as v', 'kuj.vendor_id', 'v.id')
                                    ->join('sponsors as s', 'v.sponsor_id', 's.id')
                                    ->where('transaksis.bonus', 0)
                                    ->where('transaksis.status', 3)
                                    ->where('transaksis.void', 0)
                                    ->get()->unique('sponsor_id');

        $csr = Transaksi::join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')
                                    ->join('customers as c', 'kuj.customer_id', 'c.id')
                                    ->where('transaksis.csr', 0)
                                    ->where('transaksis.status', 3)
                                    ->where('transaksis.void', 0)
                                    ->where('c.csr', 1)
                                    ->get()->unique('customer_id');

        // dd($bayar);
        return view('billing.transaksi.index', [
            'data' => $data,
            'customer' => $customer,
            'vendor' => $vendor,
            'sponsor' => $sponsor,
            'invoice' => $invoice,
            'bayar' => $bayar,
            'bonus' => $bonus,
            'csr' => $csr,
            'invoice_csr' => $invoice_csr,
        ]);
    }

    public function nota_muat()
    {
        $data = Transaksi::with(['kas_uang_jalan', 'kas_uang_jalan.vendor', 'kas_uang_jalan.rute', 'kas_uang_jalan.customer', 'kas_uang_jalan.vehicle'])->where('status', 1)->where('void', 0)->get();
        $konfigurasi = Konfigurasi::where('kode', 'nota-muat')->first()->status ?? 0;
        return view('billing.transaksi.nota-muat.index', [
            'data' => $data,
            'konfigurasi' => $konfigurasi,

        ]);
    }

    public function nota_muat_update(Request $request, Transaksi $transaksi)
    {
        $data = $request->validate([
            'nota_muat' => 'required',
            'tonase' => 'required|numeric',
            'tanggal_muat' => 'required',
            'gross_muat' => 'nullable|numeric',
            'tarra_muat' => 'nullable|numeric',
        ]);

        $konfigurasi = Konfigurasi::where('kode', 'nota-muat')->first()->status ?? 0;

        if ($konfigurasi == 1) {
            // check if tanggal muat is older than 2 day from today, if true then return error
            $tanggalMuat = strtotime($data['tanggal_muat']);
            $today = strtotime(date('Y-m-d'));

            $diff = $today - $tanggalMuat;
            $diff = $diff / (60 * 60 * 24);

            if ($diff > 2) {
                return redirect()->back()->with('error', 'Tanggal muat tidak boleh lebih dari H-2 dari hari ini!!');
            }

        }

        $data['harga_customer'] = $transaksi->kas_uang_jalan->customer->customer_tagihan->where('rute_id', $transaksi->kas_uang_jalan->rute_id)->first()->harga_tagihan;

        $vendor = $transaksi->kas_uang_jalan->vendor->pembayaran;

        if ($vendor == 'opname') {
            $data['harga_vendor'] = $transaksi->kas_uang_jalan->customer->customer_tagihan->where('rute_id', $transaksi->kas_uang_jalan->rute_id)->first()->opname;
        } elseif ($vendor == 'titipan') {
            $data['harga_vendor'] = $transaksi->kas_uang_jalan->customer->customer_tagihan->where('rute_id', $transaksi->kas_uang_jalan->rute_id)->first()->titipan;
        }

        if ($transaksi->kas_uang_jalan->customer->csr == 1) {
            $jarak = $transaksi->kas_uang_jalan->rute->jarak;
            if ($jarak > 50) {
                $data['harga_csr'] = $transaksi->kas_uang_jalan->customer->harga_csr_atas;
            } else {
                $data['harga_csr'] = $transaksi->kas_uang_jalan->customer->harga_csr_bawah;
            }
        }

        $data['tanggal_muat'] = date('Y-m-d', strtotime($data['tanggal_muat']));
        $data['status'] = 2;

        try {
            $transaksi->update($data);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Terdapat nota yang sama!!');
        }

        return redirect()->back()->with('success', 'Berhasil menyimpan data!!');
    }

    public function nota_bongkar()
    {
        $data = Transaksi::getNotaBongkar();

        return view('billing.transaksi.nota-bongkar.index', [
            'data' => $data,
        ]);
    }

    public function nota_bongkar_update(Request $request, Transaksi $transaksi)
    {
        // dd($request->all());
        $data = $request->validate([
            'nota_bongkar' => 'required',
            'timbangan_bongkar' => 'required|numeric',
            'tanggal_bongkar' => 'required',
            'gross_bongkar' => 'nullable|numeric',
            'tarra_bongkar' => 'nullable|numeric',
        ]);

        $data['status'] = 3;
        $data['tanggal_bongkar'] = date('Y-m-d', strtotime($data['tanggal_bongkar']));

        $muat = $transaksi->tanggal_muat;
        // change bongkar to unix timestamp
        $muat = strtotime($muat);
        $bongkar = strtotime($data['tanggal_bongkar']);

        if ($bongkar < $muat) {
            return redirect()->back()->with('error', 'Tanggal bongkar tidak boleh lebih kecil dari tanggal muat!!');
        }

        if ($transaksi->kas_uang_jalan->customer->tagihan_dari == 1) {
            $data['nominal_tagihan'] = $transaksi->tonase * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->harga_customer;
            $data['nominal_bayar'] = $transaksi->tonase * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->harga_vendor;
            $penalty = $transaksi->tonase * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->kas_uang_jalan->customer->nominal_penalty;

        } elseif($transaksi->kas_uang_jalan->customer->tagihan_dari == 2){
            $data['nominal_tagihan'] = $data['timbangan_bongkar'] * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->harga_customer;
            $data['nominal_bayar'] = $data['timbangan_bongkar']  * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->harga_vendor;
            $penalty = $data['timbangan_bongkar'] * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->kas_uang_jalan->customer->nominal_penalty;

        }

        if ($transaksi->kas_uang_jalan->vendor->pembayaran == 'opname') {

            $harga = $transaksi->kas_uang_jalan->rute->jarak > 50 ? 1000 : 500;

        } elseif ($transaksi->kas_uang_jalan->vendor->pembayaran == 'titipan') {

            $harga = $transaksi->kas_uang_jalan->rute->jarak > 50 ? 1000 : 500;
        }

        $data['nominal_csr'] = 0;

        if($transaksi->kas_uang_jalan->customer->csr == 1)
        {
            $data['nominal_csr'] = $data['timbangan_bongkar'] * $transaksi->harga_csr;
        }

        $data['nominal_bonus'] = $data['timbangan_bongkar'] * $harga;

        $data['profit'] = ($data['nominal_tagihan'] * 0.98) - $data['nominal_bayar'] - $data['nominal_bonus'] - $data['nominal_csr'] - $penalty;

        $vehicle = $transaksi->kas_uang_jalan->vehicle;

        DB::beginTransaction();

        try {

            $transaksi->update($data);

            $vehicle->update([
                'do_count' => $vehicle->do_count + 1,
                'status' => 'aktif',
            ]);

            DB::commit();

        } catch (\Throwable $th) {

            DB::rollBack();

            $message = $th->getMessage();

            return redirect()->back()->with('error', $message);
        }


        return redirect()->back()->with('success', 'Berhasil menyimpan data!!');
    }

    public function nota_tagihan(Request $request, Customer $customer)
    {
        $req = $request->validate([
            'rute_id' => 'nullable|exists:rutes,id',
            'filter_date' => 'nullable|required_if:tanggal_filter,!=, null|in:tanggal_muat,tanggal_bongkar,tanggal',
            'tanggal_filter' => 'nullable|required_if:filter_date,tanggal_muat,tanggal_bongkar,tanggal',
        ]);

        $rute_id = $req['rute_id'] ?? null;
        $filter_date = $req['filter_date'] ?? null;
        $tanggal_filter = $req['tanggal_filter'] ?? null;

        /** @var \Illuminate\Routing\UrlGenerator */
        $url = url();

        // Store current URL in session
        session(['previous_url' => $url->full()]);

        $rute = $customer->rute;

        $data = Transaksi::getTagihanData($customer->id, $rute_id, $filter_date, $tanggal_filter);
        $keranjang = Transaksi::getKeranjangTagihanData($customer->id)->count();

        return view('billing.transaksi.tagihan.index', [
            'data' => $data,
            'customer' => $customer,
            'rute' => $rute,
            'rute_id' => $rute_id,
            'filter_date' => $req['filter_date'] ?? null,
            'tanggal_filter' => $req['tanggal_filter'] ?? null,
            'keranjang' => $keranjang,
        ]);
    }

    public function nota_tagihan_checked(Transaksi $transaksi)
    {
        $db = new Transaksi;

        $db->changeStateNotaFisik($transaksi->id);

        return redirect()->back()->with('success', 'Berhasil menyimpan data!!');
    }

    public function nota_tagihan_unchecked(Transaksi $transaksi, Request $request)
    {
        $data = $request->validate([
            'password' => 'required',
        ]);

        $password = PasswordKonfirmasi::first();

        if (!$password) {
            return response()->json(['message' => 'Password not found'], 404);
        }

        if ($data['password'] != $password->password) {
            return response()->json(['message' => 'Password yang anda masukan Salah'], 400);
        }

        $db = new Transaksi;

        $db->changeStateNotaFisik($transaksi->id);

        return response()->json(['message' => 'Berhasil menyimpan data!!'], 200);


    }

    public function tagihan_export(Request $request, Customer $customer)
    {
        $req = $request->validate([
            'rute_id' => 'nullable|exists:rutes,id',
            'filter_date' => 'nullable|required_if:tanggal_filter,!=, null|in:tanggal_muat,tanggal_bongkar,tanggal',
            'tanggal_filter' => 'nullable|required_if:filter_date,tanggal_muat,tanggal_bongkar,tanggal',
        ]);

        $rute_id = $req['rute_id'] ?? null;
        $filter_date = $req['filter_date'] ?? null;
        $tanggal_filter = $req['tanggal_filter'] ?? null;

        $data = Transaksi::getTagihanData($customer->id, $rute_id, $filter_date, $tanggal_filter);

        // get latest data from month before current month
        // dd($bulan);
        if (auth()->user()->role == 'admin') {
            $pdf = PDF::loadview('billing.transaksi.tagihan.export-admin', [
                'data' => $data,
                'customer' => $customer,
            ])->setPaper('a4', 'landscape');
        } else{
            $pdf = PDF::loadview('billing.transaksi.tagihan.export', [
                'data' => $data,
                'customer' => $customer,
            ])->setPaper('a4', 'landscape');
        }


        return $pdf->stream('Nota Tagihan '.$customer->singkatan.'.pdf');
    }

    public function void_tagihan(Request $request, Transaksi $transaksi)
    {
        $data = $request->validate([
            'password' => 'required',
        ]);

        $password = PasswordKonfirmasi::first();

        if (!$password) {
            return redirect()->back()->with('error', 'Password belum diatur!!');
        }

        if ($data['password'] != $password->password) {
            return redirect()->back()->with('error', 'Password salah!!');
        }

        return view('billing.transaksi.tagihan.void', [
            'data' => $transaksi,
        ]);
    }

    public function void_tagihan_store(Request $request, Transaksi $transaksi)
    {
        $data = $request->validate([
            'alasan' => 'required',
        ]);

        $data['void'] = 1;
        $data['nota_muat'] = null;
        $data['nota_bongkar'] = null;

        $transaksi->update($data);

        $last = KasUangJalan::latest()->orderBy('id', 'desc')->first();
        $rek = Rekening::where('untuk', 'kas-uang-jalan')->first();

        $store = KasUangJalan::create([
            'void' => 1,
            'kode_void' => "UJ".sprintf("%02d",$transaksi->kas_uang_jalan->nomor_uang_jalan),
            'jenis_transaksi_id' => 1,
            'nominal_transaksi' => $transaksi->kas_uang_jalan->nominal_transaksi,
            'tanggal' => date('Y-m-d'),
            'saldo' => $last->saldo + $transaksi->kas_uang_jalan->nominal_transaksi,
            'transfer_ke' => substr($rek->nama_rekening, 0, 15),
            'bank' => $rek->nama_bank,
            'no_rekening' => $rek->nomor_rekening,
        ]);

        $vehicle = Vehicle::find($transaksi->kas_uang_jalan->vehicle_id);

        if($transaksi->nota_fisik == 0) {
            $vehicle->update([
                'do_count' => $vehicle->do_count - 1,
            ]);
        }

        $group = GroupWa::where('untuk', 'kas-uang-jalan')->first();

        $pesan =    "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                    "*Void Uang Jalan*\n".
                    "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                    "*UJ".sprintf("%02d",$transaksi->kas_uang_jalan->nomor_uang_jalan)."*\n\n".
                    "Nomor Lambung : ".$transaksi->kas_uang_jalan->vehicle->nomor_lambung."\n".
                    "Vendor : ".$transaksi->kas_uang_jalan->vendor->nama."\n\n".
                    "Tambang : ".$transaksi->kas_uang_jalan->customer->singkatan."\n".
                    "Rute : ".$transaksi->kas_uang_jalan->rute->nama."\n\n".
                    "Alasan : ".$data['alasan']."\n".
                    "Nilai :  *Rp. ".number_format($transaksi->kas_uang_jalan->nominal_transaksi, 0, ',', '.').",-*\n\n".
                    "Ditransfer ke rek:\n\n".
                    "Bank     : ".$rek->nama_bank."\n".
                    "Nama    : ".$rek->nama_rekening."\n".
                    "No. Rek : ".$rek->nomor_rekening."\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Uang Jalan : \n".
                    "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                    "Terima kasih 🙏🙏🙏\n";

        $send = new StarSender($group->nama_group, $pesan);
        $res = $send->sendGroup();


        return redirect()->route('billing.transaksi.index')->with('success', 'Berhasil menyimpan data!!');
    }


    public function void(Request $request, Transaksi $transaksi)
    {
        $data = $request->validate([
            'password' => 'required',
        ]);

        $password = PasswordKonfirmasi::first();

        if (!$password) {
            return redirect()->back()->with('error', 'Password belum diatur!!');
        }

        if ($data['password'] != $password->password) {
            return redirect()->back()->with('error', 'Password salah!!');
        }

        return view('billing.transaksi.void', [
            'data' => $transaksi,
        ]);
    }

    public function void_store(Request $request, Transaksi $transaksi)
    {
        $data = $request->validate([
            'alasan' => 'required',
        ]);

        $data['void'] = 1;
        $data['nota_muat'] = null;
        $data['nota_bongkar'] = null;

        try {
            DB::beginTransaction();

            $transaksi->update($data);

            $last = KasUangJalan::latest()->orderBy('id', 'desc')->first();
            $rek = Rekening::where('untuk', 'kas-uang-jalan')->first();

            $store = KasUangJalan::create([
                'void' => 1,
                'kode_void' => "UJ".sprintf("%02d",$transaksi->kas_uang_jalan->nomor_uang_jalan),
                'jenis_transaksi_id' => 1,
                'nominal_transaksi' => $transaksi->kas_uang_jalan->nominal_transaksi,
                'tanggal' => date('Y-m-d'),
                'saldo' => $last->saldo + $transaksi->kas_uang_jalan->nominal_transaksi,
                'transfer_ke' => substr($rek->nama_rekening, 0, 15),
                'bank' => $rek->nama_bank,
                'no_rekening' => $rek->nomor_rekening,
            ]);

            $cekMobil = Transaksi::join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')
                                ->where('kuj.vehicle_id', $transaksi->kas_uang_jalan->vehicle_id)
                                ->where('transaksis.status', '<', 3)
                                ->where('transaksis.void', 0)
                                ->first();

            if (!$cekMobil) {
                $transaksi->kas_uang_jalan->vehicle->update([
                    'status' => 'aktif',
                ]);
            }

            $vehicle = Vehicle::find($transaksi->kas_uang_jalan->vehicle_id);

            if($transaksi->nota_fisik == 0) {
                if ($vehicle->do_count > 0) {
                    $vehicle->update([
                        'do_count' => $vehicle->do_count - 1,
                    ]);
                }
            }

            DB::commit();
        } catch (\Throwable $th) {
            //throw $th;

            DB::rollBack();

            return redirect()->back()->with('error', 'Terdapat Error pada saat menyimpan data!!');
        }

        $dbWa = new GroupWa();
        $group = $dbWa->where('untuk', 'kas-uang-jalan')->first();

        $pesan =    "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                    "*Void Uang Jalan*\n".
                    "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                    "*UJ".sprintf("%02d",$transaksi->kas_uang_jalan->nomor_uang_jalan)."*\n\n".
                    "Nomor Lambung : ".$transaksi->kas_uang_jalan->vehicle->nomor_lambung."\n".
                    "Vendor : ".$transaksi->kas_uang_jalan->vendor->nama."\n\n".
                    "Tambang : ".$transaksi->kas_uang_jalan->customer->singkatan."\n".
                    "Rute : ".$transaksi->kas_uang_jalan->rute->nama."\n\n".
                    "Alasan : ".$data['alasan']."\n".
                    "Nilai :  *Rp. ".number_format($transaksi->kas_uang_jalan->nominal_transaksi, 0, ',', '.').",-*\n\n".
                    "Ditransfer ke rek:\n\n".
                    "Bank     : ".$rek->nama_bank."\n".
                    "Nama    : ".$rek->nama_rekening."\n".
                    "No. Rek : ".$rek->nomor_rekening."\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Uang Jalan : \n".
                    "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                    "Terima kasih 🙏🙏🙏\n";

        $send = $dbWa->sendWa($group->nama_group, $pesan);


        return redirect()->route('billing.transaksi.index')->with('success', 'Berhasil menyimpan data!!');

    }

    public function back(Request $request, Transaksi $transaksi)
    {
        $data = $request->validate([
            'password' => 'required',
        ]);

        $password = PasswordKonfirmasi::first();

        if (!$password) {
            return redirect()->back()->with('error', 'Password belum diatur!!');
        }

        if ($data['password'] != $password->password) {
            return redirect()->back()->with('error', 'Password salah!!');
        }

        $transaksi->update([
            'status' => 1,
            'nota_muat' => null,
            'tonase' => null,
            'nota_bongkar' => null,
            'timbangan_bongkar' => null,
        ]);

        return redirect()->route('billing.transaksi.index')->with('success', 'Berhasil menyimpan data!!');

    }

    public function nota_tagihan_edit(Transaksi $transaksi, Request $request)
    {
        $data = $request->validate([
            'password' => 'required',
        ]);

        $password = PasswordKonfirmasi::first();

        if (!$password) {
            return redirect()->back()->with('error', 'Password belum diatur!!');
        }

        if ($data['password'] != $password->password) {
            return redirect()->back()->with('error', 'Password salah!!');
        }

        return view('billing.transaksi.tagihan.edit', [
            'd' => $transaksi,
        ]);
    }

    public function nota_tagihan_update(Request $request, Transaksi $transaksi)
    {

        $data = $request->validate([
            'tonase' => 'required|numeric',
            'gross_muat' => 'nullable|numeric',
            'tarra_muat' => 'nullable|numeric',
            'timbangan_bongkar' => 'required|numeric',
            'gross_bongkar' => 'nullable|numeric',
            'tarra_bongkar' => 'nullable|numeric',
            'nota_muat' => 'required',
            'nota_bongkar' => 'required',
            'tanggal_muat' => 'required',
            'tanggal_bongkar' => 'required',
        ]);

        $data['tanggal_muat'] = date('Y-m-d', strtotime($data['tanggal_muat']));

        $data['tanggal_bongkar'] = date('Y-m-d', strtotime($data['tanggal_bongkar']));

        if ($transaksi->kas_uang_jalan->customer->tagihan_dari == 1) {
            $data['nominal_tagihan'] = $data['tonase'] * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->harga_customer;
            $data['nominal_bayar'] = $data['tonase'] * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->harga_vendor;
            $penalty = $data['tonase'] * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->kas_uang_jalan->customer->nominal_penalty;
        } elseif($transaksi->kas_uang_jalan->customer->tagihan_dari == 2){
            $data['nominal_tagihan'] = $data['timbangan_bongkar'] * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->harga_customer;
            $data['nominal_bayar'] = $data['timbangan_bongkar']  * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->harga_vendor;
            $penalty = $data['timbangan_bongkar'] * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->kas_uang_jalan->customer->nominal_penalty;
        }

        if ($transaksi->kas_uang_jalan->vendor->pembayaran == 'opname') {

            $harga = $transaksi->kas_uang_jalan->rute->jarak > 50 ? 1000 : 500;

        } elseif ($transaksi->kas_uang_jalan->vendor->pembayaran == 'titipan') {

            $harga = $transaksi->kas_uang_jalan->rute->jarak > 50 ? 1000 : 500;
        }

        $data['nominal_csr'] = 0;

        if($transaksi->kas_uang_jalan->customer->csr == 1)
        {
            $data['nominal_csr'] = $data['timbangan_bongkar'] * $transaksi->harga_csr;
        }

        $data['nominal_bonus'] = $data['timbangan_bongkar'] * $harga;

        $data['profit'] = ($data['nominal_tagihan'] *0.98) - $data['nominal_bayar'] - $data['nominal_bonus'] - $data['nominal_csr'] - $penalty;

        try {
            $transaksi->update($data);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Terdapat nota yang sama!!');
        }

        $previousUrl = session('previous_url');
        // dd($previousUrl);

        // Redirect to previous URL
        return redirect()->to($previousUrl)->with('success', 'Berhasil menyimpan data!!');

    }

    // tagihan lanjut update jadi keranjang
    public function nota_tagihan_lanjut_pilih(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'selectedData' => 'required',
        ]);

        $data['selectedData'] = trim($data['selectedData'], ',');
        $data['selectedData'] = explode(',', $data['selectedData']);

        $keranjang = Transaksi::whereIn('id', $data['selectedData'])->update([
            'keranjang' => 1,
        ]);

        return redirect()->route('transaksi.nota-tagihan', $customer)->with('success', 'Berhasil memindahkan data ke Keranjang. Silahkan cek keranjang untuk validasi lanjutan!!');


    }

    public function keranjang_tagihan(Request $request, Customer $customer)
    {
        $req = $request->validate([
            'rute_id' => 'nullable|exists:rutes,id',
            'filter_date' => 'nullable|required_if:tanggal_filter,!=, null|in:tanggal_muat,tanggal_bongkar,tanggal',
            'tanggal_filter' => 'nullable|required_if:filter_date,tanggal_muat,tanggal_bongkar,tanggal',
        ]);

        $rute_id = $req['rute_id'] ?? null;
        $filter_date = $req['filter_date'] ?? null;
        $tanggal_filter = $req['tanggal_filter'] ?? null;

        /** @var \Illuminate\Routing\UrlGenerator */
        $url = url();

        // Store current URL in session
        session(['previous_url' => $url->full()]);

        $rute = $customer->rute;

        $data = Transaksi::getKeranjangTagihanData($customer->id, $rute_id, $filter_date, $tanggal_filter);

        return view('billing.transaksi.tagihan.keranjang.index', [
            'data' => $data,
            'customer' => $customer,
            'rute' => $rute,
            'rute_id' => $rute_id,
            'filter_date' => $req['filter_date'] ?? null,
            'tanggal_filter' => $req['tanggal_filter'] ?? null,

        ]);

    }

    public function keranjang_tagihan_lanjut(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'penyesuaian' => 'required',
            'penalty' => 'required',
            'tanggal_hardcopy' => 'required',
            'estimasi_pembayaran' => 'required',
            'no_resi' => 'required',
            'no_validasi' => 'required',
            'ppn_dipungut' => 'required',
        ]);

        $data['tanggal_hardcopy'] = Carbon::createFromFormat('d-m-Y', $data['tanggal_hardcopy'])->format('Y-m-d');
        $data['estimasi_pembayaran'] = Carbon::createFromFormat('d-m-Y', $data['estimasi_pembayaran'])->format('Y-m-d');
        $data['penyesuaian'] = str_replace('.', '', $data['penyesuaian']);
        $data['penalty'] = str_replace('.', '', $data['penalty']);
        $data['lunas'] = 0;
        $data['tanggal'] = Carbon::now()->format('Y-m-d');
        $dipungut = $data['ppn_dipungut'];

        $tagihan = Transaksi::getKeranjangTagihanData($customer->id);

        $total = $tagihan->sum('nominal_tagihan') + $data['penyesuaian'] - $data['penalty'];

        $ppn = $customer->ppn == 1 ? $total * 0.11 : 0;
        $pph = $customer->pph == 1 ? $total * 0.02 : 0;

        $data['total_awal'] = $tagihan->sum('nominal_tagihan');

        if ($dipungut == 1) {
            $total_tagihan = ($total + $ppn - $pph);
        } else {
            $total_tagihan = ($total - $pph);
        }

        $data['ppn'] = $ppn;
        $data['pph'] = $pph;
        $data['no_invoice'] = InvoiceTagihan::where('customer_id', $customer->id)->max('no_invoice') + 1;
        $data['customer_id'] = $customer->id;
        $data['total_bayar'] = 0;
        $data['sisa_tagihan'] = $total_tagihan;
        $data['total_tagihan'] = $total_tagihan;
        $data['lunas'] = 0;
        $data['periode'] = "Periode ".$data['no_invoice'];

        try {
            DB::beginTransaction();

            $invoice = InvoiceTagihan::create($data);

            if ($ppn > 0) {
                PpnKeluaran::create([
                    'invoice_tagihan_id' => $invoice->id,
                    'uraian' => 'PPN '. $invoice->periode,
                    'nominal' => $ppn,
                    'dipungut' => $dipungut,
                ]);
            }

            if ($pph > 0) {
                PphPerusahaan::create([
                    'invoice_tagihan_id' => $invoice->id,
                    'uraian' => 'PPh '. $invoice->periode,
                    'nominal' => $pph
                ]);
            }

            foreach ($tagihan as $key => $value) {
                $value->update([
                    'tagihan' => 1,
                    'keranjang' => 0,
                ]);

                InvoiceTagihanDetail::create([
                    'invoice_tagihan_id' => $invoice->id,
                    'transaksi_id' => $value->id,
                ]);
            }

            DB::commit();


        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();

            return redirect()->route('transaksi.nota-tagihan.keranjang', $customer)->withInput()->with('error', 'Terdapat kesalahan!! '.$th->getMessage());
        }



        return redirect()->route('transaksi.nota-tagihan', $customer)->with('success', 'Berhasil menyimpan data!!');
    }

    public function keranjang_tagihan_export(Customer $customer, Request $request)
    {
        $req = $request->validate([
            'rute_id' => 'nullable|exists:rutes,id',
            'filter_date' => 'nullable|required_if:tanggal_filter,!=, null|in:tanggal_muat,tanggal_bongkar,tanggal',
            'tanggal_filter' => 'nullable|required_if:filter_date,tanggal_muat,tanggal_bongkar,tanggal',
        ]);

        $rute_id = $req['rute_id'] ?? null;
        $filter_date = $req['filter_date'] ?? null;
        $tanggal_filter = $req['tanggal_filter'] ?? null;

        $data = Transaksi::getKeranjangTagihanData($customer->id, $rute_id, $filter_date, $tanggal_filter);

        // get latest data from month before current month
        // dd($bulan);
        if (auth()->user()->role == 'admin') {
            $pdf = PDF::loadview('billing.transaksi.tagihan.export-admin', [
                'data' => $data,
                'customer' => $customer,
            ])->setPaper('a4', 'landscape');
        } else{
            $pdf = PDF::loadview('billing.transaksi.tagihan.export', [
                'data' => $data,
                'customer' => $customer,
            ])->setPaper('a4', 'landscape');
        }


        return $pdf->stream('Nota Tagihan '.$customer->singkatan.'.pdf');
    }

    public function keranjang_tagihan_delete(Customer $customer, Transaksi $transaksi)
    {
        $transaksi->update([
            'keranjang' => 0,
        ]);

        return redirect()->route('transaksi.nota-tagihan.keranjang', $customer)->with('success', 'Berhasil menghapus data!!');

    }

    public function invoice_tagihan_detail_export(InvoiceTagihan $invoice, Customer $customer)
    {
        $data = $invoice->invoice_tagihan_detail;

        // get latest data from month before current month
        // dd($bulan);
        $pdf = PDF::loadview('billing.transaksi.tagihan.export-detail', [
            'data' => $data,
            'invoice' => $invoice,
            'invoice_id' => $invoice->id,
            'customer' => $customer,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Invoice Tagihan '.$invoice->customer->singkatan.'.pdf');
    }

    public function nota_bayar(Request $request)
    {
        $vendorId = $request->vendor_id;
        $vendor = Vendor::find($vendorId);
        $data = Transaksi::getNotaBayar($vendorId);

        return view('billing.transaksi.bayar.index', [
            'data' => $data,
            'vendor' => $vendor,
        ]);
    }

    // public function nota_bayar_lanjut(Request $request, Vendor $vendor)
    // {
    //     $data = $request->validate([
    //         'total_bayar' => 'required|numeric',
    //     ]);
    //     // dd($data);
    //     $bayar = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
    //                         ->select('transaksis.id')
    //                         ->where('transaksis.status', 3)
    //                         ->where('transaksis.void', 0)
    //                         ->where('bayar', 0)
    //                         ->where('kuj.vendor_id', $vendor->id)->get();

    //     $data['tanggal'] = date('Y-m-d');
    //     // no_invoice from invoice tagihan where customer_id = $customer->id and max no_invoice
    //     $data['no_invoice'] = InvoiceBayar::where('vendor_id', $vendor->id)->max('no_invoice') + 1;
    //     $data['vendor_id'] = $vendor->id;
    //     $data['total_bayar'] = $data['total_bayar'];
    //     $data['sisa_bayar'] = $data['total_bayar'];
    //     $data['bayar'] = 0;
    //     $data['lunas'] = 0;
    //     $data['periode'] = "Periode ".$data['no_invoice'];

    //     $invoice = InvoiceBayar::create($data);

    //     foreach ($bayar as $key => $value) {
    //         $value->update([
    //             'bayar' => 1,
    //         ]);

    //         InvoiceBayarDetail::create([
    //             'invoice_bayar_id' => $invoice->id,
    //             'transaksi_id' => $value->id,
    //         ]);
    //     }

    //     return redirect()->route('transaksi.nota-bayar', ['vendor_id' =>$vendor])->with('success', 'Berhasil menyimpan data!!');

    // }

    public function nota_bayar_lanjut(Request $request, Vendor $vendor)
    {
        $data = $request->validate([
            'total_bayar' => 'required|numeric',
            'ppn' => 'required|numeric',
            'pph' => 'required|numeric',
        ]);
        // dd($data);
        $bayar = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->select('transaksis.id')
                            ->where('transaksis.status', 3)
                            ->where('transaksis.void', 0)
                            ->where('bayar', 0)
                            ->where('kuj.vendor_id', $vendor->id)->get();

        $data['tanggal'] = date('Y-m-d');
        // no_invoice from invoice tagihan where customer_id = $customer->id and max no_invoice
        $data['no_invoice'] = InvoiceBayar::where('vendor_id', $vendor->id)->max('no_invoice') + 1;
        $data['vendor_id'] = $vendor->id;
        $data['total_bayar'] = $data['total_bayar'];
        $data['sisa_bayar'] = $data['total_bayar'];
        $data['bayar'] = 0;
        $data['lunas'] = 0;
        $data['periode'] = "Periode ".$data['no_invoice'];
        $ppn = $data['ppn'];
        $pph = $data['pph'];

        unset($data['ppn']);
        unset($data['pph']);

        try {
            DB::beginTransaction();

            $invoice = InvoiceBayar::create($data);

            if ($ppn > 0) {
                PpnMasukan::create([
                    'invoice_bayar_id' => $invoice->id,
                    'uraian' => $vendor->nickname.' PPN '. $invoice->periode,
                    'nominal' => $ppn,
                ]);
            }

            if ($pph > 0) {
                PphSimpan::create([
                    'invoice_bayar_id' => $invoice->id,
                    'uraian' => $vendor->nickname.' PPh '. $invoice->periode,
                    'nominal' => $pph
                ]);
            }

            foreach ($bayar as $key => $value) {
                $value->update([
                    'bayar' => 1,
                ]);

                InvoiceBayarDetail::create([
                    'invoice_bayar_id' => $invoice->id,
                    'transaksi_id' => $value->id,
                ]);
            }

            DB::commit();

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data!!');
        }

        return redirect()->route('transaksi.nota-bayar', ['vendor_id' =>$vendor])->with('success', 'Berhasil menyimpan data!!');

    }

    public function nota_bonus(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $db = new Transaksi;

        $dataTahun = $db->dataTahun();

        $sponsorId = $request->sponsor_id;
        $sponsor = Sponsor::find($sponsorId);
        $data = $db->notaBonus($sponsorId, $bulan, $tahun);

        return view('billing.transaksi.bonus.index', [
            'data' => $data,
            'sponsor' => $sponsor,
            'bulan' => $bulan,
            'dataTahun' => $dataTahun,
            'tahun' => $tahun
        ]);
    }

    public function nota_bonus_lanjut(Request $request, Sponsor $sponsor)
    {
        $data = $request->validate([
            'total_bonus' => 'required|numeric',
            'bulan' => 'required',
            'tahun' => 'required',
        ]);

        $db = new Transaksi;

        $bonus = $db->getIdNotaBonus($sponsor->id, $data['bulan'], $data['tahun']);

        unset($data['bulan']);
        unset($data['tahun']);

        $data['tanggal'] = date('Y-m-d');
        // no_invoice from invoice tagihan where customer_id = $customer->id and max no_invoice
        $data['no_invoice'] = InvoiceBonus::where('sponsor_id', $sponsor->id)->max('no_invoice') + 1;
        $data['sponsor_id'] = $sponsor->id;
        $data['total_bayar'] = 0;
        $data['sisa_bonus'] = $data['total_bonus'];
        $data['lunas'] = 0;
        $data['periode'] = "Periode ".$data['no_invoice'];

        DB::beginTransaction();

        $invoice = InvoiceBonus::create($data);

        foreach ($bonus as $key => $value) {
            $value->update([
                'bonus' => 1,
            ]);

            InvoiceBonusDetail::create([
                'invoice_bonus_id' => $invoice->id,
                'transaksi_id' => $value->id,
            ]);

        }

        DB::commit();

        return redirect()->route('billing.transaksi.index')->with('success', 'Berhasil menyimpan data!!');
    }

    public function nota_csr(Request $request)
    {
        $customerId = $request->customer_id;

        $data = Transaksi::getNotaCsr($customerId);

        return view('billing.transaksi.csr.index', [
            'data' => $data,
            'customer' => Customer::find($customerId),
        ]);

    }

    public function nota_csr_lanjut(Request $request)
    {
        $data = $request->validate([
            'total_csr' => 'required',
            'customer_id' => 'required|exists:customers,id',
        ]);

        $csr = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->where('kuj.customer_id', $data['customer_id'])
                            ->where('transaksis.status', 3)
                            ->where('transaksis.void', 0)
                            ->where('csr', 0)
                            ->where('nominal_csr', '>', 0)
                            ->select('transaksis.id')
                            ->get();

        $d['tanggal'] = date('Y-m-d');
        $d['no_invoice'] = InvoiceCsr::where('customer_id', $data['customer_id'])->max('no_invoice') + 1;
        $d['customer_id'] = $data['customer_id'];
        $d['total_csr'] = $data['total_csr'];
        $d['periode'] = "Periode ".$d['no_invoice'];
        $d['lunas'] = 0;

        $invoice = InvoiceCsr::create($d);

        foreach($csr as $c)
        {
            $c->update([
                'csr' => 1,
            ]);

            InvoiceCsrDetail::create([
                'invoice_csr_id' => $invoice->id,
                'transaksi_id' => $c->id,
            ]);
        }

        return redirect()->route('billing.transaksi.index')->with('success', 'Berhasil menyimpan data!!');
    }

    public function sales_order(Request $request)
    {

    }


}
