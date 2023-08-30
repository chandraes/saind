<?php

namespace App\Http\Controllers;

use App\Models\Rekening;
use Illuminate\Http\Request;

class RekeningController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Rekening::all();

        return view('database.rekening.index', [
            'data' => $data,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rekening $rekening)
    {
        return view('database.rekening.edit', [
            'data' => $rekening,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Rekening $rekening)
    {
        $data = $request->validate([
            'nama_bank' => 'required',
            'nomor_rekening' => 'required',
            'nama_rekening' => 'required',
        ]);

        $data['untuk'] = $rekening->untuk;

        $rekening->update($data);

        return redirect()->route('rekening.index')->with('success', 'Data berhasil diubah');
    }

}
