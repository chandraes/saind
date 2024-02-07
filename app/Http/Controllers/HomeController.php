<?php

namespace App\Http\Controllers;

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

            return view('home', [
                'tagihan' => $tagihan
            ]);

        } else {
            return view('home');
        }

    }
}
