<?php

namespace App\Http\Controllers;

use App\Models\Konfigurasi;
use App\Models\Pengaturan;
use Illuminate\Http\Request;

class KonfigurasiController extends Controller
{
    public function index()
    {
        $data = Konfigurasi::all();
        $batasanUmum = Pengaturan::all();
        return view('pengaturan.konfigurasi-transaksi', ['data' => $data, 'batasanUmum' => $batasanUmum]);
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

    public function update_batasan(Request $request, $id)
    {
        $request->validate([
            'nilai' => 'required'
        ]);

        $pengaturan = Pengaturan::findOrFail($id);

        // Hilangkan titik pada input agar murni angka sebelum masuk database
        // (Misal: 1.500.000 menjadi 1500000)
        $nilaiBersih = str_replace('.', '', $request->nilai);

        $pengaturan->update([
            'nilai' => $nilaiBersih
        ]);

        return redirect()->back()->with('success', 'Batasan Umum berhasil diperbarui!');
    }
}
