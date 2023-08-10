<?php

namespace App\Http\Controllers;

use App\Models\Spk;
use App\Models\Vendor;
use App\Models\Customer;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;

class SpkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Spk::all();
        $vendors = Vendor::all();
        return view('dokumen.spk.index', [
            'data' => $data,
            'vendors' => $vendors,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_singkatan' => 'required',
            'pembayaran' => 'required',
            'vendor_id' => 'required|exists:vendors,id',
        ]);

        $data['created_by'] = auth()->user()->id;
        $data['nomor'] = Spk::count() + 1;
        $data['tanggal'] = now();
        $data['tanggal_expired'] = now()->addYear();

        Spk::create($data);

        return redirect()->route('spk.index')->with('success', 'SPK berhasil ditambahkan');

    }

    /**
     * Display the specified resource.
     */
    public function show(Spk $spk)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Spk $spk)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Spk $spk)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Spk $spk)
    {
        $spk->delete();
        return redirect()->route('spk.index')->with('success', 'SPK berhasil dihapus');
    }

    /**
     * Generate PDF.
     */
    public function spk_doc(Spk $spk)
    {
        $data = $spk;
        $customer = Customer::all();
        // make $data->tanggal_indo from $data->tanggal
        $data->tanggal_indo = Carbon::parse($data->tanggal)->isoFormat('D MMMM Y');
        // make $data->tahun from $data->tanggal
        $data->tahun = Carbon::parse($data->tanggal)->format('Y');
        $pdf = PDF::loadView('dokumen.spk.doc', [
            'data' => $data,
            'customer' => $customer,
        ]);
        return $pdf->stream('spk.pdf');
    }

}
