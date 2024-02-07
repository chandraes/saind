<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PerCustomerController extends Controller
{
    public function nota_tagihan(Request $request)
    {
        $req = $request->validate([
            'rute_id' => 'nullable|exists:rutes,id',
        ]);

        $rute_id = $req['rute_id'] ?? null;

        $rute = auth()->user()->customer->rute;

        $data = Transaksi::getTagihanData(auth()->user()->customer_id, $rute_id);
        $customer = Customer::find(auth()->user()->customer_id);

        return view('per-customer.nota-tagihan.index', [
            'data' => $data,
            'rute' => $rute,
            'customer' => $customer,
            'rute_id' => $rute_id,
        ]);
    }

    public function nota_tagihan_print(Request $request)
    {
        $req = $request->validate([
            'rute_id' => 'nullable|exists:rutes,id',
        ]);

        $rute_id = $req['rute_id'] ?? null;

        $rute = auth()->user()->customer->rute;

        $data = Transaksi::getTagihanData(auth()->user()->customer_id, $rute_id);
        $customer = Customer::find(auth()->user()->customer_id);

        $pdf = PDF::loadview('per-customer.nota-tagihan.print', [
            'data' => $data,
            'customer' => $customer,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Nota Tagihan '.$customer->singkatan.'.pdf');
    }
}
