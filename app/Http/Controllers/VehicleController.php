<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Vehicle::all();
        $vendors = Vendor::where('status', 'aktif')->get();
        $nomor_lambung = Vehicle::nextNomorLambung();
        // dd($nomor_lambung);
        return view('database.vehicle.index', [
            'data' => $data,
            'vendors' => $vendors,
            'no_lambung' => $nomor_lambung,
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
            'vendor_id' => 'required|exists:vendors,id',
            'nopol' => 'required',
            'nama_stnk' => 'required',
            'no_rangka' => 'required',
            'no_mesin' => 'required',
            'no_index' => 'required|integer',
            'tipe' => 'required',
            'tahun' => 'required',
            'no_kartu_gps' => 'required',
            'status' => 'required',
            'transfer_ke' => 'required',
            'bank' => 'required',
            'no_rekening' => 'required',
            'gps' => 'nullable',
        ]);

        $data['nomor_lambung'] = Vehicle::nextNomorLambung();
;
        $data['support_operational'] = Vendor::find($data['vendor_id'])->support_operational;

        if (array_key_exists('gps', $data)) {
            $data['gps'] = 1;
        }   else {
            $data['gps'] = 0;
        }

        if ($data['nomor_lambung'] === 1) {
            $data['nomor_lambung'] = 101;
        }

        $data['created_by'] = auth()->user()->id;

        Vehicle::create($data);

        return redirect()->route('vehicle.index')->with('success', 'Data berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vehicle $vehicle)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehicle $vehicle)
    {


        $data = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'nopol' => 'required',
            'nama_stnk' => 'required',
            'no_rangka' => 'required',
            'no_mesin' => 'required',
            'no_index' => 'required|integer',
            'tipe' => 'required',
            'tahun' => 'required',
            'no_kartu_gps' => 'required',
            'status' => 'required',
            'transfer_ke' => 'required',
            'bank' => 'required',
            'no_rekening' => 'required',
            'support_operational'=> 'nullable',
            'gps' => 'nullable',
        ]);

        // if $data has support_operational key
        $data['support_operational'] = Vendor::find($data['vendor_id'])->support_operational;

        if (array_key_exists('gps', $data)) {
            $data['gps'] = 1;

        }   else {
            $data['gps'] = 0;
        }

        $data['updated_by'] = auth()->user()->id;

        $vehicle->update($data);

        return redirect()->route('vehicle.index')->with('success', 'Data berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return redirect()->route('vehicle.index')->with('success', 'Data berhasil dihapus');
    }
}
