<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\KasBesar;
use App\Models\Direksi;
use App\Models\RekapGaji;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FormGajiController extends Controller
{
    public function index()
    {
        $check = RekapGaji::where('bulan', date('m'))->whereYear('tahun', date('Y'))->first();

        if ($check) {
            return redirect()->route('billing.index')->with('error', 'Form Gaji Bulan Ini Sudah Dibuat');
        }
        // monthName now in indo
        $month = Carbon::now()->locale('id')->monthName;

        $data = Karyawan::where('status', 'aktif')->get();
        $direksi = Direksi::where('status', 'aktif')->get();
        return view('billing.gaji.index', [
            'data' => $data,
            'direksi' => $direksi,
            'month' => $month
        ]);
    }

    public function store(Request $requeset)
    {
        $data = $request->validate([
            'total' => 'required',
        ]);

        $data = Karyawan::where('status', 'aktif')->get();
        $direksi = Direksi::where('status', 'aktif')->get();

        $rekap = RekapGaji::create([
            'bulan' => date('m'),
            'tahun' => date('Y'),
            'total' => $request->total,
        ]);

    }
}
