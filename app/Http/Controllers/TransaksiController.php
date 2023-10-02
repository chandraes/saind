<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    public function index()
    {
        $data = Transaksi::all();
        return view('billing.transaksi.index', [
            'data' => $data,
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

        $data['status'] = 3;
        $data['tanggal_bongkar'] = date('Y-m-d');

        $transaksi->update($data);

        $transaksi->kas_uang_jalan->vehicle->update([
            'status' => 'aktif',
        ]);

        return redirect()->back()->with('success', 'Berhasil menyimpan data!!');
    }
}
