<?php

namespace App\Http\Controllers;

use App\Models\Kontrak;
use App\Models\Vendor;
use App\Models\Customer;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;


class KontrakController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kontraks = Kontrak::all();
        $vendors = Vendor::where('status', 'aktif')->get();
        return view('dokumen.kontrak.index', [
            'vendors' => $vendors,
            'kontraks' => $kontraks,
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
            'vendor_id' => 'required',
        ]);

        $data['created_by'] = auth()->user()->id;
        $data['nomor'] = Kontrak::count() + 1;
        $data['tanggal'] = now();

        Kontrak::create($data);

        return redirect()->route('kontrak.index')->with('success', 'Kontrak berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Kontrak $kontrak)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kontrak $kontrak)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kontrak $kontrak)
    {

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kontrak $kontrak)
    {
        // delete kontrak
        $kontrak->delete();

        return redirect()->route('kontrak.index')->with('success', 'Kontrak berhasil dihapus');
    }

    public function kontrak_doc(Kontrak $kontrak)
    {
        $data = $kontrak;

        $customer = Customer::select('nama', 'singkatan', 'harga_opname', 'harga_titipan')->get();
        // take year from tanggal
        $data->tahun = Carbon::parse($data->tanggal)->locale('id')->isoFormat('YYYY');

        // buat penyebutan angka hari dari tanggal
        $data->hari = Carbon::parse($data->tanggal)->locale('id')->isoFormat('dddd');

        $data->hari_angka = Carbon::parse($data->tanggal)->locale('id')->isoFormat('d');

        // buat penyebutan angka bulan dari tanggal
        $data->bulan = Carbon::parse($data->tanggal)->locale('id')->isoFormat('MMMM');

        $data->tanggal_string = Carbon::parse($data->tanggal)->locale('id')->isoFormat('D MMMM YYYY');

        $pdf = Pdf::loadview('dokumen.kontrak.doc', [
            'data' => $data,
            'customer' => $customer,
        ]);

        // if $data->dokumen_kontrak is null, then create new one then save pdf and put into storage and add uuid to suffix name
        // if ($data->dokumen_kontrak == null) {
        //     $data->dokumen_kontrak = $data->tahun.'- Kontrak '.$data->vendor->nama.Uuid::uuid4()->toString() . '.pdf';
        //     $data->save();
        //     $pdf->save(storage_path('app/public/kontrak/' . $data->dokumen_kontrak));
        // }


        return $pdf->stream('Kontrak '.$data->vendor->perusahaan.'.pdf');
    }
}
