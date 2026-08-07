<?php

namespace App\Http\Controllers;

use App\Models\Rute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables; // Pastikan class ini di-import

class RuteController extends Controller
{
    public function index(Request $request)
    {
        // Implementasi Server-Side Yajra DataTables
        if ($request->ajax()) {
            $data = Rute::with(['user'])->select('rutes.*');

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('uang_jalan', function($row){
                    return 'Rp ' . number_format($row->uang_jalan, 0, ',', '.');
                })
                ->editColumn('uj_ditahan', function($row){
                    return 'Rp ' . number_format($row->uj_ditahan, 0, ',', '.');
                })
             
                ->addColumn('action', function($row){
                    $btn = '<div class="d-flex justify-content-center">';
                    // Melempar data ke fungsi javascript untuk modal edit
                    $btn .= '<button type="button" class="btn btn-warning btn-sm me-2 rounded shadow-sm" onclick="editRute('.$row->id.', \''.$row->nama.'\', '.$row->jarak.', '.$row->uang_jalan.', '.$row->uj_ditahan.')"><i class="fa fa-edit"></i> Ubah</button>';

                    $btn .= '<form action="'.route('rute.destroy', $row->id).'" method="POST" class="deleteForm d-inline">';
                    $btn .= csrf_field() . method_field("DELETE");
                    $btn .= '<button type="submit" class="btn btn-danger btn-sm rounded shadow-sm"><i class="fa fa-trash"></i> Hapus</button>';
                    $btn .= '</form></div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('database.rute.index');
    }

    public function store(Request $request)
    {
        // 1. Bersihkan format titik sebelum validasi agar terbaca sebagai angka bulat
        $request->merge([
            'uang_jalan' => str_replace('.', '', $request->uang_jalan),
            'uj_ditahan' => str_replace('.', '', $request->uj_ditahan),
        ]);

        // 2. Jalankan validasi menggunakan lte:uang_jalan (Less Than or Equal to uang_jalan)
        $data = $request->validate([
            'nama' => 'required',
            'jarak' => 'required|numeric',
            'uang_jalan' => 'required|numeric|min:0',
            'uj_ditahan' => 'required|numeric|min:0|lte:uang_jalan',
        ], [
            'uj_ditahan.lte' => 'Uang jalan ditahan tidak boleh lebih besar dari Uang Jalan utama.'
        ]);

        $data['user_id'] = Auth::id();

        Rute::create($data);

        return redirect()->route('rute.index')->with('success', 'Rute berhasil ditambahkan');
    }

    public function update(Request $request, Rute $rute)
    {
        // 1. Bersihkan format titik sebelum validasi
        $request->merge([
            'uang_jalan' => str_replace('.', '', $request->uang_jalan),
            'uj_ditahan' => str_replace('.', '', $request->uj_ditahan),
        ]);

        // 2. Validasi
        $data = $request->validate([
            'nama' => 'required',
            'jarak' => 'required|numeric',
            'uang_jalan' => 'required|numeric|min:0',
            'uj_ditahan' => 'required|numeric|min:0|lte:uang_jalan',
        ], [
            'uj_ditahan.lte' => 'Uang jalan ditahan tidak boleh lebih besar dari Uang Jalan utama.'
        ]);

        $data['edited_by'] = Auth::id();

        $rute->update($data);

        return redirect()->route('rute.index')->with('success', 'Rute berhasil diubah');
    }

    public function destroy(Rute $rute)
    {
        $rute->delete();
        return redirect()->route('rute.index')->with('success', 'Rute berhasil dihapus');
    }
}
