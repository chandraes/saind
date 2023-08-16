<?php

namespace App\Http\Controllers;

use App\Models\Spk;
use App\Models\Vendor;
use App\Models\Customer;
use App\Models\TemplateSpk;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;

class SpkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Spk::all();
        $vendors = Vendor::where('status', 'aktif')->get();
        return view('dokumen.spk.index', [
            'data' => $data,
            'vendors' => $vendors,
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
            'pembayaran' => 'required',
            'vendor_id' => 'required|exists:vendors,id',
        ]);

        $data['created_by'] = auth()->user()->id;
        $data['nomor'] = Spk::count() + 1;
        $data['tanggal'] = now();
        $data['tanggal_expired'] = now()->addYear();

        Spk::create($data);

        return redirect()->route('spk.index')->with('success', 'SPK berhasil ditambahkan');

    }

    /**
     * Display the specified resource.
     */
    public function show(Spk $spk)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Spk $spk)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Spk $spk)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Spk $spk)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('spk.index')->with('error', 'Anda tidak memiliki akses untuk menghapus SPK');
        }

        // delete file
        if ($spk->dokumen_asli) {
            $path = storage_path('app/'.$spk->dokumen_asli);
            unlink($path);
        }

        $spk->delete();
        return redirect()->route('spk.index')->with('success', 'SPK berhasil dihapus');
    }

    /**
     * Generate PDF.
     */
    public function spk_doc(Spk $spk)
    {
        $data = $spk;
        $template = TemplateSpk::orderBy('halaman', 'asc')->get();

        $hk_mip = $data->vendor->vendor_bayar->where('pembayaran', $data->pembayaran)->where('customer_id', 1)->first()->harga_kesepakatan;
        $hk_bp = $data->vendor->vendor_bayar->where('pembayaran', $data->pembayaran)->where('customer_id', 2)->first()->harga_kesepakatan;

        // $customer = Customer::all();
        // make $data->tanggal_indo from $data->tanggal
        $data->tanggal_indo = Carbon::parse($data->tanggal)->isoFormat('D MMMM Y');
        // make $data->tahun from $data->tanggal
        $data->tahun = Carbon::parse($data->tanggal)->format('Y');

        foreach ($template as $t) {
            $t->content = str_replace('{{$tahun}}', $data->tahun, $t->content);
            $t->content = str_replace('{{$tanggal}}', $data->tanggal_indo, $t->content);
            $t->content = str_replace('{{$nomor}}', sprintf("%03d", $data->nomor), $t->content);
            $t->content = str_replace('{{$singkatan}}', $data->nama_singkatan, $t->content);
            $t->content = str_replace('{{$nama_vendor}}', $data->vendor->nama, $t->content);
            $t->content = str_replace('{{$jabatan_vendor}}', $data->vendor->jabatan, $t->content);
            $t->content = str_replace('{{$perusahaan_vendor}}', $data->vendor->perusahaan ? $data->vendor->perusahaan : '-', $t->content);
            $t->content = str_replace('{{$perusahaan}}', $data->vendor->perusahaan ? $data->vendor->perusahaan : '', $t->content);
            $t->content = str_replace('{{$alamat_vendor}}', $data->vendor->alamat, $t->content);
            $t->content = str_replace('{{$no_hp_vendor}}', $data->vendor->no_hp, $t->content);
            $t->content = str_replace('{{$email_vendor}}', $data->vendor->email, $t->content);
            $t->content = str_replace('{{$pembayaran}}', strtoupper($data->pembayaran), $t->content);
            $t->content = str_replace('{{$harga_kesepakatan_mip}}', number_format($hk_mip, 0, ',', '.'), $t->content);
            $t->content = str_replace('{{$harga_kesepakatan_bp}}', number_format($hk_bp, 0, ',', '.'), $t->content);
            $t->content = str_replace('{{$bank}}', $data->vendor->bank, $t->content);
            $t->content = str_replace('{{$no_rekening}}', $data->vendor->no_rekening, $t->content);
            $t->content = str_replace('{{$nama_rekening}}', $data->vendor->nama_rekening, $t->content);
        }

        $pdf = Pdf::loadView('dokumen.template.template-spk.doc', [
            // 'data' => $data,
            'template' => $template,
        ]);
        return $pdf->stream($data->nomor.' - SPK '.$data->vendor->perusahaan.' - '.$data->vendor->nama.'.pdf');
    }

    // upload SPK
    public function upload(Request $request, Spk $spk)
    {
        $data = $request->validate([
            'dokumen_asli' => 'required|mimes:pdf|max:10000',
        ]);

        $filename = $spk->nomor.' - '.$spk->vendor->nama.' - '.Uuid::uuid4().'.'.$request->file('dokumen_asli')->extension();

        $data['dokumen_asli'] = $request->file('dokumen_asli')->storeAs('public/spk', $filename);

        $spk->update($data);

        return redirect()->route('spk.index')->with('success', 'SPK berhasil diupload');
    }

    public function view_file(Spk $spk)
    {
        $path = storage_path('app/'.$spk->dokumen_asli);
        return response()->file($path);
    }

    public function delete_file(Spk $spk)
    {

        $path = storage_path('app/'.$spk->dokumen_asli);
        unlink($path);

        $spk->dokumen_asli = null;
        $spk->save();

        return redirect()->route('spk.index')->with('success', 'SPK Asli berhasil dihapus');
    }

}
