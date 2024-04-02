<?php

namespace App\Http\Controllers;

use App\Models\AktivasiMaintenance;
use App\Models\BarangMaintenance;
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

    public function barang_maintenance()
    {
        $data = BarangMaintenance::all();

        return view('database.barang-maintenance.index', [
            'data' => $data,
        ]);
    }

    public function barang_maintenance_store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
            'harga_jual' => 'required',
        ]);

        $data['harga_jual'] = str_replace('.', '', $data['harga_jual']);

        BarangMaintenance::create($data);

        return redirect()->route('database.barang-maintenance')->with('success', 'Data berhasil ditambahkan');
    }

    public function barang_maintenance_update(Request $request, BarangMaintenance $bm)
    {
        $data = $request->validate([
            'nama' => 'required',
            'harga_jual' => 'required',
        ]);

        $data['harga_jual'] = str_replace('.', '', $data['harga_jual']);

        $bm->update($data);

        return redirect()->route('database.barang-maintenance')->with('success', 'Data berhasil diubah');
    }

    public function barang_maintenance_destroy(BarangMaintenance $bm)
    {
        if($bm->stok > 0) {
            return redirect()->route('database.barang-maintenance')->with('error', 'Data tidak bisa dihapus karena masih ada stok');
        }

        $bm->delete();

        return redirect()->route('database.barang-maintenance')->with('success', 'Data berhasil dihapus');
    }

    public function aktivasi_maintenance()
    {

        $data = AktivasiMaintenance::with(['vehicle'])->get();

        $vehicleId = $data->pluck('vehicle_id')->toArray();

        $vehicles = Vehicle::whereNot('status', 'nonaktif')->whereNotIn('id', $vehicleId)->get();

        $editVehicles = Vehicle::whereNot('status', 'nonaktif')->get();

        return view('database.aktivasi-maintenance.index', [
            'data' => $data,
            'vehicles' => $vehicles,
            'editVehicles' => $editVehicles,
        ]);
    }

    public function aktivasi_maintenance_store(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'tanggal_mulai' => 'required',
        ]);

        $data['tanggal_mulai'] = date('Y-m-d', strtotime($data['tanggal_mulai']));

        AktivasiMaintenance::create($data);

        return redirect()->route('database.aktivasi-maintenance')->with('success', 'Data berhasil ditambahkan');
    }

    public function aktivasi_maintenance_update(Request $request, AktivasiMaintenance $am)
    {
        $data = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'tanggal_mulai' => 'required',
        ]);

        $data['tanggal_mulai'] = date('Y-m-d', strtotime($data['tanggal_mulai']));

        $am->update($data);

        return redirect()->route('database.aktivasi-maintenance')->with('success', 'Data berhasil diubah');
    }

    public function aktivasi_maintenance_destroy(AktivasiMaintenance $am)
    {
        $am->delete();

        return redirect()->route('database.aktivasi-maintenance')->with('success', 'Data berhasil dihapus');
    }

}
