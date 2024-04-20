<?php

namespace App\Http\Controllers;

use App\Models\Konfigurasi;
use Illuminate\Http\Request;

class KonfigurasiController extends Controller
{
    public function index()
    {
        $data = Konfigurasi::all();
        return view('pengaturan.konfigurasi-transaksi', ['data' => $data]);
    }

    public function update(Konfigurasi $konfigurasi)
    {
        $konfigurasi->update([
            'status' => $konfigurasi->status == 1 ? 0 : 1
        ]);

        return redirect()->back()->with('success', 'Konfigurasi berhasil diubah');
    }

    public function update_jam(Konfigurasi $konfigurasi, Request $request)
    {
        $data = $request->validate([
            'waktu_aktif' => 'required|numeric'
        ]);

        $konfigurasi->update([
            'waktu_aktif' => $data['waktu_aktif']
        ]);

        return redirect()->back()->with('success', 'Konfigurasi berhasil diubah');
    }
}
