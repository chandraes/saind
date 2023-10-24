<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'kategori_barang_id' => 'required',
            'nama' => 'required',
        ]);

        Barang::create($data);

        return redirect()->route('kategori-barang.index')->with('success', 'Berhasil menambahkan barang');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Barang $barang)
    {
        $data = $request->validate([
            'nama' => 'required',
            'harga_jual' => 'required',
        ]);

        $data['harga_jual'] = str_replace('.', '', $data['harga_jual']);

        $barang->update($data);

        return redirect()->route('kategori-barang.index')->with('success', 'Berhasil mengubah barang');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Barang $barang)
    {
        
        $barang->delete();

        return redirect()->route('kategori-barang.index')->with('success', 'Berhasil menghapus barang');
    }
}
