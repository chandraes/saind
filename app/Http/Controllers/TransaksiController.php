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
use App\Models\Sponsor;
use App\Models\GroupWa;
use App\Services\StarSender;
use App\Models\Rekening;
use App\Models\PasswordKonfirmasi;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\TransaksiExport;

class TransaksiController extends Controller
{
    public function index()
    {
        $data = Transaksi::all();
        $customer = Customer::all();

        $invoice = InvoiceTagihan::where('lunas', 0)->count();
        $bayar = InvoiceBayar::where('lunas', 0)->count();
        $bonus = InvoiceBonus::where('lunas', 0)->count();

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
        // dd($bayar);
        return view('billing.transaksi.index', [
            'data' => $data,
            'customer' => $customer,
            'vendor' => $vendor,
            'sponsor' => $sponsor,
            'invoice' => $invoice,
            'bayar' => $bayar,
            'bonus' => $bonus,
        ]);
    }

    public function nota_muat()
    {
        $data = Transaksi::where('status', 1)->where('void', 0)->get();
        return view('billing.transaksi.nota-muat', [
            'data' => $data,
        ]);
    }

    public function nota_muat_update(Request $request, Transaksi $transaksi)
    {
        $data = $request->validate([
            'nota_muat' => 'required',
            'tonase' => 'required|numeric',
        ]);

        $data['harga_customer'] = $transaksi->kas_uang_jalan->customer->customer_tagihan->where('rute_id', $transaksi->kas_uang_jalan->rute_id)->first()->harga_tagihan;

        $vendor = $transaksi->kas_uang_jalan->vendor->pembayaran;

        if ($vendor == 'opname') {
            $data['harga_vendor'] = $transaksi->kas_uang_jalan->customer->customer_tagihan->where('rute_id', $transaksi->kas_uang_jalan->rute_id)->first()->opname;
        } elseif ($vendor == 'titipan') {
            $data['harga_vendor'] = $transaksi->kas_uang_jalan->customer->customer_tagihan->where('rute_id', $transaksi->kas_uang_jalan->rute_id)->first()->opname;
        }

        $data['status'] = 2;
        $data['tanggal_muat'] = date('Y-m-d');

        $transaksi->update($data);

        return redirect()->back()->with('success', 'Berhasil menyimpan data!!');
    }

    public function nota_bongkar()
    {
        $data = Transaksi::where('status', 2)->where('void', 0)->get();

        return view('billing.transaksi.nota-bongkar', [
            'data' => $data,
        ]);
    }

    public function nota_bongkar_update(Request $request, Transaksi $transaksi)
    {
        // dd($request->all());
        $data = $request->validate([
            'nota_bongkar' => 'required',
            'timbangan_bongkar' => 'required|numeric',
        ]);

        $data['status'] = 3;
        $data['tanggal_bongkar'] = date('Y-m-d');

        if ($transaksi->kas_uang_jalan->customer->tagihan_dari == 1) {
            $data['nominal_tagihan'] = $transaksi->tonase * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->harga_customer;
        } elseif($transaksi->kas_uang_jalan->customer->tagihan_dari == 2){
            $data['nominal_tagihan'] = $data['timbangan_bongkar'] * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->harga_customer;
        }

        if ($transaksi->kas_uang_jalan->vendor->pembayaran == 'opname') {
            $data['nominal_bayar'] = $data['timbangan_bongkar']  * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->harga_vendor;

            $harga = $transaksi->kas_uang_jalan->rute->jarak > 50 ? 1000 : 500;


        } elseif ($transaksi->kas_uang_jalan->vendor->pembayaran == 'titipan') {
            $data['nominal_bayar'] = $data['timbangan_bongkar']  * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->harga_vendor;

            $harga = $transaksi->kas_uang_jalan->rute->jarak > 50 ? 500 : 250;
        }

        $data['nominal_bonus'] = $data['timbangan_bongkar'] * $harga;

        $data['profit'] = ($data['nominal_tagihan'] * 0.98) - $data['nominal_bayar'] - $data['nominal_bonus'];

        $transaksi->update($data);

        $transaksi->kas_uang_jalan->vehicle->update([
            'status' => 'aktif',
        ]);

        return redirect()->back()->with('success', 'Berhasil menyimpan data!!');
    }

    public function nota_tagihan(Customer $customer)
    {
        $data = Transaksi::join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')->where('status', 3)->where('transaksis.void', 0)
                            ->where('tagihan', 0)->where('kuj.customer_id', $customer->id)
                            ->select('transaksis.*')
                            ->get();

        return view('billing.transaksi.tagihan.index', [
            'data' => $data,
            'customer' => $customer,
        ]);
    }

    public function tagihan_export(Customer $customer)
    {
        $data = Transaksi::join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')->where('status', 3)->where('transaksis.void', 0)
                            ->where('tagihan', 0)->where('kuj.customer_id', $customer->id)
                            ->select('transaksis.*')
                            ->get();

        // get latest data from month before current month
        // dd($bulan);
        $pdf = PDF::loadview('billing.transaksi.tagihan.export', [
            'data' => $data,
            'customer' => $customer,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Nota Tagihan '.$customer->singkatan.'.pdf');
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

        $transaksi->kas_uang_jalan->vehicle->update([
            'status' => 'aktif',
        ]);

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

    public function back_tagihan(Request $request, Transaksi $transaksi)
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

        return redirect()->route('transaksi.nota-tagihan.edit', $transaksi);
    }

    public function nota_tagihan_edit(Transaksi $transaksi)
    {
        return view('billing.transaksi.tagihan.edit', [
            'd' => $transaksi,
        ]);
    }

    public function nota_tagihan_update(Request $request, Transaksi $transaksi)
    {

        $data = $request->validate([
            'tonase' => 'required|numeric',
            'timbangan_bongkar' => 'required|numeric',
            'nota_muat' => 'required',
            'nota_bongkar' => 'required',
        ]);

        if ($transaksi->kas_uang_jalan->customer->tagihan_dari == 1) {
            $data['nominal_tagihan'] = $data['tonase'] * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->harga_customer;
        } elseif($transaksi->kas_uang_jalan->customer->tagihan_dari == 2){
            $data['nominal_tagihan'] = $data['timbangan_bongkar'] * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->harga_customer;
        }

        if ($transaksi->kas_uang_jalan->vendor->pembayaran == 'opname') {
            $data['nominal_bayar'] = $data['timbangan_bongkar']  * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->harga_vendor;
            $harga = $transaksi->kas_uang_jalan->rute->jarak > 50 ? 1000 : 500;

        } elseif ($transaksi->kas_uang_jalan->vendor->pembayaran == 'titipan') {
            $data['nominal_bayar'] = $data['timbangan_bongkar']  * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->harga_vendor;
            $harga = $transaksi->kas_uang_jalan->rute->jarak > 50 ? 500 : 250;
        }
        $data['nominal_bonus'] = $data['timbangan_bongkar'] * $harga;

        $data['profit'] = ($data['nominal_tagihan'] *0.98) - $data['nominal_bayar'] - $data['nominal_bonus'];

        $transaksi->update($data);

        return redirect()->route('transaksi.nota-tagihan', $transaksi->kas_uang_jalan->customer_id)->with('success', 'Berhasil menyimpan data!!');
    }

    public function nota_tagihan_lanjut(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'total_tagihan' => 'required|numeric',
        ]);

        $tagihan = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->select('transaksis.id')
                            ->where('transaksis.status', 3)
                            ->where('transaksis.void', 0)
                            ->where('tagihan', 0)
                            ->where('kuj.customer_id', $customer->id)->get();



        $data['tanggal'] = date('Y-m-d');
        // no_invoice from invoice tagihan where customer_id = $customer->id and max no_invoice
        $data['no_invoice'] = InvoiceTagihan::where('customer_id', $customer->id)->max('no_invoice') + 1;
        $data['customer_id'] = $customer->id;
        $data['total_bayar'] = 0;
        $data['sisa_tagihan'] = $data['total_tagihan'];
        $data['lunas'] = 0;
        $data['periode'] = "Periode ".$data['no_invoice'];

        $invoice = InvoiceTagihan::create($data);

        foreach ($tagihan as $key => $value) {
            $value->update([
                'tagihan' => 1,
            ]);

            InvoiceTagihanDetail::create([
                'invoice_tagihan_id' => $invoice->id,
                'transaksi_id' => $value->id,
            ]);
        }

        return redirect()->route('transaksi.nota-tagihan', $customer)->with('success', 'Berhasil menyimpan data!!');

    }

    public function nota_bayar(Request $request)
    {
        $vendorId = $request->vendor_id;
        $vendor = Vendor::find($vendorId);
        $data = Transaksi::join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')->where('status', 3)->where('transaksis.void', 0)
                            ->where('bayar', 0)->where('kuj.vendor_id', $vendorId)->get();

        return view('billing.transaksi.bayar.index', [
            'data' => $data,
            'vendor' => $vendor,
        ]);
    }

    public function nota_bayar_lanjut(Request $request, Vendor $vendor)
    {
        $data = $request->validate([
            'total_bayar' => 'required|numeric',
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

        $invoice = InvoiceBayar::create($data);

        foreach ($bayar as $key => $value) {
            $value->update([
                'bayar' => 1,
            ]);

            InvoiceBayarDetail::create([
                'invoice_bayar_id' => $invoice->id,
                'transaksi_id' => $value->id,
            ]);
        }

        return redirect()->route('transaksi.nota-bayar', ['vendor_id' =>$vendor])->with('success', 'Berhasil menyimpan data!!');

    }

    public function nota_bonus(Request $request)
    {
        $sponsorId = $request->sponsor_id;
        $sponsor = Sponsor::find($sponsorId);
        $data = Transaksi::join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')
                            ->join('vendors as v', 'kuj.vendor_id', 'v.id')
                            ->join('sponsors as s', 'v.sponsor_id', 's.id')
                            ->where('transaksis.status', 3)->where('transaksis.void', 0)
                            ->where('bonus', 0)
                            ->where('s.id', $sponsorId)
                            ->select('transaksis.*')
                            ->get();

        return view('billing.transaksi.bonus.index', [
            'data' => $data,
            'sponsor' => $sponsor,
        ]);
    }

    public function nota_bonus_lanjut(Request $request, Sponsor $sponsor)
    {
        $data = $request->validate([
            'total_bonus' => 'required|numeric',
        ]);

        $bonus = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->join('vendors as v', 'kuj.vendor_id', 'v.id')
                            ->join('sponsors as s', 'v.sponsor_id', 's.id')
                            ->select('transaksis.id')
                            ->where('transaksis.status', 3)
                            ->where('transaksis.void', 0)
                            ->where('bonus', 0)
                            ->where('s.id', $sponsor->id)->get();

        $data['tanggal'] = date('Y-m-d');
        // no_invoice from invoice tagihan where customer_id = $customer->id and max no_invoice
        $data['no_invoice'] = InvoiceBonus::where('sponsor_id', $sponsor->id)->max('no_invoice') + 1;
        $data['sponsor_id'] = $sponsor->id;
        $data['total_bayar'] = 0;
        $data['sisa_bonus'] = $data['total_bonus'];
        $data['lunas'] = 0;
        $data['periode'] = "Periode ".$data['no_invoice'];

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

        return redirect()->route('billing.transaksi.index')->with('success', 'Berhasil menyimpan data!!');
    }


}
