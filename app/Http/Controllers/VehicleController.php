<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Vendor;
use App\Models\KasUangJalan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // 1. Tangani Request AJAX dari DataTables
        if ($request->ajax()) {
            // Gunakan select('vehicles.*') untuk mencegah bentrok ID dengan tabel relasi
            $data = Vehicle::with(['vendor', 'kas_uang_jalan'])->select('vehicles.*');

            return DataTables::of($data)
                ->addIndexColumn() // Menambahkan kolom DT_RowIndex (Nomor Urut)

                // Kondisi: Row class 'table-warning' jika kas_uang_jalan kosong
                ->setRowClass(function ($row) {
                    return $row->kas_uang_jalan->first() == null ? 'table-warning' : '';
                })

                // Kondisi: Teks merah dan link modal untuk Nomor Lambung
                ->editColumn('nomor_lambung', function ($row) {
                    $textClass = ($row->no_index < 30 || $row->tahun < 2016) ? 'text-danger' : '';
                    // Nanti kita akan ubah cara panggil modalnya agar dinamis menggunakan class, bukan ID yang di-loop
                    return '<a href="javascript:void(0)" class="btn-show-vehicle ' . $textClass . '" data-id="' . $row->id . '">
                                <h5>' . $row->nomor_lambung . '</h5>
                            </a>';
                })

                ->editColumn('vendor_nama', function ($row) {
                    return $row->vendor ? $row->vendor->nama : '-';
                })

                ->editColumn('vendor_perusahaan', function ($row) {
                    return $row->vendor ? $row->vendor->perusahaan : '-';
                })

                // Kondisi: Teks merah untuk Index < 30
                ->editColumn('no_index', function ($row) {
                    $textClass = ($row->no_index < 30) ? 'text-danger' : '';
                    return '<span class="' . $textClass . '">' . $row->no_index . '</span>';
                })

                // Kondisi: Teks merah untuk Tahun < 2016
                ->editColumn('tahun', function ($row) {
                    $textClass = ($row->tahun < 2016) ? 'text-danger' : '';
                    return '<span class="' . $textClass . '">' . $row->tahun . '</span>';
                })

                // Kondisi: Ikon GPS
                ->editColumn('gps', function ($row) {
                    if ($row->gps == 1) {
                        return '<i class="fa fa-check-circle text-success" style="font-size: 25px"></i>';
                    }
                    return '';
                })

                // Kondisi: Badge Status
                ->editColumn('status', function ($row) {
                    if ($row->status == 'aktif') {
                        return '<h5><span class="badge bg-success">Aktif</span></h5>';
                    } elseif ($row->status == 'nonaktif') {
                        return '<h5><span class="badge bg-danger">Nonaktif</span></h5>';
                    } elseif ($row->status == 'proses') {
                        return '<h5><span class="badge bg-warning">Sedang Jalan</span></h5>';
                    }
                    return '-';
                })

                 ->addColumn('action', function ($row) {
                    $deleteUrl = route('vehicle.destroy', $row->id);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');

                    // Tombol diubah menjadi Info UJ
                    $btnRekening = '<button type="button" class="btn btn-info m-1 btn-edit-rekening text-white" data-id="' . $row->id . '" title="Edit Rekening / UJ"><i class="fa fa-bank"></i> Info UJ</button>';
                    $btnEdit = '<button type="button" class="btn btn-warning m-1 btn-edit-vehicle" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</button>';

                    $btnDelete = '
                        <form action="' . $deleteUrl . '" method="post" style="display:inline-block;">
                            ' . $csrf . '
                            ' . $method . '
                            <button type="submit" class="btn btn-danger m-1" onclick="return confirm(\'Apakah anda yakin ingin menghapus data ini?\')"><i class="fa fa-trash"></i> Hapus</button>
                        </form>
                    ';

                    return '<div class="d-flex justify-content-center">' . $btnRekening . $btnEdit . $btnDelete . '</div>';
                })
                // Beritahu Yajra kolom mana saja yang memuat tag HTML agar tidak di-escape
                ->rawColumns(['nomor_lambung', 'no_index', 'tahun', 'gps', 'status', 'action'])
                ->make(true);
        }

        // 2. Load View Awal (Hanya mengirim variabel pendukung, BUKAN semua data Vehicle)
        $vendors = Vendor::where('status', 'aktif')->get();
        $nomor_lambung = Vehicle::nextNomorLambung();
        $assignedDriverIds = Vehicle::whereNotNull('driver_id')->pluck('driver_id')->toArray();
        // 2. Ambil Driver yang ID-nya TIDAK ADA dalam daftar ID yang sudah terpakai
        $availableDrivers = Driver::whereNotIn('id', $assignedDriverIds)->get();

        return view('database.vehicle.index', [
            'vendors' => $vendors,
            'no_lambung' => $nomor_lambung,
            'drivers' => $availableDrivers
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
            'nopol' => 'required|unique:vehicles,nopol',
            'nama_stnk' => 'required',
            'no_rangka' => 'required|unique:vehicles,no_rangka',
            'no_mesin' => 'required|unique:vehicles,no_mesin',
            'no_index' => 'required|integer',
            'tipe' => 'required',
            'tahun' => 'required',
            'no_kartu_gps' => 'required',
            'status' => 'required',
            'transfer_ke' => 'nullable|required_if:uj_ditahan,0',
            'bank'        => 'nullable|required_if:uj_ditahan,0',
            'no_rekening' => 'nullable|required_if:uj_ditahan,0',
            'gps' => 'nullable',
            'tanggal_pajak_stnk' => 'required',
            'tanggal_kir' => 'required',
            'tanggal_kimper' => 'required',
            'tanggal_sim' => 'required',
            'lock_uj' => 'required|boolean',
            'uj_ditahan'  => 'required|boolean',
            'driver_id'   => 'nullable|required_if:uj_ditahan,1|unique:vehicles,driver_id',
        ]);

        if ($data['uj_ditahan'] == 1) {
            $data['transfer_ke'] = null;
            $data['bank'] = null;
            $data['no_rekening'] = null;
        } else {
            $data['driver_id'] = null;
        }

        $data['tanggal_pajak_stnk'] = date('Y-m-d', strtotime($data['tanggal_pajak_stnk']));
        $data['tanggal_kir'] = date('Y-m-d', strtotime($data['tanggal_kir']));
        $data['tanggal_kimper'] = date('Y-m-d', strtotime($data['tanggal_kimper']));
        $data['tanggal_sim'] = date('Y-m-d', strtotime($data['tanggal_sim']));

        $data['nomor_lambung'] = Vehicle::nextNomorLambung();

        $data['support_operational'] = Vendor::find($data['vendor_id'])->support_operational;

        if (array_key_exists('gps', $data)) {
            $data['gps'] = 1;
        }   else {
            $data['gps'] = 0;
        }


        if ($data['nomor_lambung'] === 1) {
            $data['nomor_lambung'] = 101;
        }

        $data['created_by'] = Auth::user()->id;

        Vehicle::create($data);

        return redirect()->route('vehicle.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function show(Vehicle $vehicle)
    {
        // Ambil vendor aktif ATAU vendor yang sedang dipakai oleh vehicle ini
        $vendors = Vendor::where('status', 'aktif')
                         ->orWhere('id', $vehicle->vendor_id)
                         ->get();

        return view('database.vehicle.show', [
            'd' => $vehicle,
            'vendors' => $vendors
        ]);
    }

    public function edit(Vehicle $vehicle)
    {
        // Ambil vendor aktif ATAU vendor yang sedang dipakai oleh vehicle ini
        $vendors = Vendor::where('status', 'aktif')
                         ->orWhere('id', $vehicle->vendor_id)
                         ->get();

        // Ambil ID Driver yang terpakai OLEH KENDARAAN LAIN (selain kendaraan yang sedang di-edit ini)
        $assignedDriverIds = Vehicle::whereNotNull('driver_id')
                                    ->where('id', '!=', $vehicle->id)
                                    ->pluck('driver_id')->toArray();

        $availableDrivers = Driver::whereNotIn('id', $assignedDriverIds)->get();

        return view('database.vehicle.edit', [
            'd' => $vehicle,
            'vendors' => $vendors,
            'drivers' => $availableDrivers // Lempar ke view
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        if ($vehicle->status == 'proses') {
            return redirect()->back()->with('error', 'Data tidak dapat diubah karena status sedang jalan');
        }

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
            'transfer_ke' => 'nullable|required_if:uj_ditahan,0',
            'bank'        => 'nullable|required_if:uj_ditahan,0',
            'no_rekening' => 'nullable|required_if:uj_ditahan,0',
            'support_operational'=> 'nullable',
            'gps' => 'nullable',
            'tanggal_pajak_stnk' => 'required',
            'tanggal_kir' => 'required',
            'tanggal_kimper' => 'required',
            'tanggal_sim' => 'required',
            'lock_uj' => 'required|boolean',
            'uj_ditahan'  => 'required|boolean',
            'driver_id'   => 'nullable|required_if:uj_ditahan,1|unique:vehicles,driver_id,' . $vehicle->id,
        ]);

        // Bersihkan data sesuai kondisi
        if ($data['uj_ditahan'] == 1) {
            // $data['transfer_ke'] = null;
            // $data['bank'] = null;
            // $data['no_rekening'] = null;
        } else {
            $data['driver_id'] = null;
        }

        $checker = KasUangJalan::where('vehicle_id', $vehicle->id)->first();

        // if ($checker && $data['vendor_id'] != $vehicle->vendor_id) {
        //     return redirect()->back()->with('error', 'Data tidak dapat diubah karena sudah ada transaksi');
        // }

        // if $data has support_operational key
        $data['support_operational'] = Vendor::find($data['vendor_id'])->support_operational;

        if (array_key_exists('gps', $data)) {
            $data['gps'] = 1;

        }   else {
            $data['gps'] = 0;
        }

        $data['tanggal_pajak_stnk'] = date('Y-m-d', strtotime($data['tanggal_pajak_stnk']));
        $data['tanggal_kir'] = date('Y-m-d', strtotime($data['tanggal_kir']));
        $data['tanggal_kimper'] = date('Y-m-d', strtotime($data['tanggal_kimper']));
        $data['tanggal_sim'] = date('Y-m-d', strtotime($data['tanggal_sim']));


        $data['updated_by'] = Auth::user()->id;
        // update data vehicle and if database error, return to previous page with error message
        try {
            $vehicle->update($data);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Terdapat nopol, no rangka, atau no mesin yang sama. '. $th->getMessage());
        }
        // $vehicle->update($data);


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

    public function print_preview_vehicle()
    {
        $data = Vehicle::all();

        $pdf = PDF::loadview('database.vehicle.preview-vehicle', [
            'data' => $data,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Daftar Vehicle.pdf');
    }

    public function editRekening(Vehicle $vehicle)
    {
        // Logika sama seperti edit
        $assignedDriverIds = Vehicle::whereNotNull('driver_id')
                                    ->where('id', '!=', $vehicle->id)
                                    ->pluck('driver_id')->toArray();
        $availableDrivers = Driver::whereNotIn('id', $assignedDriverIds)->get();

        return view('database.vehicle.edit-rekening', [
            'd' => $vehicle,
            'drivers' => $availableDrivers
        ]);
    }

    /**
     * Memproses update data rekening saja
     */
    public function updateRekening(Request $request, Vehicle $vehicle)
    {
        // Validasi input
        $data = $request->validate([
            'uj_ditahan'  => 'required|boolean',
            'driver_id'   => 'nullable|required_if:uj_ditahan,1|unique:vehicles,driver_id,' . $vehicle->id,
            'transfer_ke' => 'nullable|required_if:uj_ditahan,0|string',
            'bank'        => 'nullable|required_if:uj_ditahan,0|string',
            'no_rekening' => 'nullable|required_if:uj_ditahan,0|string',
        ]);

        $data['updated_by'] = Auth::user()->id;

        if ($data['uj_ditahan'] == 1) {
            $data['transfer_ke'] = null;
            $data['bank'] = null;
            $data['no_rekening'] = null;
        } else {
            $data['driver_id'] = null;
        }

        // Update hanya data rekening
        try {
            $vehicle->update($data);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data rekening. ' . $th->getMessage());
        }

        return redirect()->route('vehicle.index')->with('success', 'Data Rekening berhasil diperbarui!');
    }
}
