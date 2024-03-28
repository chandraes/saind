<?php

namespace App\Http\Controllers;

use App\Models\InvoiceTagihan;
use App\Models\Transaksi;
use App\Models\UpahGendong;
use App\Models\Vehicle;
use App\Models\Vendor;
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
        $user = auth()->user();

        if ($user->role == 'customer') {

            $db = new Transaksi();
            $tagihan = $db->countNotaTagihan(auth()->user()->customer_id);
            $invoice = InvoiceTagihan::where('customer_id', auth()->user()->customer_id)->where('lunas', 0)->count();
            return view('home', [
                'tagihan' => $tagihan,
                'invoice' => $invoice
            ]);

        }

        if ($user->role == 'operasional') {
            $db = Vendor::all();
            $ug = UpahGendong::all();
            return view('home', ['vendor' => $db, 'ug' => $ug]);
        }

        if ($user->role == 'vendor') {
            $vehicle = Vehicle::where('vendor_id', auth()->user()->vendor_id)->pluck('id');
            $ug = UpahGendong::whereIn('vehicle_id', $vehicle)->get();
            return view('home', ['ug' => $ug]);
        }

        return view('home');


    }
}
