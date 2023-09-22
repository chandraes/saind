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
}
