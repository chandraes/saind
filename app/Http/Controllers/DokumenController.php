<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Webklex\PDFMerger\Facades\PDFMergerFacade as PDFMerger;
use Carbon\Carbon;

class DokumenController extends Controller
{
    public function index()
    {
        return view('dokumen.index');
    }

    public function sph()
    {
        $data = \App\Models\Sph::all();
        return view('dokumen.sph.index', compact('data'));
    }

    public function store_sph(Request $request)
    {
        $request->validate([
            'nama' => 'required',
        ]);

        $tahun = date('Y');
        $tanggal = date('Y-m-d');

        $nomor = \App\Models\Sph::where('tahun', $tahun)->count();
        $nomor = $nomor + 1;
        $nomor = sprintf("%03d", $nomor);

        $sph = \App\Models\Sph::create([
            'nama' => $request->nama,
            'nomor' => $nomor,
            'tanggal' => $tanggal,
            'tahun' => $tahun,
            'user_id' => auth()->user()->id,
        ]);
        // return to route dokumen.sph with success message
        return redirect()->route('dokumen.sph')->with('success', 'Data berhasil disimpan');
    }

    public function sph_doc($id)
    {
        $data = \App\Models\Sph::find($id);

        // make $data->tanggal into local value id with Carbon
        $data->tanggal = Carbon::parse($data->tanggal)->locale('id')->isoFormat('LL');

        $pdf = Pdf::loadview('dokumen.sph.doc', compact('data'));
        // put file temporary in public folder
        // $pdf->save(public_path('files/template/sph_template1.pdf'));

        // $pdfmerge = PDFMerger::init();
        // $pdfmerge->addPDF(public_path('files/template/sph_template1.pdf'), 'all');
        // $pdfmerge->addPDF(public_path('files/template/sph_template.pdf'), 'all');
        // $pdfmerge->merge();

        return $pdf->stream();
        // return $pdf->download('sph.pdf');
    }

    public function kontrak()
    {
        return view('dokumen.kontrak.index');
    }

    public function spk()
    {
        return view('dokumen.spk.index');
    }
}
