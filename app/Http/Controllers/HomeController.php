<?php

namespace App\Http\Controllers;

use App\Models\AktivasiMaintenance;
use App\Models\Customer;
use App\Models\InvoiceAddVendor;
use App\Models\InvoiceBayar;
use App\Models\InvoiceTagihan;
use App\Models\Transaksi;
use App\Models\UpahGendong;
use App\Models\Vehicle;
use App\Models\Vendor;
use Illuminate\Http\Request;
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
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role == 'asisten-user') {

             $data = UpahGendong::with(['vehicle'])->get();
            $vehicle = Vehicle::whereNot('status', 'nonaktif')->get();
            $customer = Customer::all();
            $vendor = Vendor::select('id','nama')->where('status', 'aktif')->get();
            return view('home', [
                'data' => $data,
                'vehicle' => $vehicle,
                'customer' => $customer,
                'vendor' => $vendor
            ]);
        }

        if ($user->role == 'customer') {

            $db = new Transaksi();
            $tagihan = $db->countNotaTagihan(Auth::user()->customer_id);
            $invoice = InvoiceTagihan::where('customer_id', Auth::user()->customer_id)->where('lunas', 0)->count();
            return view('home', [
                'tagihan' => $tagihan,
                'invoice' => $invoice
            ]);

        }

        if ($user->role == 'customer-admin') {

            $db = new Transaksi();
            $tagihan = $db->countNotaTagihan(Auth::user()->customer_id);
            $invoice = InvoiceTagihan::where('customer_id', Auth::user()->customer_id)->where('lunas', 0)->count();

            return view('home', [
                'tagihan' => $tagihan,
                'invoice' => $invoice
            ]);

        }

        if ($user->role == 'operasional') {
            $db = Vendor::all();
            $ug = UpahGendong::all();
            $maintenance = AktivasiMaintenance::with(['vehicle'])
            ->get();
            $customer = Customer::where('status', 1)->get();
            $vehicle = Vehicle::whereNot('status', 'nonaktif')->get();
            return view('home', ['vendor' => $db, 'ug' => $ug, 'maintenance' => $maintenance, 'customer' => $customer, 'vehicle' =>$vehicle]);
        }

        if ($user->role == 'vendor') {
            $v = Vehicle::where('vendor_id', Auth::user()->vendor_id)->pluck('id');
            $vehicle = Vehicle::where('vendor_id', Auth::user()->vendor_id)->whereNot('status', 'nonaktif')->get();
            $invoiceAdd = InvoiceAddVendor::where('vendor_id', Auth::user()->vendor_id)->where('is_finished', 0)->count();
            $bayar = InvoiceBayar::where('vendor_id', Auth::user()->vendor_id)->where('lunas', 0)->count() + $invoiceAdd;

            return view('home', [
                'bayar' => $bayar,
                'vehicle' => $vehicle,

            ]);
        }

        if ($user->role == 'vendor-operational') {
            $v = Vehicle::where('vendor_id', Auth::user()->vendor_id)->pluck('id');
            $vehicle = Vehicle::where('vendor_id', Auth::user()->vendor_id)->whereNot('status', 'nonaktif')->get();
            $ug = UpahGendong::whereIn('vehicle_id', $v)->get();
            $maintenance = AktivasiMaintenance::with(['vehicle'])
                        ->whereHas('vehicle', function ($query) {
                            $query->where('vendor_id', Auth::user()->vendor_id);
                        })
                        ->get();
            return view('home', [
                'ug' => $ug,
                'vehicle' => $vehicle,
                'maintenance' => $maintenance
            ]);
        }

        return view('home');
    }
}
