<?php

namespace App\Http\Controllers;

use App\Models\AktivasiMaintenance;
use App\Models\BarangMaintenance;
use App\Models\KeranjangMaintenance;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class FormMaintenanceController extends Controller
{
    public function beli()
    {
        $kategori = BarangMaintenance::all();
        $keranjang = KeranjangMaintenance::where('user_id', auth()->user()->id)->get();
        return view('billing.form-maintenance.beli.index', [
            'kategori' => $kategori,
            'keranjang' => $keranjang,
        ]);
    }

    public function keranjang_store(Request $request)
    {
        $data = $request->validate([
            'barang_maintenance_id' => 'required|exists:barang_maintenances,id',
            'jumlah' => 'required',
            'harga_satuan' => 'required',
        ]);

        $data['user_id'] = auth()->user()->id;

        $data['harga_satuan'] = str_replace('.', '', $data['harga_satuan']);

        $data['total'] = $data['jumlah'] * $data['harga_satuan'];

        KeranjangMaintenance::create($data);

        return redirect()->route('billing.form-maintenance.beli')->with('success', 'Berhasil menambahkan barang ke keranjang');
    }


    public function keranjang_empty()
    {
        KeranjangMaintenance::where('user_id', auth()->user()->id)->delete();

        return redirect()->route('billing.form-maintenance.beli')->with('success', 'Berhasil mengosongkan keranjang');
    }

    public function keranjang_destroy(KeranjangMaintenance $keranjang)
    {
        $keranjang->delete();

        return redirect()->route('billing.form-maintenance.beli')->with('success', 'Berhasil menghapus barang dari keranjang');
    }

    public function beli_store(Request $request)
    {
        $data = $request->validate([
            'uraian' => 'required',
            'bank' => 'required',
            'transfer_ke' => 'required',
            'no_rekening' => 'required',
        ]);

        $db = new KeranjangMaintenance();

        $store = $db->beliStore($data);

        return redirect()->route('billing.index')->with($store['status'], $store['message']);

    }

    public function get_harga_jual(Request $request)
    {
        $barang = BarangMaintenance::find($request->barang_maintenance_id);

        return response()->json($barang);
    }

    public function jual_vendor()
    {
        $kategori = BarangMaintenance::where('stok', '>', 0)->get();
        $am = AktivasiMaintenance::pluck('vehicle_id');
        $vehicle = Vehicle::whereIn('id', $am)->get();

        return view('billing.form-maintenance.jual-vendor', [
            'kategori' => $kategori,
            'vehicle' => $vehicle,
        ]);
    }

    public function jual_vendor_store(Request $request)
    {
        $data = $request->validate([
            'barang_maintenance_id' => 'required|exists:barang_maintenances,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'vendor_id' => 'required|exists:vendors,id',
            'jumlah' => 'required',
        ]);

        $db = new BarangMaintenance();

        $store = $db->jualVendorStore($data);

        return redirect()->route('billing.index')->with($store['status'], $store['message']);
    }
}
