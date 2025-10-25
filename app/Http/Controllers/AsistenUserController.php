<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class AsistenUserController extends Controller
{
    public function perform_unit(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $offset = $request->offset ?? 0;

        $vendor = $request->vendor ?? null;
        // nama bulan dalam indonesia berdasarkan $bulan

        $db = new Transaksi();

        $all = $db->performUnit($bulan, $tahun, $offset, $vendor);

        return view('asisten-user.perform-unit', $all);
    }

    public function perform_unit_tahunan(Request $request)
    {
        $tahun = $request->tahun ?? date('Y');
        $db = new Transaksi();
        $all = $db->performUnitTahunan($tahun);
        return view('asisten-user.perform-unit-tahunan', $all);
    }

    public function upah_gendong(Request $request)
    {
        $vehicle = $request->vehicle_id;
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $tanggal_filter = $request->tanggal_filter ?? null;

        $check = Vehicle::where('id', $vehicle)->first();

        if($check == null){
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }

        $db = new Transaksi();

        $all = $db->upahGendong($vehicle, $bulan, $tahun, $tanggal_filter);

        return view('asisten-user.upah-gendong', $all);
    }
}
