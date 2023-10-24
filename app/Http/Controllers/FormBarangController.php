<?php

namespace App\Http\Controllers;

use App\Models\KategoriBarang;
use App\Models\Barang;
use App\Models\KeranjangBelanja;
use Illuminate\Http\Request;

class FormBarangController extends Controller
{
    public function beli()
    {
        $kategori = KategoriBarang::all();
        $keranjang = KeranjangBelanja::where('user_id', auth()->user()->id)->get();
        return view('billing.barang.beli', [
            'kategori' => $kategori,
            'keranjang' => $keranjang,
        ]);
    }

    public function get_barang(Request $request)
    {
        $barang = Barang::where('kategori_barang_id', $request->kategori_barang_id)->get();

        return response()->json($barang);
    }

    public function keranjang()
    {

    }

    public function keranjang_store(Request $request)
    {
        $data = $request->validate([
            'barang_id' => 'required',
            'jumlah' => 'required',
            'harga_satuan' => 'required',
        ]);

        $data['user_id'] = auth()->user()->id;

        $data['harga_satuan'] = str_replace('.', '', $data['harga_satuan']);

        KeranjangBelanja::create($data);

        return redirect()->route('billing.form-barang.beli')->with('success', 'Berhasil menambahkan barang ke keranjang');
    }

    public function keranjang_destroy(KeranjangBelanja $keranjang)
    {
        $keranjang->delete();

        return redirect()->route('billing.form-barang.beli')->with('success', 'Berhasil menghapus barang dari keranjang');
    }

    public function beli_store(Request $request)
    {

    }
}
