<?php

namespace App\Http\Controllers;

use App\Models\PemegangSaham;
use Illuminate\Http\Request;

class PemegangSahamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = PemegangSaham::all();
        return view('database.saham.index', compact('data'));
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
            'nama' => 'required',
            'persentase' => 'required|integer',
            'nama_rekening' => 'required',
            'nomor_rekening' => 'required',
            'bank' => 'required',
        ]);

        $saham = PemegangSaham::sum('persentase');

        if ($saham + $data['persentase'] > 100) {
            return redirect()->back()->with('error', 'Persentase pemegang saham tidak boleh lebih dari 100%');
        }

        PemegangSaham::create($data);

        return redirect()->route('pemegang-saham.index')->with('success', 'Data berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(PemegangSaham $pemegangSaham)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PemegangSaham $pemegangSaham)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PemegangSaham $pemegangSaham)
    {
        $data = $request->validate([
            'nama' => 'required',
            'persentase' => 'required|integer',
            'nama_rekening' => 'required',
            'nomor_rekening' => 'required',
            'bank' => 'required',
        ]);

        $saham = PemegangSaham::sum('persentase');

        if ($saham - $pemegangSaham->persentase + $data['persentase'] > 100) {
            return redirect()->back()->with('error', 'Persentase pemegang saham tidak boleh lebih dari 100%');
        }

        $pemegangSaham->update($data);

        return redirect()->route('pemegang-saham.index')->with('success', 'Data berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PemegangSaham $pemegangSaham)
    {
        $pemegangSaham->delete();

        return redirect()->route('pemegang-saham.index')->with('success', 'Data berhasil dihapus');
    }
}
