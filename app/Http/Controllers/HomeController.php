<?php

namespace App\Http\Controllers;

use App\Models\InvoiceTagihan;
use App\Models\Transaksi;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (auth()->user()->role == 'customer') {

            $db = new Transaksi();
            $tagihan = $db->countNotaTagihan(auth()->user()->customer_id);
            $invoice = InvoiceTagihan::where('customer_id', auth()->user()->customer_id)->where('lunas', 0)->count();
            return view('home', [
                'tagihan' => $tagihan,
                'invoice' => $invoice
            ]);

        } else {
            return view('home');
        }

    }
}
