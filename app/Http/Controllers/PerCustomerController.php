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
            'filter_date' => 'nullable|in:tanggal_muat,tanggal_bongkar',
            'tanggal_filter' => 'nullable|required_if:filter_date,tanggal_muat,tanggal_bongkar',
        ]);

        $rute_id = $req['rute_id'] ?? null;
        $filter_date = $req['filter_date'] ?? null;
        $tanggal_filter = $req['tanggal_filter'] ?? null;

        $rute = auth()->user()->customer->rute;

        $data = Transaksi::getTagihanData(auth()->user()->customer_id, $rute_id, $filter_date, $tanggal_filter);
        $customer = Customer::find(auth()->user()->customer_id);

        return view('per-customer.nota-tagihan.index', [
            'data' => $data,
            'rute' => $rute,
            'customer' => $customer,
            'rute_id' => $rute_id,
            'filter_date' => $req['filter_date'] ?? null,
            'tanggal_filter' => $req['tanggal_filter'] ?? null,

        ]);
    }

    public function nota_tagihan_print(Request $request)
    {
        $req = $request->validate([
            'rute_id' => 'nullable|exists:rutes,id',
            'filter_date' => 'nullable|in:tanggal_muat,tanggal_bongkar',
            'tanggal_filter' => 'nullable|required_if:filter_date,tanggal_muat,tanggal_bongkar',
        ]);

        $rute_id = $req['rute_id'] ?? null;
        $filter_date = $req['filter_date'] ?? null;
        $tanggal_filter = $req['tanggal_filter'] ?? null;


        $data = Transaksi::getTagihanData(auth()->user()->customer_id, $rute_id, $filter_date, $tanggal_filter);
        $customer = Customer::find(auth()->user()->customer_id);

        $pdf = PDF::loadview('per-customer.nota-tagihan.print', [
            'data' => $data,
            'customer' => $customer,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Nota Tagihan '.$customer->singkatan.'.pdf');
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

        return $pdf->download('Invoice Tagihan '.$invoice->customer->singkatan.'.pdf');
    }

    public function nota_lunas()
    {
        return view('per-customer.nota-lunas.index');
    }

    public function nota_lunas_detail(InvoiceTagihan $invoice)
    {
        $periode = $invoice->periode;
        $customer = Customer::find($invoice->customer_id);
        $data = $invoice->load(['transaksi', 'transaksi.kas_uang_jalan', 'transaksi.kas_uang_jalan.vehicle',
                                'transaksi.kas_uang_jalan.vendor', 'transaksi.kas_uang_jalan.customer', 'transaksi.kas_uang_jalan.rute'])->transaksi;

        return view('per-customer.nota-lunas.detail', [
            'data' => $invoice->transaksi,
            'customer' => $customer,
            'periode' => $periode,
            'invoice_id' => $invoice->id
        ]);
    }

    public function nota_lunas_data(Request $request)
    {

        $searchValue = $request->input('search.value');

        $query = InvoiceTagihan::where('customer_id', auth()->user()->customer_id)->where('lunas', 1);

        if ($searchValue) {
            $query = $query->where('periode', 'like', '%' . $searchValue . '%');
        }

        // if ($request->has('prodi') && !empty($request->prodi)) {
        //     $filter = $request->prodi;
        //     $query->whereIn('id_prodi', $filter);
        // }

        $recordsFiltered = $query->count();

        // $limit = (int) $request->input('length');
        // $offset = (int) $request->input('start');

        // Define the column names that correspond to the DataTables column indices
        if ($request->has('order')) {
            $orderColumn = $request->input('order.0.column');
            $orderDirection = $request->input('order.0.dir');

            // Define the column names that correspond to the DataTables column indices
            $columns = ['tanggal', 'no_invoice', 'total_tagihan'];

            // if ($columns[$orderColumn] == 'prodi') {
            //     $query = $query->join('program_studis as prodi', 'mata_kuliahs.id_prodi', '=', 'prodi.id')
            //         ->orderBy('prodi.nama_jenjang_pendidikan', $orderDirection)
            //         ->orderBy('prodi.nama_program_studi', $orderDirection)
            //         ->select('mata_kuliahs.*', 'prodi.nama_jenjang_pendidikan', 'prodi.nama_program_studi'); // Avoid column name conflicts
            // } else {
                $query = $query->orderBy($columns[$orderColumn], $orderDirection);
            // }
        }
        $data = $query->get();
        // $data = $query->skip($offset)->take($limit)->get();
        // dd($data);
        $recordsTotal = InvoiceTagihan::where('customer_id', auth()->user()->customer_id)->where('lunas', 1)->count();

        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ];

        return response()->json($response);
    }
}
