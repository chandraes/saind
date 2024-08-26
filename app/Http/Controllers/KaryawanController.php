<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Jabatan;
use App\Models\KasBon;
use App\Models\KasBonCicilan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class KaryawanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $status = $request->filled('status') ? $request->status : 'aktif';
        $karyawans = Karyawan::with(['jabatan'])->where('status', $status)->get();
        $jabatan = Jabatan::select('id', 'nama')->get();

        return view('database.karyawan.index', [
            'karyawans' => $karyawans,
            'jabatan' => $jabatan,
            'status' => $status,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $jabatan = Jabatan::select('id', 'nama')->get();

        return view('database.karyawan.create', [
            'jabatan' => $jabatan,
        ]);
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
            'jabatan_id' => 'required',
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
            'foto_ktp' => 'required|mimes:jpg,jpeg,png|max:10000',
            'foto_diri' => 'required|mimes:jpg,jpeg,png|max:10000',
        ]);

        $last = Karyawan::latest()->orderBy('id', 'desc')->first();

        $data['nomor'] = $last ? $last->nomor + 1 : 1;

        $data['bank'] = "BCA";

        $data['gaji_pokok'] = str_replace('.', '', $data['gaji_pokok']);
        $data['tunjangan_jabatan'] = str_replace('.', '', $data['tunjangan_jabatan']);
        $data['tunjangan_keluarga'] = str_replace('.', '', $data['tunjangan_keluarga']);

        $data['created_by'] = auth()->user()->id;

        DB::transaction(function () use ($data, $request) {


            $file_name_ktp = Uuid::uuid4().'- KTP - '. $data['nama']. '.' . $request->foto_ktp->extension();
            $file_name_diri = Uuid::uuid4(). ' - Foto Diri '. $data['nama']. '.' . $request->foto_diri->extension();

            $data['foto_ktp'] = $request->file('foto_ktp')->storeAs('public/karyawan', $file_name_ktp);
            $data['foto_diri'] = $request->file('foto_diri')->storeAs('public/karyawan', $file_name_diri);

            Karyawan::create($data);

        });

        return redirect()->route('karyawan.index')->with('success', 'Karyawan berhasil ditambahkan');

    }

    /**
     * Display the specified resource.
     */
    public function show(Karyawan $karyawan)
    {
        // make $karyawan->mulai_bekerja to Carbon ID
        $karyawan->mulai_bekerja = Carbon::parse($karyawan->mulai_bekerja)->locale('id')->isoFormat('D MMMM YYYY');
        $karyawan->tanggal_lahir = Carbon::parse($karyawan->tanggal_lahir)->locale('id')->isoFormat('D MMMM YYYY');



        $pdf = PDF::loadview('database.karyawan.show', [
            'karyawan' => $karyawan,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Karyawan $karyawan)
    {
        $jabatan = Jabatan::select('id', 'nama')->get();

        return view('database.karyawan.edit', [
            'karyawan' => $karyawan,
            'jabatan' => $jabatan,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Karyawan $karyawan)
    {
        $data = $request->validate([
            'nama' => 'required',
            'nickname' => 'required',
            'jabatan_id' => 'required',
            'gaji_pokok' => 'required',
            'tunjangan_jabatan' => 'required',
            'tunjangan_keluarga' => 'required',
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
            'foto_ktp' => 'nullable|mimes:jpg,jpeg,png|max:10000',
            'foto_diri' => 'nullable|mimes:jpg,jpeg,png|max:10000',
        ]);

        $data['bank'] = "BCA";

        $data['updated_by'] = auth()->user()->id;

        $data['gaji_pokok'] = str_replace('.', '', $data['gaji_pokok']);
        $data['tunjangan_jabatan'] = str_replace('.', '', $data['tunjangan_jabatan']);
        $data['tunjangan_keluarga'] = str_replace('.', '', $data['tunjangan_keluarga']);

        DB::transaction(function () use ($data, $request, $karyawan) {

            if ($request->hasFile('foto_ktp')) {
                $file_name_ktp = Uuid::uuid4().'- KTP - '. $data['nama']. '.' . $request->foto_ktp->extension();
                $data['foto_ktp'] = $request->file('foto_ktp')->storeAs('public/karyawan', $file_name_ktp);
                $ktp_path = storage_path('app/'.$karyawan->foto_ktp);
                unlink($ktp_path);
            }

            if ($request->hasFile('foto_diri')) {
                $file_name_diri = Uuid::uuid4(). ' - Foto Diri '. $data['nama']. '.' . $request->foto_diri->extension();
                $data['foto_diri'] = $request->file('foto_diri')->storeAs('public/karyawan', $file_name_diri);
                $diri_path = storage_path('app/'.$karyawan->foto_diri);
                unlink($diri_path);
            }

            $karyawan->update($data);

        });

        return redirect()->route('karyawan.index')->with('success', 'Karyawan berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Karyawan $karyawan)
    {
       // Delete karyawan data and file
       try {
        DB::beginTransaction();

        $ktp_path = storage_path('app/'.$karyawan->foto_ktp);
        $diri_path = storage_path('app/'.$karyawan->foto_diri);



        $kasbon = KasBon::where('karyawan_id', $karyawan->id)
                ->where('void', 0)
                ->where('lunas', 0)
                ->first();

        if ($kasbon) {
            return redirect()->route('karyawan.index')->with('error', 'Karyawan gagal dihapus! Karyawan masih memiliki kasbon yang belum lunas');
        }

        KasBon::where('karyawan_id', $karyawan->id)->delete();

        $kasbonCicilan = KasBonCicilan::where('karyawan_id', $karyawan->id)
                                    ->where('lunas', 0)
                                    ->first();

        if ($kasbonCicilan) {
            return redirect()->route('karyawan.index')->with('error', 'Karyawan gagal dihapus! Karyawan masih memiliki cicilan kasbon yang belum lunas');
        }

        KasBonCicilan::where('karyawan_id', $karyawan->id)->delete();

        if (file_exists($ktp_path)) {
            unlink($ktp_path);
        }

        if (file_exists($diri_path)) {
            unlink($diri_path);
        }

        $karyawan->delete();

        DB::commit();

       } catch (\Throwable $th) {
              DB::rollBack();
                return redirect()->route('karyawan.index')->with('error', 'Karyawan gagal dihapus! '.$th->getMessage());
       }


        return redirect()->route('karyawan.index')->with('success', 'Karyawan berhasil dihapus');
    }

    public function jabatan_store(Request $request)
    {
        $data = $request->validate([
            'nama_jabatan_tambah' => 'required',
        ]);

        Jabatan::create([
            'nama' => $data['nama_jabatan_tambah'],
        ]);

        return redirect()->route('karyawan.index')->with('success', 'Jabatan berhasil ditambahkan');
    }

    public function jabatan_update(Request $request, Jabatan $jabatan)
    {
        $data = $request->validate([
            'nama_jabatan' => 'required',
        ]);

        $jabatan->update([
            'nama' => $data['nama_jabatan'],
        ]);

        return redirect()->route('karyawan.index')->with('success', 'Jabatan berhasil diupdate');
    }

    public function jabatan_destroy(Jabatan $jabatan)
    {
        $jabatan->delete();

        return redirect()->route('karyawan.index')->with('success', 'Jabatan berhasil dihapus');
    }
}
