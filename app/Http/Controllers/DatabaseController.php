<?php

namespace App\Http\Controllers;

use App\Models\UpahGendong;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class DatabaseController extends Controller
{
    public function index()
    {
        return view('database.index');
    }

    public function upah_gendong()
    {
        $data = UpahGendong::all();
        $vehicleId = $data->pluck('vehicle_id')->toArray();
        $vehicles = Vehicle::whereNot('status', 'nonaktif')->whereNotIn('id', $vehicleId)->get();
        $editVehicles = Vehicle::whereNot('status', 'nonaktif')->get();

        return view('database.upah-gendong.index', [
            'data' => $data,
            'vehicles' => $vehicles,
            'editVehicles' => $editVehicles,
        ]);
    }

    public function upah_gendong_store(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'nominal' => 'required',
            'tonase_min' => 'required|integer', // Add this line
            'nama_driver' => 'required',
            'nama_pengurus' => 'required',
            'no_rek' => 'required',
            'bank' => 'required',
            'nama_rek' => 'required',
        ]);

        $data['nominal'] = str_replace('.', '', $data['nominal']);

        UpahGendong::create($data);

        return redirect()->route('database.upah-gendong')->with('success', 'Data berhasil ditambahkan');
    }

    public function upah_gendong_update(UpahGendong $ug, Request $request)
    {

        // dd($request->all());
        $data = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'nominal' => 'required',
            'tonase_min' => 'required', // Add this line
            'nama_driver' => 'required',
            'nama_pengurus' => 'required',
            'no_rek' => 'required',
            'bank' => 'required',
            'nama_rek' => 'required',
        ]);

        $data['nominal'] = str_replace('.', '', $data['nominal']);

        $ug->update($data);

        return redirect()->route('database.upah-gendong')->with('success', 'Data berhasil diubah');
    }

    public function upah_gendong_destroy(UpahGendong $ug)
    {
        $ug->delete();

        return redirect()->route('database.upah-gendong')->with('success', 'Data berhasil dihapus');
    }

}
