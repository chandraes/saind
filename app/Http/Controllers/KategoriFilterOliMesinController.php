<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKategoriFilterOliMesinRequest;
use App\Http\Requests\UpdateKategoriFilterOliMesinRequest;
use App\Models\KategoriFilterOliMesin;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KategoriFilterOliMesinController extends Controller
{
    public function index(): View
    {
        return view('database.kategori-filter-oli-mesin.index', [
            'kategori' => KategoriFilterOliMesin::orderBy('id')->get(),
        ]);
    }

    public function store(StoreKategoriFilterOliMesinRequest $request): RedirectResponse
    {
        KategoriFilterOliMesin::create($request->validated());

        return to_route('database.kategori-filter-oli-mesin.index')
            ->with('success', 'Kategori filter & oli mesin berhasil ditambahkan.');
    }

    public function edit(KategoriFilterOliMesin $kategori): View
    {
        abort_unless(in_array(auth()->user()->role, ['su', 'admin'], true), 403);

        return view('database.kategori-filter-oli-mesin.edit', compact('kategori'));
    }

    public function update(UpdateKategoriFilterOliMesinRequest $request, KategoriFilterOliMesin $kategori): RedirectResponse
    {
        $kategori->update($request->validated());

        return to_route('database.kategori-filter-oli-mesin.index')
            ->with('success', 'Kategori filter & oli mesin berhasil diubah.');
    }
}
