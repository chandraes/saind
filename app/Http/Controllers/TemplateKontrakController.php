<?php

namespace App\Http\Controllers;

use App\Models\TemplateKontrak;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class TemplateKontrakController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = TemplateKontrak::orderBy('urutan', 'asc')->get();
        return view('dokumen.template.template-kontrak.index', [
            'data' => $data
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dokumen.template.template-kontrak.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
            'urutan' => 'required|numeric',
            'content' => 'required',
        ]);

        TemplateKontrak::create($data);

        return redirect()->route('template-kontrak.index')->with('success', 'Template Kontrak berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(TemplateKontrak $templateKontrak)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TemplateKontrak $templateKontrak)
    {
        return view('dokumen.template.template-kontrak.edit', [
            'data' => $templateKontrak
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TemplateKontrak $templateKontrak)
    {
        $data = $request->validate([
            'nama' => 'required',
            'urutan' => 'required|numeric',
            'content' => 'required',
        ]);

        $templateKontrak->update($data);

        return redirect()->route('template-kontrak.index')->with('success', 'Template Kontrak berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TemplateKontrak $templateKontrak)
    {
        //
    }

    public function preview()
    {
        $template = TemplateKontrak::orderBy('urutan', 'asc')->get();
        // // make $data->tanggal_indo from $data->tanggal
        // $data->tanggal_indo = Carbon::parse($data->tanggal)->isoFormat('D MMMM Y');
        // // make $data->tahun from $data->tanggal
        // $data->tahun = Carbon::parse($data->tanggal)->format('Y');

        // foreach ($template as $t) {
        //     $t->content = str_replace('{{$tahun}}', $data->tahun, $t->content);
        //     $t->content = str_replace('{{$tanggal}}', $data->tanggal_indo, $t->content);
        //     $t->content = str_replace('{{$nomor}}', sprintf("%03d", $data->nomor), $t->content);
        //     $t->content = str_replace('{{$singkatan}}', $data->nama_singkatan, $t->content);
        //     $t->content = str_replace('{{$nama_vendor}}', $data->vendor->nama, $t->content);
        //     $t->content = str_replace('{{$jabatan_vendor}}', $data->vendor->jabatan, $t->content);
        //     $t->content = str_replace('{{$perusahaan_vendor}}', $data->vendor->perusahaan ? $data->vendor->perusahaan : '-', $t->content);
        //     $t->content = str_replace('{{$perusahaan}}', $data->vendor->perusahaan ? $data->vendor->perusahaan : '', $t->content);
        //     $t->content = str_replace('{{$alamat_vendor}}', $data->vendor->alamat, $t->content);
        //     $t->content = str_replace('{{$no_hp_vendor}}', $data->vendor->no_hp, $t->content);
        //     $t->content = str_replace('{{$email_vendor}}', $data->vendor->email, $t->content);
        //     $t->content = str_replace('{{$pembayaran}}', strtoupper($data->pembayaran), $t->content);
        //     $t->content = str_replace('{{$harga_kesepakatan_mip}}', number_format($hk_mip, 0, ',', '.'), $t->content);
        //     $t->content = str_replace('{{$harga_kesepakatan_bp}}', number_format($hk_bp, 0, ',', '.'), $t->content);
        //     $t->content = str_replace('{{$bank}}', $data->vendor->bank, $t->content);
        //     $t->content = str_replace('{{$no_rekening}}', $data->vendor->no_rekening, $t->content);
        //     $t->content = str_replace('{{$nama_rekening}}', $data->vendor->nama_rekening, $t->content);
        // }

        $pdf = Pdf::loadView('dokumen.template.template-kontrak.preview', [
            // 'data' => $data,
            'template' => $template,
        ]);

        return $pdf->stream('Template Kontrak.pdf');
    }
}
