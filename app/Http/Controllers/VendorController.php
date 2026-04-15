<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\Customer;
use App\Models\VendorBayar;
use App\Models\VendorUangJalan;
use App\Models\Rute;
use App\Models\CustomerRute;
use App\Models\Sponsor;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class VendorController extends Controller
{

    public function index(Request $request)
    {
        // Jika ada request AJAX dari Datatables
        if ($request->ajax()) {
            $query = Vendor::with(['sponsor', 'vendor_uang_jalan.rute']);

            if (Auth::user()->role == 'vendor') {
                $query->where('vendor_id', Auth::user()->vendor_id);
            }

            return DataTables::of($query)
                ->addIndexColumn() // Untuk nomor urut otomatis (DT_RowIndex)
                ->editColumn('nama', function ($d) {
                    return '<a href="' . route('vendor.show', $d->id) . '"><strong>' . $d->nama . '</strong></a>';
                })
                ->editColumn('pembayaran', function ($d) {
                    $class = in_array($d->pembayaran, ['titipan', 'titipan_khusus']) ? 'text-danger' : '';
                    return '<span class="' . $class . '">' . strtoupper(str_replace('_', ' ', $d->pembayaran)) . '</span>';
                })
                ->editColumn('support_operational', function ($d) {
                    return $d->support_operational == 1 ? '<i class="fa fa-check-circle text-success" style="font-size: 25px"></i>' : '';
                })
                ->editColumn('ppn', function ($d) {
                    return $d->ppn == 1 ? '<i class="fa fa-check-circle text-success" style="font-size: 25px"></i>' : '';
                })
                ->editColumn('pph', function ($d) {
                    return $d->pph == 1 ? '<i class="fa fa-check-circle text-success" style="font-size: 25px"></i>' : '';
                })
                ->editColumn('plafon_titipan', function ($d) {
                    $class = $d->pembayaran == 'titipan' ? 'text-danger' : '';
                    return '<span class="' . $class . '">' . number_format($d->plafon_titipan, 0, ',', '.') . '</span>';
                })
                ->editColumn('plafon_lain', function ($d) {
                    $class = $d->plafon_lain > 10000000 ? 'text-danger' : '';
                    return '<span class="' . $class . '">' . number_format($d->plafon_lain, 0, ',', '.') . '</span>';
                })
                ->editColumn('status', function ($d) {
                    if ($d->status == "aktif") {
                        return '<span class="badge badge-xl rounded-pill text-bg-success">Aktif</span>';
                    } elseif ($d->status === "nonaktif") {
                        return '<span class="badge rounded-pill text-bg-danger">Non Aktif</span>';
                    }
                    return '';
                })
              ->editColumn('limit_tonase', function ($d) {
                    // Cek apakah nilainya 1/true di database
                    $checked = $d->limit_tonase ? 'checked' : '';
                    // Simpan URL route ke dalam data attribute
                    $url = route('vendor.toggle-limit-tonase', $d->id);

                    // Menggunakan checkbox standar dengan ukuran yang sedikit diperbesar agar mudah diklik
                    return '
                    <div class="d-flex justify-content-center align-items-center">
                        <input class="form-check-input border border-secondary toggle-limit-tonase m-0" type="checkbox" value="" data-url="'.$url.'" '.$checked.' style="cursor: pointer; width: 2em; height: 2em;">
                    </div>';
                })
                ->addColumn('sponsor_nama', function ($d) {
                    return $d->sponsor ? $d->sponsor->nama : '';
                })
                ->addColumn('uang_jalan', function ($d) {
                    // Kita pindahkan tombol dan modal ke view parsial agar controller tetap bersih
                    return view('database.vendor.partials.uang_jalan_btn', compact('d'))->render();
                })
                ->addColumn('action', function ($d) {
                    return view('database.vendor.partials.action_btn', compact('d'))->render();
                })
                ->rawColumns(['nama', 'pembayaran', 'support_operational', 'ppn', 'pph', 'plafon_titipan', 'plafon_lain', 'status', 'uang_jalan', 'action', 'limit_tonase'])
                ->make(true);
        }

        // Jika load halaman biasa, jangan query datanya (biarkan JS yang memanggil via AJAX)
        return view('database.vendor.index');
    }

    public function toggleLimitTonase($id)
    {
        $vendor = Vendor::findOrFail($id);
        // Ubah status kebalikannya (jika 1 jadi 0, jika 0 jadi 1)
        $vendor->limit_tonase = !$vendor->limit_tonase;
        $vendor->save();

        return response()->json([
            'success' => true,
            'message' => 'Limit Tonase Muat berhasil diperbarui!'
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     if (Auth::user()->role == 'admin' || Auth::user()->role == 'su') {
    //         $vendors = Vendor::with(['sponsor', 'vendor_uang_jalan', 'vendor_uang_jalan.rute'])->get();
    //     }

    //     if(Auth::user()->role == 'vendor') {
    //         $vendors = Vendor::where('vendor_id', Auth::user()->vendor_id)->get();
    //     }

    //     $customers = Customer::all();

    //     return view('database.vendor.index', [
    //         'vendors' => $vendors,
    //         'customers' => $customers,
    //     ]);
    // }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::all();
        $sponsor = Sponsor::all();

        return view('database.vendor.create', [
            'customers' => $customers,
            'sponsor' => $sponsor,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $data = $request->validate([
            'nama' => 'required|min:3',
            'nickname' => 'required|min:3',
            'tipe' => 'required',
            'jabatan' => 'required',
            'perusahaan' => 'nullable',
            'npwp' => 'required',
            'alamat' => 'required',
            'no_hp' => 'required',
            'email' => 'required',
            'no_rekening' => 'required',
            'bank' => 'required',
            'nama_rekening' => 'required',
            'status' => 'required',
            'sponsor_id' => 'nullable',
            'ppn' => 'nullable',
            'pph' => 'nullable',
            'pembayaran' => 'required',
            'plafon_titipan' => 'required',
            'plafon_lain' => 'required',
            'support_operational' => 'nullable',
            'pph_val' => 'nullable|required_if:pph,on',
        ]);


        if (array_key_exists('ppn', $data)) {
            $data['ppn'] = 1;
        } else {
            $data['ppn'] = 0;
        }

        // if (array_key_exists('pph', $data)) {
        //     $data['pph'] = 1;
        // } else {
        //     $data['pph'] = 0;
        // }
          if (array_key_exists('pph', $data)) {
            $data['pph'] = 1;
            $data['pph_val'] = str_replace(',', '.', $data['pph_val']);
            if ($data['pph_val'] == 0 ) {
                return redirect()->back()->withInput()->withErrors(['pph_val' => 'Nilai PPh harus lebih dari 0 jika PPh dicentang']);
            }
        } else {
            $data['pph_val'] = 0;
            $data['pph'] = 0;
        }

        // dd($data);

        if(array_key_exists('support_operational', $data)){
            $data['support_operational'] = 1;
        } else {
            $data['support_operational'] = 0;
        }

        $data['plafon_titipan'] = str_replace('.', '', $data['plafon_titipan']);
        $data['plafon_lain'] = str_replace('.', '', $data['plafon_lain']);
        // dd($data);

        $data['user_id'] = Auth::user()->id;

        $store = Vendor::create($data);

        $id = $store->id;

        return redirect()->route('uj.vendor.uang-jalan', $id);
    }

    /**
     * Display the specified resource.
     */
    public function show(Vendor $vendor)
    {
        $sponsor = Sponsor::all();
        return view('database.vendor.show', [
            'vendor' => $vendor,
            'sponsor' => $sponsor,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $vendor = Vendor::findOrFail($id);
        $sponsor = Sponsor::all();
        return view('database.vendor.edit', [
            'vendor' => $vendor,
            'sponsor' => $sponsor,
        ]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $data = $request->validate([
            'nama' => 'required|min:3',
            'nickname' => 'required|min:3',
            'tipe' => 'required|in:perusahaan,perorangan',
            'jabatan' => 'required',
            'perusahaan' => 'nullable',
            'npwp' => 'required',
            'alamat' => 'required',
            'no_hp' => 'required',
            'email' => 'required',
            'no_rekening' => 'required',
            'bank' => 'required',
            'nama_rekening' => 'required',
            'status' => 'required',
            'sponsor_id' => 'nullable',
            'ppn' => 'nullable',
            'pph' => 'nullable',
            'pembayaran' => 'required',
            'plafon_titipan' => 'required',
            'plafon_lain' => 'required',
            'support_operational' => 'nullable',
            'pph_val' => 'nullable|required_if:pph,on',
        ]);

        if (array_key_exists('ppn', $data)) {
            $data['ppn'] = 1;
        } else {
            $data['ppn'] = 0;
        }

        if (array_key_exists('pph', $data)) {
            $data['pph'] = 1;
            $data['pph_val'] = str_replace(',', '.', $data['pph_val']);
            if ($data['pph_val'] == 0 ) {
                return redirect()->back()->withInput()->withErrors(['pph_val' => 'Nilai PPh harus lebih dari 0 jika PPh dicentang']);
            }
        } else {
            $data['pph_val'] = 0;
            $data['pph'] = 0;
        }

        if(array_key_exists('support_operational', $data)){
            $data['support_operational'] = 1;
        } else {
            $data['support_operational'] = 0;
        }

        $vendor = Vendor::findOrFail($id);

        $data['plafon_titipan'] = str_replace('.', '', $data['plafon_titipan']);
        $data['plafon_lain'] = str_replace('.', '', $data['plafon_lain']);

        $vendor->update($data);

        if($data['status'] == 'nonaktif'){
            $vendor->vehicle()->update([
                'status' => 'nonaktif',
            ]);
        } elseif($data['status'] == 'aktif'){
            $vendor->vehicle()->update([
                'status' => 'aktif',
            ]);
        }

        return redirect()->route('vendor.index')->with('success', 'Vendor berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vendor $vendor)
    {

        $vendor->delete();

        return redirect()->route('vendor.index')->with('success', 'Vendor berhasil dihapus');
    }

    public function uang_jalan($id)
    {
        $rutes = Rute::all();

        return view('database.vendor.create-uangjalan', [
            'id' => $id,
            'rutes' => $rutes,
        ]);
    }

    public function uang_jalan_store(Request $request)
    {
        $data = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'rute_id' => 'required',
            'rute_id.*' => 'required|exists:rutes,id',
            'uang_jalan' => 'required',
            'uang_jalan.*' => 'required',
        ]);

        $id = $data['vendor_id'];
        $checkRole = Auth::user()->role;

        $role = ['admin', 'su'];

        if (!in_array($checkRole, $role)) {
           for ($i=0; $i < count($data['hk_opname']); $i++) {
                if ($data['hk_opname'][$i] != Rute::find($data['rute_id'][$i])->uang_jalan) {
                    return redirect()->back()->with('error', 'Harga opname tidak sesuai');
                }
                if ($data['hk_titipan'][$i] != Rute::find($data['rute_id'][$i])->uang_jalan) {
                    return redirect()->back()->with('error', 'Harga titipan tidak sesuai');
                }
           }
        }

        DB::beginTransaction();
        try {
            for ($i=0; $i < count($data['rute_id']); $i++) {
                VendorUangJalan::create([
                    'vendor_id' => $id,
                    'rute_id' => $data['rute_id'][$i],
                    'hk_uang_jalan' => str_replace('.', '', $data['uang_jalan'][$i]),
                    'user_id' => Auth::user()->id,
                ]);
                DB::commit();
            }
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error', $th->getMessage());
        }



        return redirect()->route('vendor.index')->with('success', 'Vendor berhasil ditambahkan');
    }

    public function uang_jalan_edit(Vendor $vendor)
    {

        // $data = VendorUangJalan::findOrFail($id);
        $vendor = $vendor->load('vendor_uang_jalan');
        $rutes = Rute::all();

        return view('database.vendor.edit-uangjalan', [
            'data' => $vendor,
            'rutes' => $rutes,
        ]);
    }

    public function uang_jalan_update(Request $request, string $id)
    {
        $data = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'rute_id' => 'required',
            'rute_id.*' => 'required|exists:rutes,id',
            'uang_jalan' => 'required',
            'uang_jalan.*' => 'required',
        ]);

        $id = $data['vendor_id'];
        $checkRole = Auth::user()->role;

        // dd($checkRole);
        $role = ['admin', 'su'];

        if (!in_array($checkRole, $role)) {
           for ($i=0; $i < count($data['hk_opname']); $i++) {
                if ($data['hk_opname'][$i] != Rute::find($data['rute_id'][$i])->uang_jalan) {
                    return redirect()->back()->with('error', 'Harga opname tidak sesuai');
                }
                if ($data['hk_titipan'][$i] != Rute::find($data['rute_id'][$i])->uang_jalan) {
                    return redirect()->back()->with('error', 'Harga titipan tidak sesuai');
                }
           }
        }
        // dd($data);
        DB::beginTransaction();
        try {

            VendorUangJalan::where('vendor_id', $id)->delete();

            for ($i=0; $i < count($data['rute_id']); $i++) {
                VendorUangJalan::create([
                    'vendor_id' => $id,
                    'rute_id' => $data['rute_id'][$i],
                    'hk_uang_jalan' => str_replace('.', '', $data['uang_jalan'][$i]),
                    'user_id' => Auth::user()->id,
                ]);
            }

            DB::commit();

        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error', $th->getMessage());
        }
            // delete vendor uang jalan

        return redirect()->route('vendor.index')->with('success', 'Vendor berhasil diupdate');
    }



    public function biodata_vendor(string $id)
    {
        $data = Vendor::find($id);
        $customer = Customer::all();
        // $array_vb = $data->vendor_bayar->pluck('customer_id')->toArray();
        // make $data->tanggal into local value id with Carbon
        $data->tanggal = Carbon::parse($data->created_at)->locale('id')->isoFormat('LL');

        $pdf = Pdf::loadview('database.vendor.biodata-vendor', [
            'data' => $data,
            'customer' => $customer,
        ]);
        // put file temporary in public folder
        // $pdf->save(public_path('files/template/sph_template1.pdf'));

        // $pdfmerge = PDFMerger::init();
        // $pdfmerge->addPDF(public_path('files/template/sph_template1.pdf'), 'all');
        // $pdfmerge->addPDF(public_path('files/template/sph_template.pdf'), 'all');
        // $pdfmerge->merge();

        return $pdf->stream();
    }

    public function preview_vendor()
    {
        $data = Vendor::all();

        $pdf = PDF::loadview('database.vendor.prieview-vendor', [
            'data' => $data,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Daftar Vendor.pdf');
    }
}
