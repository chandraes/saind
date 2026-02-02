<?php

namespace App\Http\Controllers;

use App\Models\Direksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class DireksiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $status = $request->filled('status') ? $request->status : 'aktif';
        $data = Direksi::where('status', $status)->get();
        return view('database.direksi.index', compact('data', 'status'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('database.direksi.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
            'gaji_pokok' => 'required',
            'tunjangan_jabatan' => 'nullable',
            'tunjangan_keluarga' => 'nullable',
            'nickname' => 'required',
            'jabatan' => 'required',
            'nik' => 'required',
            'npwp' => 'required',
            'bpjs_tk' => 'required',
            'bpjs_kesehatan' => 'required',
            'tempat_lahir' => 'required',
            'tanggal_lahir' => 'required',
            'alamat' => 'required',
            'no_hp' => 'required',
            'no_wa' => 'required',
            'bank' => 'required',
            'no_rekening' => 'required',
            'nama_rekening' => 'required',
            'mulai_bekerja' => 'required',
            'status' => 'required',
            'apa_bpjs_tk' => 'nullable',
            'apa_bpjs_kesehatan' => 'nullable',
            'status_menikah' => 'required|boolean', // Pastikan menerima 0 atau 1
            'jumlah_anak' => 'nullable|integer',
            'foto_ktp' => 'required|mimes:jpg,jpeg,png|max:10000',
            'foto_diri' => 'required|mimes:jpg,jpeg,png|max:10000',
        ]);

        $data['bank'] = "BCA";
        $data['apa_bpjs_tk'] = $request->filled('apa_bpjs_tk') ? 1 : 0;
        $data['apa_bpjs_kesehatan'] = $request->filled('apa_bpjs_kesehatan') ? 1 : 0;
        $data['status_menikah'] = (int) $request->status_menikah;
        
        $data['gaji_pokok'] = str_replace('.', '', $data['gaji_pokok']);
        $data['tunjangan_jabatan'] = str_replace('.', '', $data['tunjangan_jabatan']);
        $data['tunjangan_keluarga'] = str_replace('.', '', $data['tunjangan_keluarga']);

        $data['created_by'] = auth()->user()->id;

        DB::transaction(function () use ($data, $request) {


            $file_name_ktp = Uuid::uuid4().'- KTP - '. $data['nama']. '.' . $request->foto_ktp->extension();
            $file_name_diri = Uuid::uuid4(). ' - Foto Diri '. $data['nama']. '.' . $request->foto_diri->extension();

            $data['foto_ktp'] = $request->file('foto_ktp')->storeAs('public/direksi', $file_name_ktp);
            $data['foto_diri'] = $request->file('foto_diri')->storeAs('public/direksi', $file_name_diri);

            Direksi::create($data);

        });

        return redirect()->route('direksi.index')->with('success', 'Direksi berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Direksi $direksi)
    {
        // make $direksi->mulai_bekerja to Carbon ID
        $direksi->mulai_bekerja = Carbon::parse($direksi->mulai_bekerja)->locale('id')->isoFormat('D MMMM YYYY');
        $direksi->tanggal_lahir = Carbon::parse($direksi->tanggal_lahir)->locale('id')->isoFormat('D MMMM YYYY');



        $pdf = PDF::loadview('database.direksi.show', [
            'data' => $direksi,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Direksi $direksi)
    {
        return view('database.direksi.edit', [
            'data' => $direksi
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Direksi $direksi)
    {
        $data = $request->validate([
            'nama' => 'required',
            'nickname' => 'required',
            'jabatan' => 'required',
            'gaji_pokok' => 'required',
            'tunjangan_jabatan' => 'nullable',
            'tunjangan_keluarga' => 'nullable',
            'nik' => 'required',
            'npwp' => 'required',
            'bpjs_tk' => 'required',
            'bpjs_kesehatan' => 'required',
            'tempat_lahir' => 'required',
            'tanggal_lahir' => 'required',
            'alamat' => 'required',
            'no_hp' => 'required',
            'no_wa' => 'required',
            'bank' => 'required',
            'no_rekening' => 'required',
            'nama_rekening' => 'required',
            'mulai_bekerja' => 'required',
            'status' => 'required',
            'apa_bpjs_tk' => 'nullable',
            'apa_bpjs_kesehatan' => 'nullable',
            'status_menikah' => 'required|boolean', // Pastikan menerima 0 atau 1
            'jumlah_anak' => 'nullable|integer',
            'foto_ktp' => 'nullable|mimes:jpg,jpeg,png|max:10000',
            'foto_diri' => 'nullable|mimes:jpg,jpeg,png|max:10000',
        ]);

        $data['bank'] = "BCA";
         $data['status_menikah'] = (int) $request->status_menikah;
        $data['apa_bpjs_tk'] = $request->filled('apa_bpjs_tk') ? 1 : 0;
        $data['apa_bpjs_kesehatan'] = $request->filled('apa_bpjs_kesehatan') ? 1 : 0;

        $data['updated_by'] = auth()->user()->id;

        $data['gaji_pokok'] = str_replace('.', '', $data['gaji_pokok']);
        $data['tunjangan_jabatan'] = str_replace('.', '', $data['tunjangan_jabatan']);
        $data['tunjangan_keluarga'] = str_replace('.', '', $data['tunjangan_keluarga']);

        DB::transaction(function () use ($data, $request, $direksi) {

            if ($request->hasFile('foto_ktp')) {
                $file_name_ktp = Uuid::uuid4().'- KTP - '. $data['nama']. '.' . $request->foto_ktp->extension();
                $data['foto_ktp'] = $request->file('foto_ktp')->storeAs('public/karyawan', $file_name_ktp);
                $ktp_path = storage_path('app/'.$direksi->foto_ktp);
                unlink($ktp_path);
            }

            if ($request->hasFile('foto_diri')) {
                $file_name_diri = Uuid::uuid4(). ' - Foto Diri '. $data['nama']. '.' . $request->foto_diri->extension();
                $data['foto_diri'] = $request->file('foto_diri')->storeAs('public/karyawan', $file_name_diri);
                $diri_path = storage_path('app/'.$direksi->foto_diri);
                unlink($diri_path);
            }

            $direksi->update($data);

        });

        return redirect()->route('direksi.index')->with('success', 'Direksi berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Direksi $direksi)
    {
        $ktp_path = storage_path('app/'.$direksi->foto_ktp);
        $diri_path = storage_path('app/'.$direksi->foto_diri);
        unlink($ktp_path);
        unlink($diri_path);
        $direksi->delete();

        return redirect()->route('direksi.index')->with('success', 'Direksi berhasil dihapus');
    }
}
