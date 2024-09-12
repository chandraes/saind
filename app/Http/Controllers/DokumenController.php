<?php

namespace App\Http\Controllers;

use App\Models\Dokumen\MutasiRekening;
use App\Services\StarSender;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class DokumenController extends Controller
{
    public function index()
    {
        return view('dokumen.index');
    }

    public function mutasi_rekening(Request $request)
    {
        $tahun = $request->tahun ?? Carbon::now()->year;

        $dataTahun = MutasiRekening::select('tahun')->distinct()->get();

        $bulan = [
            '1' => 'Januari',
            '2' => 'Februari',
            '3' => 'Maret',
            '4' => 'April',
            '5' => 'Mei',
            '6' => 'Juni',
            '7' => 'Juli',
            '8' => 'Agustus',
            '9' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        ];

        // Fetch all records for the specified year in a single query
        $mutasiRekenings = MutasiRekening::where('tahun', $tahun)->get()->keyBy('bulan');

        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            $mutasiRekening = $mutasiRekenings->get($i);
            $data[$i] = [
                'id' => $mutasiRekening ? $mutasiRekening->id : null,
                'tahun' => $tahun,
                'bulan' => $bulan[$i],
                'file' => $mutasiRekening ? $mutasiRekening->file : null
            ];
        }

        return view('dokumen.mutasi-rekening.index', [
            'tahun' => $tahun,
            'dataTahun' => $dataTahun,
            'data' => $data,
            'bulan' => $bulan
        ]);
    }

    public function mutasi_rekening_store(Request $request)
    {
        $request->validate([
            'tahun' => 'required|numeric',
            'bulan' => 'required|numeric',
            'file' => 'required|file|mimes:pdf|max:5120'
        ]);

        $path = public_path('files/dokumen/mutasi-rekening');

        // Check if directory exists, if not create it
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        // Store the file
        $file = $request->file('file');
        $filename = time() . '.' . $file->getClientOriginalExtension();
        $file->move($path, $filename);

        // Save the data
        $data['file'] = 'files/dokumen/mutasi-rekening/' . $filename;

        MutasiRekening::create([
            'tahun' => $request->tahun,
            'bulan' => $request->bulan,
            'file' => $data['file']
        ]);

        return redirect()->route('dokumen.mutasi-rekening')->with('success', 'Data berhasil disimpan');
    }

    public function mutasi_rekening_destroy(MutasiRekening $mutasi)
    {
        $path = public_path($mutasi->file);

        if(File::exists($path)) {
            File::delete($path);
        }

        $mutasi->delete();

        return redirect()->route('dokumen.mutasi-rekening')->with('success', 'Data berhasil dihapus');
    }

    public function kirim_wa(MutasiRekening $mutasi, Request $request)
    {
        $data = $request->validate([
            'tujuan' => 'required',
        ]);

        $data['tujuan'] = str_replace('-', '', $data['tujuan']);
        Carbon::setLocale('id');
        $data['pesan'] = Carbon::createFromDate($mutasi->tahun, $mutasi->bulan, 1)->translatedFormat('F Y');
        // dd($data);
        // baseurl + file
        $file = url($mutasi->file);

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

}
