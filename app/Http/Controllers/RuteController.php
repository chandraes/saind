<?php

namespace App\Http\Controllers;

use App\Models\Rute;
use App\Models\RuteNoteAlias;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
                ->editColumn('uang_jalan', function ($row) {
                    return 'Rp '.number_format($row->uang_jalan, 0, ',', '.');
                })
                ->editColumn('uj_ditahan', function ($row) {
                    return 'Rp '.number_format($row->uj_ditahan, 0, ',', '.');
                })

                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex justify-content-center">';
                    // Melempar data ke fungsi javascript untuk modal edit
                    $btn .= '<button type="button" class="btn btn-warning btn-sm me-2 rounded shadow-sm" onclick="editRute('.$row->id.', \''.$row->nama.'\', '.$row->jarak.', '.$row->uang_jalan.', '.$row->uj_ditahan.')"><i class="fa fa-edit"></i> Ubah</button>';

                    $btn .= '<form action="'.route('rute.destroy', $row->id).'" method="POST" class="deleteForm d-inline">';
                    $btn .= csrf_field().method_field('DELETE');
                    $btn .= '<button type="submit" class="btn btn-danger btn-sm rounded shadow-sm"><i class="fa fa-trash"></i> Hapus</button>';
                    $btn .= '</form></div>';

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('database.rute.index', [
            'routeChoices' => Rute::select(['id', 'nama'])->orderBy('nama')->get(),
            'noteAliases' => RuteNoteAlias::with('rute:id,nama')->orderBy('name')->paginate(20, ['*'], 'alias_page'),
        ]);
    }

    public function storeAlias(Request $request, Rute $rute): RedirectResponse
    {
        $request->merge(['alias_rute_id' => $rute->id]);
        $data = $request->validate(['name' => ['required', 'string', 'max:191']]);
        $normalized = RuteNoteAlias::normalize($data['name']);
        Validator::make(['name' => $normalized], ['name' => ['required', Rule::unique('rute_note_aliases', 'normalized_name')]],
            ['name.required' => 'Alias harus mengandung huruf atau angka.', 'name.unique' => 'Alias nota sudah digunakan pada rute lain atau rute ini.'])->validate();
        try {
            RuteNoteAlias::create(['rute_id' => $rute->id, 'name' => $data['name']]);
        } catch (UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages(['name' => 'Alias nota sudah digunakan pada rute lain atau rute ini.']);
        }

        return to_route('rute.index')->with('success', 'Alias nota berhasil ditambahkan.');
    }

    public function destroyAlias(Rute $rute, RuteNoteAlias $alias): RedirectResponse
    {
        abort_unless((int) $alias->rute_id === (int) $rute->id, 404);
        $alias->delete();

        return to_route('rute.index')->with('success', 'Alias nota berhasil dihapus.');
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
            'uj_ditahan.lte' => 'Uang jalan ditahan tidak boleh lebih besar dari Uang Jalan utama.',
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
            'uj_ditahan.lte' => 'Uang jalan ditahan tidak boleh lebih besar dari Uang Jalan utama.',
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
