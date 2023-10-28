<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Direksi;
use Illuminate\Http\Request;

class FormGajiController extends Controller
{
    public function index()
    {
        $data = Karyawan::where('status', 'aktif')->get();
        $direksi = Direksi::where('status', 'aktif')->get();
        return view('billing.gaji.index', [
            'data' => $data,
            'direksi' => $direksi
        ]);
    }

    public function store()
    {

    }
}
