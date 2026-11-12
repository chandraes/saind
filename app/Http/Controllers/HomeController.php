<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\InvoiceTagihan;
use App\Models\Transaksi;
use App\Models\UpahGendong;
use App\Models\Vehicle;
use App\Models\Vendor;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Auth;

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
     * @return Renderable
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role == 'asisten-user') {

            $data = UpahGendong::with(['vehicle'])->get();
            $vehicle = Vehicle::whereNot('status', 'nonaktif')->get();
            $customer = Customer::all();
            $vendor = Vendor::select('id', 'nama')->where('status', 'aktif')->get();

            return view('home', [
                'data' => $data,
                'vehicle' => $vehicle,
                'customer' => $customer,
                'vendor' => $vendor,
            ]);
        }

        if ($user->role == 'customer') {

            $db = new Transaksi;
            $tagihan = $db->countNotaTagihan(Auth::user()->customer_id);
            $invoice = InvoiceTagihan::where('customer_id', Auth::user()->customer_id)->where('lunas', 0)->count();

            return view('home', [
                'tagihan' => $tagihan,
                'invoice' => $invoice,
            ]);

        }

        if ($user->role == 'customer-admin') {

            $db = new Transaksi;
            $tagihan = $db->countNotaTagihan(Auth::user()->customer_id);
            $invoice = InvoiceTagihan::where('customer_id', Auth::user()->customer_id)->where('lunas', 0)->count();

            return view('home', [
                'tagihan' => $tagihan,
                'invoice' => $invoice,
            ]);

        }

        if ($user->isOperasional()) {
            // Menu sama dengan vendor-operational (VendorDashboardMenu), untuk seluruh vendor.
            return view('home');
        }

        if ($user->isVendorAccount()) {
            abort_unless($user->vendor_id && $user->vendor()->exists(), 403, 'Akun belum terhubung dengan vendor.');

            return view('home');
        }

        return view('home');
    }
}
