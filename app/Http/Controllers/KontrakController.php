<?php

namespace App\Http\Controllers;

use App\Models\Kontrak;
use App\Models\Vendor;
use App\Models\Customer;
use App\Models\TemplateKontrak;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;


class KontrakController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kontraks = Kontrak::all();
        $vendors = Vendor::where('status', 'aktif')->get();
        return view('dokumen.kontrak.index', [
            'vendors' => $vendors,
            'kontraks' => $kontraks,
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
            'nama_singkatan' => 'required',
            'vendor_id' => 'required',
        ]);

        $data['created_by'] = auth()->user()->id;
        $data['nomor'] = Kontrak::count() + 1;
        $data['tanggal'] = now();

        Kontrak::create($data);

        return redirect()->route('kontrak.index')->with('success', 'Kontrak berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Kontrak $kontrak)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kontrak $kontrak)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kontrak $kontrak)
    {

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kontrak $kontrak)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('kontrak.index')->with('error', 'Anda tidak memiliki akses untuk menghapus kontrak');
        }

        // delete dokumen kontrak
        if ($kontrak->dokumen_asli) {
            unlink(storage_path('app/' . $kontrak->dokumen_asli));
        }
        // delete kontrak
        $kontrak->delete();

        return redirect()->route('kontrak.index')->with('success', 'Kontrak berhasil dihapus');
    }

    public function kontrak_doc(Kontrak $kontrak)
    {
        $data = $kontrak;
        $template = TemplateKontrak::orderBy('urutan', 'asc')->get();
        $customer = Customer::select('nama', 'singkatan', 'harga_opname', 'harga_titipan')->get();
        // take year from tanggal
        $data->tahun = Carbon::parse($data->tanggal)->locale('id')->isoFormat('YYYY');

        // buat penyebutan angka hari dari tanggal
        $data->hari = Carbon::parse($data->tanggal)->locale('id')->isoFormat('dddd');

        $data->hari_angka = Carbon::parse($data->tanggal)->locale('id')->isoFormat('D');

        // buat penyebutan angka bulan dari tanggal
        $data->bulan = Carbon::parse($data->tanggal)->locale('id')->isoFormat('MMMM');

        $data->tanggal_string = Carbon::parse($data->tanggal)->locale('id')->isoFormat('D MMMM YYYY');

        $kalimat_perusahaan = "<strong>".strtoupper($data->vendor->perusahaan)."</strong> suatu ".ucfirst($data->vendor->tipe)." dengan NPWP ".$data->vendor->npwp
                                ." yang selanjutnya diwakilkan oleh <strong>".$data->vendor->nama." </strong> selaku ".$data->vendor->jabatan." ";

        $kalimat_perseorangan = "<strong>".$data->vendor->nama."</strong> selaku ".ucfirst($data->vendor->jabatan)." dengan NIK ".$data->vendor->npwp." ";
        foreach ($template as $t) {
            $t->content = str_replace('{{$hari}}', $data->hari, $t->content);
            $t->content = str_replace('{{$tanggal}}', $data->tanggal_string, $t->content);
            $t->content = str_replace('{{$nomor}}', sprintf("%03d", $data->nomor), $t->content);
            $t->content = str_replace('{{$tahun}}', $data->tahun, $t->content);
            $t->content = str_replace('{{$nama_singkatan}}', $data->nama_singkatan, $t->content);
            $t->content = str_replace('{{$nama_vendor}}', $data->vendor->tipe == 'perusahaan' ? $data->vendor->perusahaan : $data->vendor->nama, $t->content);
            $t->content = str_replace('{{$tipe_vendor}}', ucfirst($data->vendor->tipe), $t->content);
            $t->content = str_replace('{{$hari_angka}}', $data->hari_angka, $t->content);
            $t->content = str_replace('{{$bulan}}', $data->bulan, $t->content);
            $t->content = str_replace('{{$kalimat_pribadi_perusahaan}}', $data->vendor->tipe == 'perusahaan' ? $kalimat_perusahaan : $kalimat_perseorangan, $t->content);
            $t->content = str_replace('{{$tipe}}', $data->vendor->tipe == 'perusahaan' ? "perusahaan" : "pribadi", $t->content);
            $t->content = str_replace('{{$alamat}}', $data->vendor->alamat, $t->content);
            $t->content = str_replace('{{ $vendor_nama }}', $data->vendor->nama, $t->content);
            $t->content = str_replace('{{ $jabatan_vendor }}', $data->vendor->jabatan, $t->content);
            $t->content = str_replace('{{$vendor_bank}}', $data->vendor->bank, $t->content);
            $t->content = str_replace('{{$vendor_no_rekening}}', $data->vendor->no_rekening, $t->content);
            $t->content = str_replace('{{$vendor_nama_rekening}}', $data->vendor->nama_rekening, $t->content);
        }

        $pdf = Pdf::loadView('dokumen.template.template-kontrak.preview', [
            // 'data' => $data,
            'template' => $template,
        ])->setPaper('a4', 'portrait');

        // if $data->dokumen_kontrak is null, then create new one then save pdf and put into storage and add uuid to suffix name
        // if ($data->dokumen_kontrak == null) {
        //     $data->dokumen_kontrak = $data->tahun.'- Kontrak '.$data->vendor->nama.Uuid::uuid4()->toString() . '.pdf';
        //     $data->save();
        //     $pdf->save(storage_path('app/public/kontrak/' . $data->dokumen_kontrak));
        // }


        return $pdf->stream($data->nomor.' - Kontrak '.$data->vendor->perusahaan.' - '.$data->vendor->nama.'.pdf');
    }

    public function upload(Request $request, Kontrak $kontrak)
    {
        $data = $request->validate([
            'dokumen_asli' => 'required|mimes:pdf|max:10000',
        ]);

        $filename = $kontrak->nomor.' - '.$kontrak->vendor->nama.' - '.Uuid::uuid4().'.'.$request->file('dokumen_asli')->extension();

        $data['dokumen_asli'] = $request->file('dokumen_asli')->storeAs('public/kontrak', $filename);

        $kontrak->update($data);

        return redirect()->route('kontrak.index')->with('success', 'Kontrak berhasil diupload');
    }

    public function view_file(Kontrak $kontrak)
    {
        $path = storage_path('app/'.$kontrak->dokumen_asli);
        return response()->file($path);
    }

    public function delete_file(Kontrak $kontrak)
    {
        $path = storage_path('app/'.$kontrak->dokumen_asli);
        unlink($path);

        $kontrak->dokumen_asli = null;
        $kontrak->save();

        return redirect()->route('kontrak.index')->with('success', 'File kontrak berhasil dihapus');
    }
}
