<?php

namespace App\Http\Controllers;

use App\Models\Sponsor;
use Illuminate\Http\Request;

class SponsorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Sponsor::all();
        return view('database.sponsor.index', [
            'data' => $data
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
            'nomor_wa' => 'required',
            'nama_bank' => 'required',
            'nomor_rekening' => 'required',
            'transfer_ke' => 'required',
        ]);

        $nomor = Sponsor::latest()->first();

        if ($nomor) {
            $data['nomor_kode_sponsor'] = $nomor->nomor_kode_sponsor + 1;
        } else {
            $data['nomor_kode_sponsor'] = 1;
        }

        Sponsor::create($data);

        return redirect()->back()->with('success', 'Sponsor berhasil ditambahkan');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sponsor $sponsor)
    {
        $data = $request->validate([
            'nama' => 'required',
            'nomor_wa' => 'required',
            'nama_bank' => 'required',
            'nomor_rekening' => 'required',
            'transfer_ke' => 'required',
        ]);

        $sponsor->update($data);

        return redirect()->back()->with('success', 'Sponsor berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sponsor $sponsor)
    {
        $sponsor->delete();

        return redirect()->back()->with('success', 'Sponsor berhasil dihapus');
    }
}
