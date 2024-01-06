<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\KasVendor;
use App\Models\Direksi;
use App\Models\KasDireksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ByPassVendorController extends Controller
{
    public function index()
    {
        return view('admin.index');
    }

    public function kas_vendor()
    {
        $vendors = Vendor::all();
        return view('admin.bypass-vendor', ['vendors' => $vendors]);
    }

    public function kas_vendor_store(Request $request)
    {
        $data = $request->validate([
            'tipe' => 'required',
            'vendor_id' => 'required|exists:vendors,id',
            'uraian' => 'required',
            'nominal' => 'required',
        ]);

        $db = new KasVendor;

        $data['tanggal'] = date('Y-m-d');
        $data['nominal'] = str_replace('.', '', $data['nominal']);

        if($data['tipe'] == 0){
            $data['pinjaman'] = $data['nominal'];
            $data['sisa'] = $db->sisa_terakhir($data['vendor_id']) + $data['pinjaman'];
        } elseif($data['tipe'] == 1){
            $data['bayar'] = $data['nominal'];
            $data['sisa'] = $db->sisa_terakhir($data['vendor_id']) - $data['bayar'];
        }

        unset($data['nominal'], $data['tipe']);

        DB::beginTransaction();

        try {
            $db->create($data);
            DB::commit();
            return redirect()->back()->with('success', 'Berhasil menambahkan data');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menambahkan data');
        }

    }

    public function kas_direksi()
    {
        $direksis = Direksi::all();
        return view('admin.bypass-direksi', ['direksis' => $direksis]);
    }

    public function kas_direksi_store(Request $request)
    {
        $data = $request->validate([
            'tipe' => 'required',
            'direksi_id' => 'required|exists:direksis,id',
            'uraian' => 'required',
            'nominal' => 'required',
        ]);

        $db = new KasDireksi;

        $data['tanggal'] = date('Y-m-d');
        $data['nominal'] = str_replace('.', '', $data['nominal']);

        if ($data['tipe'] == 0) {
            $data['total_kas'] = $data['nominal'];
            $data['sisa_kas'] = $db->sisa_kas_terakhir($data['direksi_id']) + $data['total_kas'];
        } elseif ($data['tipe'] == 1) {
            $data['total_bayar'] = $data['nominal'];
            $data['sisa_kas'] = $db->sisa_kas_terakhir($data['direksi_id']) - $data['total_bayar'];
        }

        unset($data['nominal'], $data['tipe']);

        DB::beginTransaction();

        try {
            $db->create($data);
            DB::commit();
            return redirect()->back()->with('success', 'Berhasil menambahkan data');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menambahkan data');
        }
    }

}
