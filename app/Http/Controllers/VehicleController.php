<?php

namespace App\Http\Controllers;

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

                // Action Buttons
                ->addColumn('action', function ($row) {
                    // Tombol Hapus (Form)
                    $deleteUrl = route('vehicle.destroy', $row->id);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');

                    // Kita gunakan class "btn-edit-vehicle" untuk trigger modal via AJAX nanti
                    $btnEdit = '<button type="button" class="btn btn-warning m-2 btn-edit-vehicle" data-id="' . $row->id . '">Edit</button>';

                    $btnDelete = '
                        <form action="' . $deleteUrl . '" method="post" style="display:inline-block;">
                            ' . $csrf . '
                            ' . $method . '
                            <button type="submit" class="btn btn-danger m-2" onclick="return confirm(\'Apakah anda yakin ingin menghapus data ini?\')">Hapus</button>
                        </form>
                    ';

                    return $btnEdit . $btnDelete;
                })
                ->addColumn('action', function ($row) {
                    $deleteUrl = route('vehicle.destroy', $row->id);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');

                    // Tombol khusus Rekening (Warna Info/Biru Muda)
                    $btnRekening = '<button type="button" class="btn btn-info m-1 btn-edit-rekening text-white" data-id="' . $row->id . '" title="Edit Rekening"><i class="fa fa-university"></i> Rekening</button>';

                    // Tombol Edit
                    $btnEdit = '<button type="button" class="btn btn-warning m-1 btn-edit-vehicle" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</button>';

                    // Tombol Hapus
                    $btnDelete = '
                        <form action="' . $deleteUrl . '" method="post" style="display:inline-block;">
                            ' . $csrf . '
                            ' . $method . '
                            <button type="submit" class="btn btn-danger m-1" onclick="return confirm(\'Apakah anda yakin ingin menghapus data ini?\')"><i class="fa fa-trash"></i> Hapus</button>
                        </form>
                    ';

                    // Gabungkan semua tombol
                    return '<div class="d-flex justify-content-center">' . $btnRekening . $btnEdit . $btnDelete . '</div>';
                })
                // Beritahu Yajra kolom mana saja yang memuat tag HTML agar tidak di-escape
                ->rawColumns(['nomor_lambung', 'no_index', 'tahun', 'gps', 'status', 'action'])
                ->make(true);
        }

        // 2. Load View Awal (Hanya mengirim variabel pendukung, BUKAN semua data Vehicle)
        $vendors = Vendor::where('status', 'aktif')->get();
        $nomor_lambung = Vehicle::nextNomorLambung();

        return view('database.vehicle.index', [
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
            'nopol' => 'required|unique:vehicles,nopol',
            'nama_stnk' => 'required',
            'no_rangka' => 'required|unique:vehicles,no_rangka',
            'no_mesin' => 'required|unique:vehicles,no_mesin',
            'no_index' => 'required|integer',
            'tipe' => 'required',
            'tahun' => 'required',
            'no_kartu_gps' => 'required',
            'status' => 'required',
            'transfer_ke' => 'required',
            'bank' => 'required',
            'no_rekening' => 'required',
            'gps' => 'nullable',
            'tanggal_pajak_stnk' => 'required',
            'tanggal_kir' => 'required',
            'tanggal_kimper' => 'required',
            'tanggal_sim' => 'required',
            'lock_uj' => 'required|boolean',
        ]);

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

        return view('database.vehicle.edit', [
            'd' => $vehicle,
            'vendors' => $vendors
        ]);
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
            'tanggal_pajak_stnk' => 'required',
            'tanggal_kir' => 'required',
            'tanggal_kimper' => 'required',
            'tanggal_sim' => 'required',
            'lock_uj' => 'required|boolean',
        ]);

        if ($vehicle->status == 'proses') {
            return redirect()->back()->with('error', 'Data tidak dapat diubah karena status sedang jalan');
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
        return view('database.vehicle.edit-rekening', [
            'd' => $vehicle
        ]);
    }

    /**
     * Memproses update data rekening saja
     */
    public function updateRekening(Request $request, Vehicle $vehicle)
    {
        // Validasi input
        $data = $request->validate([
            'transfer_ke' => 'required|string',
            'bank'        => 'required|string',
            'no_rekening' => 'required|string',
        ]);

        $data['updated_by'] = Auth::user()->id;

        // Update hanya data rekening
        try {
            $vehicle->update($data);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data rekening. ' . $th->getMessage());
        }

        return redirect()->route('vehicle.index')->with('success', 'Data Rekening berhasil diperbarui!');
    }
}
