<?php

namespace App\Http\Controllers;

use App\Models\Rute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RuteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Rute::with(['user'])->get();
        return view('database.rute.index', [
            'data' => $data,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
            'jarak' => 'required|numeric',
            'uang_jalan' => 'required',
        ]);

        $data['uang_jalan'] = str_replace('.', '', $data['uang_jalan']);

        $data['user_id'] = Auth::id();

        Rute::create($data);

        return redirect()->route('rute.index')->with('success', 'Rute berhasil ditambahkan');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Rute $rute)
    {

        $data = $request->validate([
            'nama' => 'required',
            'jarak' => 'required|numeric',
            'uang_jalan' => 'required',
        ]);

        $data['uang_jalan'] = str_replace('.', '', $data['uang_jalan']);

        $data['edited_by'] = Auth::id();

        $rute->update($data);

        return redirect()->route('rute.index')->with('success', 'Rute berhasil diubah');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rute $rute)
    {
        $rute->delete();

        return redirect()->route('rute.index')->with('success', 'Rute berhasil dihapus');
    }
}
