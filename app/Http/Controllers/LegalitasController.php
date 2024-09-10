<?php

namespace App\Http\Controllers;

use App\Models\Legalitas\LegalitasDokumen;
use App\Models\Legalitas\LegalitasKategori;
use App\Services\StarSender;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class LegalitasController extends Controller
{
    public function index()
    {
        $kategori = LegalitasKategori::all();
        $dokumen = LegalitasDokumen::orderBy('legalitas_kategori_id')->get()->groupBy('legalitas_kategori_id');

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
            'file' => 'required|file|mimes:pdf|max:10240'
        ]);

        // Define the storage path
        $path = public_path('files/legalitas');

        // Check if directory exists, if not create it
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        // Store the file
        $file = $request->file('file');
        $filename = time() . '.' . $file->getClientOriginalExtension();
        $file->move($path, $filename);

        // Save the data
        $data['file'] = 'files/legalitas/' . $filename;
        LegalitasDokumen::create($data);

        return redirect()->back()->with('success', 'Dokumen berhasil ditambahkan');
    }

    public function update(LegalitasDokumen $legalitas, Request $request)
    {
        $data = $request->validate([
            'legalitas_kategori_id' => 'required',
            'nama' => 'required',
            'file' => 'nullable|file|mimes:pdf|max:10240'
        ]);

        if ($request->hasFile('file')) {
            $path = public_path($legalitas->file);

            if(File::exists($path)) {
                File::delete($path);
            }

            $file = $request->file('file');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('files/legalitas'), $filename);

            $data['file'] = 'files/legalitas/' . $filename;
        }

        $legalitas->update($data);

        return redirect()->back()->with('success', 'Dokumen berhasil diubah');
    }

    public function destroy(LegalitasDokumen $legalitas)
    {
        $path = public_path($legalitas->file);

        if(File::exists($path)) {
            File::delete($path);
        }

        $legalitas->delete();

        return redirect()->back()->with('success', 'Dokumen berhasil dihapus');
    }

    public function kirim_wa(LegalitasDokumen $legalitas, Request $request)
    {
        $data = $request->validate([
            'tujuan' => 'required',
        ]);

        $data['tujuan'] = str_replace('-', '', $data['tujuan']);
        $data['pesan'] = $legalitas->nama;
        // dd($data);
        // baseurl + file
        $file = url($legalitas->file);

        ini_set('post_max_size', '15M');
        ini_set('upload_max_filesize', '15M');
        // dd($file, $data);
        $service = new StarSender($data['tujuan'], $data['pesan'], $file);

        $res = $service->sendWaLama();

        if ($res == 'true') {
            return redirect()->back()->with('success', 'Dokumen berhasil dikirim');
        } else {
            return redirect()->back()->with('error', 'Dokumen gagal dikirim ');
        }

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
