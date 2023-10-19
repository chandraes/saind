<?php

namespace App\Http\Controllers;

use App\Models\BbmStoring;
use Illuminate\Http\Request;

class BbmStoringController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = BbmStoring::all();
        return view('database.bbm-storing.index', [
            'data' => $data,
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
        $data = $request->validate([
            'km' => 'required',
            'biaya_vendor' => 'required',
            'biaya_mekanik' => 'required',
        ]);

        $data['biaya_vendor'] = str_replace('.', '', $data['biaya_vendor']);
        $data['biaya_mekanik'] = str_replace('.', '', $data['biaya_mekanik']);

        BbmStoring::create($data);

        return redirect()->route('bbm-storing.index')->with('success', 'Data berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(BbmStoring $bbmStoring)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BbmStoring $bbmStoring)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BbmStoring $bbmStoring)
    {
        $data = $request->validate([
            'km' => 'required',
            'biaya_vendor' => 'required',
            'biaya_mekanik' => 'required',
        ]);

        $data['biaya_vendor'] = str_replace('.', '', $data['biaya_vendor']);
        $data['biaya_mekanik'] = str_replace('.', '', $data['biaya_mekanik']);

        $bbmStoring->update($data);

        return redirect()->route('bbm-storing.index')->with('success', 'Data berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BbmStoring $bbmStoring)
    {
        $bbmStoring->delete();

        return redirect()->route('bbm-storing.index')->with('success', 'Data berhasil dihapus');
    }
}
