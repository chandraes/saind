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
        return view('pemegang-saham.index', compact('data'));
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PemegangSaham $pemegangSaham)
    {
        //
    }
}
