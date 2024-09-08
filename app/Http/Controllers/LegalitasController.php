<?php

namespace App\Http\Controllers;

use App\Models\Legalitas\LegalitasDokumen;
use App\Models\Legalitas\LegalitasKategori;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class LegalitasController extends Controller
{
    public function index()
    {
        $kategori = LegalitasKategori::all();
        $dokumen = LegalitasDokumen::all();

        return view('legalitas.index', [
            'kategori' => $kategori,
            'data' => $dokumen
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'legalitas_kategori_id' => 'required',
            'nama' => 'required',
            'file' => 'required|file|mimes:pdf'
        ]);

        // Define the storage path
        $path = 'legalitas';

        // Store the file
        $file = $request->file('file');
        $filename = time() . '.' . $file->getClientOriginalExtension();
        $file->storeAs($path, $filename, 'public');

        // Save the data
        $data['file'] = $filename;
        LegalitasDokumen::create($data);

        return redirect()->back()->with('success', 'Dokumen berhasil ditambahkan');
    }

    public function destroy(LegalitasDokumen $legalitas)
    {
        $file = $legalitas->file;
        $path = 'public/legalitas/' . $file;

        // Delete the file
        if (Storage::exists($path)) {
            Storage::delete($path);
        }

        $legalitas->delete();

        return redirect()->back()->with('success', 'Dokumen berhasil dihapus'); 
    }

    public function kategori_store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|unique:legalitas_kategoris,nama'
        ]);

        LegalitasKategori::create($data);

        return redirect()->back()->with('success', 'Kategori berhasil ditambahkan');
    }

    public function kategori_update(Request $request, $id)
    {
        $data = $request->validate([
            'nama' => 'required|unique:legalitas_kategoris,nama,' . $id
        ]);

        LegalitasKategori::find($id)->update($data);

        return redirect()->back()->with('success', 'Kategori berhasil diubah');
    }

    public function kategori_destroy($id)
    {
        $check = LegalitasDokumen::where('legalitas_kategori_id', $id)->count();

        if ($check > 0) {
            return redirect()->back()->with('error', 'Kategori tidak bisa dihapus karena masih terdapat dokumen yang terkait');
        }

        LegalitasKategori::find($id)->delete();

        return redirect()->back()->with('success', 'Kategori berhasil dihapus');
    }
}
