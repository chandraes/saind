<?php

namespace App\Http\Controllers;

use App\Models\AktivasiMaintenance;
use App\Models\BarangMaintenance;
use App\Models\CostOperational;
use App\Models\db\Kreditor;
use App\Models\Driver;
use App\Models\KategoriBarangMaintenance;
use App\Models\UpahGendong;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

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
            'tanggal_masuk_driver' => 'required',
            'tanggal_masuk_pengurus' => 'required',
        ]);

        $data['nominal'] = str_replace('.', '', $data['nominal']);
        $data['tanggal_masuk_driver'] = date('Y-m-d', strtotime($data['tanggal_masuk_driver']));
        $data['tanggal_masuk_pengurus'] = date('Y-m-d', strtotime($data['tanggal_masuk_pengurus']));

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
            'tanggal_masuk_driver' => 'required',
            'tanggal_masuk_pengurus' => 'required',
        ]);

        $data['nominal'] = str_replace('.', '', $data['nominal']);

        $data['tanggal_masuk_driver'] = date('Y-m-d', strtotime($data['tanggal_masuk_driver']));
        $data['tanggal_masuk_pengurus'] = date('Y-m-d', strtotime($data['tanggal_masuk_pengurus']));

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
        $data = BarangMaintenance::with(['kategori'])->get();
        $kategori = KategoriBarangMaintenance::all();

        return view('database.barang-maintenance.index', [
            'data' => $data,
            'kategori' => $kategori,
        ]);
    }

    public function kategori_store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
        ]);

        KategoriBarangMaintenance::create($data);

        return redirect()->back()->with('success', 'Data berhasil ditambahkan');
    }

    public function kategori_update(Request $request, KategoriBarangMaintenance $kategori)
    {
        $data = $request->validate([
            'nama' => 'required',
        ]);

        $kategori->update($data);

        return redirect()->back()->with('success', 'Data berhasil diubah');
    }

    public function kategori_destroy(KategoriBarangMaintenance $kategori)
    {
        if($kategori->barang_maintenance->count() > 0) {
            return redirect()->back()->with('error', 'Data tidak bisa dihapus karena masih ada barang maintenance');
        }

        $kategori->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus');
    }

    public function barang_maintenance_store(Request $request)
    {
        $data = $request->validate([
            'kategori_barang_maintenance_id' => 'required|exists:kategori_barang_maintenances,id',
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
            'kategori_barang_maintenance_id' => 'required|exists:kategori_barang_maintenances,id',
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

    public function cost_operational()
    {
        $data = CostOperational::all();

        return view('database.cost-operational.index', [
            'data' => $data
        ]);
    }

   public function cost_operational_store(Request $req)
    {
        $data = $req->validate([
            'nama' => 'required',
            'nominal' => 'required',
            'periode' => 'required|in:mingguan,bulanan',
            'jumlah_limit' => 'required|integer|min:1',
        ]);

        // Menghilangkan format titik (ex: 1.500.000 menjadi 1500000) sebelum disimpan
        $data['nominal'] = str_replace('.', '', $data['nominal']);

        CostOperational::create($data);

        return redirect()->back()->with('success', 'Data berhasil ditambahkan');
    }

    public function cost_operational_update(CostOperational $cost, Request $req)
    {
        $data = $req->validate([
            'nama' => 'required',
            'nominal' => 'required',
            'periode' => 'required|in:mingguan,bulanan',
            'jumlah_limit' => 'required|integer|min:1',
        ]);

        $data['nominal'] = str_replace('.', '', $data['nominal']);

        $cost->update($data);

        return redirect()->back()->with('success', 'Data berhasil diupdate');
    }

    public function cost_operational_delete(CostOperational $cost)
    {
        $cost->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus');
    }

    public function kreditor()
    {
        $data = Kreditor::where('is_active', 1)->get();

        return view('database.kreditor.index', [
            'data' => $data
        ]);
    }

    public function kreditor_store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
            'persen' => 'required',
            'npwp' => 'required',
            'no_rek' => 'required',
            'nama_rek' => 'required',
            'bank' => 'required',
            'apa_pph' => 'required',
        ]);

        Kreditor::create($data);

        return redirect()->back()->with('success', 'Data berhasil ditambahkan');
    }

    public function kreditor_update(Kreditor $kreditor, Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
            'persen' => 'required',
            'npwp' => 'required',
            'no_rek' => 'required',
            'nama_rek' => 'required',
            'bank' => 'required',
            'apa_pph' => 'required',
        ]);

        $kreditor->update($data);

        return redirect()->back()->with('success', 'Data berhasil diubah');
    }

    public function kreditor_destroy(Kreditor $kreditor)
    {
        $kreditor->update(['is_active' => 0]);

        return redirect()->back()->with('success', 'Data berhasil dihapus');
    }

    public function driver(Request $request)
    {
        if ($request->ajax()) {
            $query = Driver::query();

            // Filter berdasarkan status dari frontend
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('masa_berlaku_sim', function ($row) {
                    return $row->masa_berlaku_sim ? $row->masa_berlaku_sim->format('d/m/Y') : '-';
                })
                ->editColumn('foto_sim', function ($row) {
                    if ($row->foto_sim) {
                        $url = asset('storage/' . $row->foto_sim);
                        return '<a href="'.$url.'" target="_blank" class="btn btn-sm btn-outline-info"><i class="fa fa-image"></i> Lihat SIM</a>';
                    }
                    return '<span class="badge bg-secondary">Tidak Ada</span>';
                })
                ->editColumn('status', function ($row) {
                    if ($row->status === 'aktif') {
                        return '<span class="badge bg-success"><i class="fa fa-check-circle"></i> Aktif</span>';
                    }
                    return '<span class="badge bg-danger"><i class="fa fa-times-circle"></i> Non-Aktif</span>';
                })
                ->addColumn('action', function ($row) {
                    return '
                        <button onclick="editDriver('.$row->id.')" class="btn btn-warning btn-sm me-1" title="Edit"><i class="fa fa-edit"></i></button>
                        <button onclick="deleteDriver('.$row->id.')" class="btn btn-danger btn-sm" title="Hapus"><i class="fa fa-trash"></i></button>
                    ';
                })
                ->rawColumns(['foto_sim', 'status', 'action'])
                ->make(true);
        }

        return view('database.drivers.index');
    }

    public function driver_store(Request $request)
    {
        $validated = $request->validate([
            'nama'             => 'required|string|max:255',
            'no_sim'           => 'required|string|max:50|unique:drivers,no_sim',
            'masa_berlaku_sim' => 'required|date',
            'no_hp'            => 'required|string|max:20',
            'no_rek'           => 'required|string|max:50',
            'nama_rek'         => 'required|string|max:255',
            'bank'             => 'required|string|max:100',
            'alamat'           => 'required|string',
            'foto_sim'         => 'required|image|mimes:jpeg,png,jpg|max:2048', // WAJIB DIISI
            'status'           => 'required|in:aktif,non_aktif',
            'keterangan'       => 'required_if:status,non_aktif|nullable|string',
        ], [
            'foto_sim.required' => 'Foto / Dokumen SIM wajib diunggah!',
            'keterangan.required_if' => 'Keterangan wajib diisi apabila status driver Non-Aktif!'
        ]);

        if ($request->hasFile('foto_sim')) {
            $validated['foto_sim'] = $request->file('foto_sim')->store('drivers_sim', 'public');
        }

        Driver::create($validated);

        return response()->json(['message' => 'Data Driver berhasil disimpan!']);
    }

    public function driver_edit($id)
    {
        $driver = Driver::findOrFail($id);
        return response()->json($driver);
    }

    public function driver_update(Request $request, $id)
    {
        $driver = Driver::findOrFail($id);

        $validated = $request->validate([
            'nama'             => 'required|string|max:255',
            'no_sim'           => 'required|string|max:50|unique:drivers,no_sim,'.$id,
            'masa_berlaku_sim' => 'required|date',
            'no_hp'            => 'required|string|max:20',
            'no_rek'           => 'required|string|max:50',
            'nama_rek'         => 'required|string|max:255',
            'bank'             => 'required|string|max:100',
            'alamat'           => 'required|string',
            'foto_sim'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Optional saat edit (kecuali ingin diganti)
            'status'           => 'required|in:aktif,non_aktif',
            'keterangan'       => 'required_if:status,non_aktif|nullable|string',
        ], [
            'keterangan.required_if' => 'Keterangan wajib diisi apabila status driver Non-Aktif!'
        ]);

        if ($request->hasFile('foto_sim')) {
            if ($driver->foto_sim && Storage::disk('public')->exists($driver->foto_sim)) {
                Storage::disk('public')->delete($driver->foto_sim);
            }
            $validated['foto_sim'] = $request->file('foto_sim')->store('drivers_sim', 'public');
        }

        $driver->update($validated);

        return response()->json(['message' => 'Data Driver berhasil diperbarui!']);
    }

    public function driver_destroy($id)
    {
        $driver = Driver::findOrFail($id);

        if ($driver->foto_sim && Storage::disk('public')->exists($driver->foto_sim)) {
            Storage::disk('public')->delete($driver->foto_sim);
        }
        $driver->delete();

        return response()->json(['message' => 'Data Driver berhasil dihapus!']);
    }
}
