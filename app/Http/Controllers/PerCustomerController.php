<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\InvoiceTagihan;
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

    public function invoice()
    {
        $data = InvoiceTagihan::where('customer_id', auth()->user()->customer_id)->where('lunas', 0)->get();
        return view('per-customer.invoice-tagihan.index', [
            'data' => $data,
        ]);
    }

    public function invoice_detail(InvoiceTagihan $invoice)
    {
        $periode = $invoice->periode;
        $customer = Customer::find($invoice->customer_id);

        $data = $invoice->transaksi;

        return view('per-customer.invoice-tagihan.detail', [
            'data' => $data,
            'periode' => $periode,
            'customer' => $customer,
            'invoice_id' => $invoice->id
        ]);
    }

    public function invoice_export(InvoiceTagihan $invoice)
    {
        $data = $invoice->transaksi;
        $customer = Customer::find($invoice->customer_id);

        // get latest data from month before current month
        // dd($bulan);
        $pdf = PDF::loadview('per-customer.invoice-tagihan.export', [
            'data' => $data,
            'invoice' => $invoice,
            'customer' => $customer,
            'periode' => $invoice->periode,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Invoice Tagihan '.$invoice->customer->singkatan.'.pdf');
    }
}
