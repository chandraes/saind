<?php

namespace App\Http\Controllers;

use App\Models\PersentaseAwal;
use Illuminate\Http\Request;

class PersentaseAwalController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
            'persentase' => 'required|integer|max:100',
        ]);

        $saham = PersentaseAwal::sum('persentase');

        if ($saham + $data['persentase'] > 100) {
            return redirect()->back()->with('error', 'Persentase awal tidak boleh lebih dari 100%');
        }

        PersentaseAwal::create($data);

        return redirect()->route('pemegang-saham.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function update(PersentaseAwal $awal, Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
            'persentase' => 'required|integer|max:100',
        ]);

        $saham = PersentaseAwal::sum('persentase');

        if ($saham - $awal->persentase + $data['persentase'] > 100) {
            return redirect()->back()->with('error', 'Persentase awal tidak boleh lebih dari 100%');
        }

        $awal->update($data);

        return redirect()->route('pemegang-saham.index')->with('success', 'Data berhasil diubah');
    }

    public function destroy(PersentaseAwal $awal)
    {
        $awal->delete();

        return redirect()->route('pemegang-saham.index')->with('success', 'Data berhasil dihapus');
    }
}
