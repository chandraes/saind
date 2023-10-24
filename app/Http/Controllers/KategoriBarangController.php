<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\KategoriBarang;
use Illuminate\Http\Request;

class KategoriBarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kategori = KategoriBarang::all();

        return view('database.barang.index', [
            'kategori' => $kategori,
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Barang $barang)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Barang $barang)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Barang $barang)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Barang $barang)
    {
        //
    }

    public function kategori_store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
        ]);

        KategoriBarang::create($data);

        return redirect()->back()->with('success', 'Kategori berhasil ditambahkan');
    }

    public function kategori_update(Request $request, KategoriBarang $kategori)
    {
        $data = $request->validate([
            'nama' => 'required',
        ]);

        $kategori->update($data);

        return redirect()->back()->with('success', 'Kategori berhasil diubah');
    }

    public function kategori_destroy(KategoriBarang $kategori)
    {
        $kategori->delete();

        return redirect()->back()->with('success', 'Kategori berhasil dihapus');
    }


}
