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

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (auth()->user()->role == 'admin' || auth()->user()->role == 'su') {
            $vendors = Vendor::with(['sponsor', 'vendor_uang_jalan', 'vendor_uang_jalan.rute'])->get();
        }
        if(auth()->user()->role == 'vendor') {
            $vendors = Vendor::where('vendor_id', auth()->user()->vendor_id)->get();
        }

        $customers = Customer::all();

        return view('database.vendor.index', [
            'vendors' => $vendors,
            'customers' => $customers,
        ]);
    }

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
        ]);

        if (array_key_exists('ppn', $data)) {
            $data['ppn'] = 1;
        } else {
            $data['ppn'] = 0;
        }

        if (array_key_exists('pph', $data)) {
            $data['pph'] = 1;
        } else {
            $data['pph'] = 0;
        }

        if(array_key_exists('support_operational', $data)){
            $data['support_operational'] = 1;
        } else {
            $data['support_operational'] = 0;
        }

        $data['plafon_titipan'] = str_replace('.', '', $data['plafon_titipan']);
        $data['plafon_lain'] = str_replace('.', '', $data['plafon_lain']);
        // dd($data);

        $data['user_id'] = auth()->user()->id;

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
        $checkRole = auth()->user()->role;

        if ($checkRole !== 'admin' || $checkRole !== 'su') {
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
                    'user_id' => auth()->user()->id,
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
        $checkRole = auth()->user()->role;

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
                    'user_id' => auth()->user()->id,
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
