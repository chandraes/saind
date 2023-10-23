<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\KasUangJalan;
use App\Models\InvoiceTagihan;
use App\Models\InvoiceTagihanDetail;
use App\Models\GroupWa;
use App\Services\StarSender;
use App\Models\Rekening;
use App\Models\PasswordKonfirmasi;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransaksiExport;

class TransaksiController extends Controller
{
    public function index()
    {
        $data = Transaksi::all();
        $customer = Customer::all();
        $vendor = Transaksi::join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')
                                    ->where('status', 3)
                                    ->where('transaksis.void', 0)
                                    ->get()->unique('vendor_id');
        // dd($bayar);
        return view('billing.transaksi.index', [
            'data' => $data,
            'customer' => $customer,
            'vendor' => $vendor,
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
            $data['nominal_tagihan'] = $transaksi->tonase * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->kas_uang_jalan->customer->customer_tagihan->where('customer_id', $transaksi->kas_uang_jalan->customer->id)->where('rute_id', $transaksi->kas_uang_jalan->rute_id)->first()->harga_tagihan;
        } elseif($transaksi->kas_uang_jalan->customer->tagihan_dari == 2){
            $data['nominal_tagihan'] = $data['timbangan_bongkar'] * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->kas_uang_jalan->customer->customer_tagihan->where('customer_id', $transaksi->kas_uang_jalan->customer->id)->where('rute_id', $transaksi->kas_uang_jalan->rute_id)->first()->harga_tagihan;
        }

        if ($transaksi->kas_uang_jalan->vendor->pembayaran == 'opname') {
            $data['nominal_bayar'] = $data['timbangan_bongkar']  * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->kas_uang_jalan->customer->customer_tagihan->where('customer_id', $transaksi->kas_uang_jalan->customer->id)->where('rute_id', $transaksi->kas_uang_jalan->rute_id)->first()->opname;

            $harga = $transaksi->kas_uang_jalan->rute->jarak > 50 ? 1000 : 500;


        } elseif ($transaksi->kas_uang_jalan->vendor->pembayaran == 'titipan') {
            $data['nominal_bayar'] = $data['timbangan_bongkar']  * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->kas_uang_jalan->customer->customer_tagihan->where('customer_id', $transaksi->kas_uang_jalan->customer->id)->where('rute_id', $transaksi->kas_uang_jalan->rute_id)->first()->titipan;

            $harga = $transaksi->kas_uang_jalan->rute->jarak > 50 ? 500 : 250;
        }

        $data['nominal_bonus'] = $transaksi->tonase * $harga;

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

    public function tagihan_export(Customer $customer)
    {
        $id = $customer->id;
        return Excel::download(new TransaksiExport($id), 'customer.xlsx');
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

        $last = KasUangJalan::latest()->first();
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
            $data['nominal_tagihan'] = $data['tonase'] * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->kas_uang_jalan->customer->customer_tagihan->where('customer_id', $transaksi->kas_uang_jalan->customer->id)->where('rute_id', $transaksi->kas_uang_jalan->rute_id)->first()->harga_tagihan;
        } elseif($transaksi->kas_uang_jalan->customer->tagihan_dari == 2){
            $data['nominal_tagihan'] = $data['timbangan_bongkar'] * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->kas_uang_jalan->customer->customer_tagihan->where('customer_id', $transaksi->kas_uang_jalan->customer->id)->where('rute_id', $transaksi->kas_uang_jalan->rute_id)->first()->harga_tagihan;
        }

        if ($transaksi->kas_uang_jalan->vendor->pembayaran == 'opname') {
            $data['nominal_bayar'] = $data['timbangan_bongkar']  * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->kas_uang_jalan->customer->customer_tagihan->where('customer_id', $transaksi->kas_uang_jalan->customer->id)->where('rute_id', $transaksi->kas_uang_jalan->rute_id)->first()->opname;
            $harga = $transaksi->kas_uang_jalan->rute->jarak > 50 ? 1000 : 500;

        } elseif ($transaksi->kas_uang_jalan->vendor->pembayaran == 'titipan') {
            $data['nominal_bayar'] = $data['timbangan_bongkar']  * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->kas_uang_jalan->customer->customer_tagihan->where('customer_id', $transaksi->kas_uang_jalan->customer->id)->where('rute_id', $transaksi->kas_uang_jalan->rute_id)->first()->titipan;
            $harga = $transaksi->kas_uang_jalan->rute->jarak > 50 ? 500 : 250;
        }
        $data['nominal_bonus'] = $data['tonase'] * $harga;

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

}
