<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Customer;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransaksiExport;

class TransaksiController extends Controller
{
    public function index()
    {
        $data = Transaksi::all();
        $customer = Customer::all();
        return view('billing.transaksi.index', [
            'data' => $data,
            'customer' => $customer,
        ]);
    }

    public function nota_muat()
    {
        $data = Transaksi::where('status', 1)->get();
        return view('billing.transaksi.nota-muat', [
            'data' => $data,
        ]);
    }

    public function nota_muat_update(Request $request, Transaksi $transaksi)
    {
        $data = $request->validate([
            'nota_muat' => 'required',
            'tonase' => 'required',
        ]);

        $data['status'] = 2;
        $data['tanggal_muat'] = date('Y-m-d');

        $transaksi->update($data);

        return redirect()->back()->with('success', 'Berhasil menyimpan data!!');
    }

    public function nota_bongkar()
    {
        $data = Transaksi::where('status', 2)->get();

        return view('billing.transaksi.nota-bongkar', [
            'data' => $data,
        ]);
    }

    public function nota_bongkar_update(Request $request, Transaksi $transaksi)
    {
        // dd($request->all());
        $data = $request->validate([
            'nota_bongkar' => 'required',
            'timbangan_bongkar' => 'required',
        ]);

        // cek harga kesepakatan
        if($transaksi->kas_uang_jalan->vendor->vendor_bayar->where('customer_id', $transaksi->kas_uang_jalan->customer->id)->first() == null)
        {
           return redirect()->back()->with('error', 'Harga kesepakatan belum diisi!!');
        }

        $data['status'] = 3;
        $data['tanggal_bongkar'] = date('Y-m-d');

        if ($transaksi->kas_uang_jalan->customer->tagihan_dari == 1) {

            $data['nominal_tagihan'] = $transaksi->tonase * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->kas_uang_jalan->vendor->vendor_bayar->where('customer_id', $transaksi->kas_uang_jalan->customer->id)->first()->harga_kesepakatan;
        } elseif($transaksi->kas_uang_jalan->customer->tagihan_dari == 2){
            $data['nominal_tagihan'] = $data['timbangan_bongkar'] * $transaksi->kas_uang_jalan->rute->jarak * $transaksi->kas_uang_jalan->vendor->vendor_bayar->where('customer_id', $transaksi->kas_uang_jalan->customer->id)->first()->harga_kesepakatan;

        }

        $transaksi->update($data);

        $transaksi->kas_uang_jalan->vehicle->update([
            'status' => 'aktif',
        ]);

        return redirect()->back()->with('success', 'Berhasil menyimpan data!!');
    }

    public function nota_tagihan(Customer $customer)
    {
        $data = Transaksi::join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')->where('status', 3)
                            ->where('tagihan', 0)->where('kuj.customer_id', $customer->id)->get();

        return view('billing.transaksi.tagihan.index', [
            'data' => $data,
            'customer' => $customer,
        ]);
    }

    public function tagihan_export(Customer $customer)
    {
        $id = $customer->id;
        return Excel::download(new TransaksiExport($id), 'customer.xlsx');
    }
}
