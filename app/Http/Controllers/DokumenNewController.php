<?php

namespace App\Http\Controllers;

use App\Models\TemplateKontrakNew;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class DokumenNewController extends Controller
{
    public function index()
    {
        return view('dokumen.template-new.index');
    }

    public function kontrak_new()
    {
        return view('dokumen.template-new.kontrak-new');
    }

    public function create_template_kontrak(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
            'file' => 'required|mimes:rtf',
        ]);

        $filename = Uuid::uuid4().'.'.$request->file('file')->extension();

        $data['file'] = $request->file('file')->storeAs('public/template-kontrak-new', $filename);

        TemplateKontrakNew::create($data);

        return redirect()->route('template-kontrak-new.index')->with('success', 'Template Kontrak berhasil ditambahkan');

    }


}
