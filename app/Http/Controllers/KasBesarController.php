<?php

namespace App\Http\Controllers;

use App\Models\KasBesar;
use Illuminate\Http\Request;

class KasBesarController extends Controller
{
    public function index()
    {
        $month = date('m');

        $data = KasBesar::whereMonth('tanggal', $month)->get();

        // dapatkan saldo terakhir dari bulan sebelumnya
        $saldo = KasBesar::whereMonth('tanggal', $month - 1)->latest()->orderBy('id', 'desc')->first();

        // jika tidak ada saldo terakhir, maka saldo awal adalah 0
        if (!$saldo) {
            $saldo = 0;
        } else {
            $saldo = $saldo->saldo;
        }

        return view('billing.kas-besar.index', [
            'data' => $data,
            'saldo' => $saldo,
        ]);
    }
}
