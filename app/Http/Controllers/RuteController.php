<?php

namespace App\Http\Controllers;

use App\Models\Rute;
use App\Models\RuteNoteAlias;
use Illuminate\Contracts\View\View;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
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
            $data = Rute::with(['user', 'noteAliases'])->select('rutes.*');

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('uang_jalan', function ($row) {
                    return 'Rp '.number_format($row->uang_jalan, 0, ',', '.');
                })
                ->editColumn('uj_ditahan', function ($row) {
                    return 'Rp '.number_format($row->uj_ditahan, 0, ',', '.');
                })

                ->addColumn('note_aliases', fn (Rute $row): string => view('database.rute.nota-alias-cell', ['rute' => $row])->render())
                ->addColumn('action', fn (Rute $row): string => view('database.rute.actions', ['rute' => $row])->render())
                ->rawColumns(['action', 'note_aliases'])
                ->make(true);
        }

        return view('database.rute.index');
    }

    public function aliases(Rute $rute): View
    {
        return view('database.rute.nota-alias', ['rute' => $rute->load('noteAliases')]);
    }

    /** @return array{name: string} */
    private function validateAlias(Request $request, ?RuteNoteAlias $alias = null): array
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:191']]);
        $unique = Rule::unique('rute_note_aliases', 'normalized_name');
        if ($alias !== null) {
            $unique->ignore($alias);
        }
        Validator::make(['name' => RuteNoteAlias::normalize($data['name'])], ['name' => ['required', $unique]],
            ['name.required' => 'Alias harus mengandung huruf atau angka.', 'name.unique' => 'Alias nota sudah digunakan pada rute lain atau rute ini.'])->validate();

        return $data;
    }

    private function aliasResponse(Request $request, string $message): JsonResponse|RedirectResponse
    {
        return $request->expectsJson() ? response()->json(['message' => $message]) : to_route('rute.index')->with('success', $message);
    }

    public function storeAlias(Request $request, Rute $rute): JsonResponse|RedirectResponse
    {
        $data = $this->validateAlias($request);
        try {
            $rute->noteAliases()->create($data);
        } catch (UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages(['name' => 'Alias nota sudah digunakan pada rute lain atau rute ini.']);
        }

        return $this->aliasResponse($request, 'Alias nota berhasil ditambahkan.');
    }

    public function updateAlias(Request $request, Rute $rute, RuteNoteAlias $alias): JsonResponse|RedirectResponse
    {
        abort_unless((int) $alias->rute_id === (int) $rute->id, 404);
        $data = $this->validateAlias($request, $alias);
        try {
            $alias->update($data);
        } catch (UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages(['name' => 'Alias nota sudah digunakan pada rute lain atau rute ini.']);
        }

        return $this->aliasResponse($request, 'Alias nota berhasil diubah.');
    }

    public function destroyAlias(Request $request, Rute $rute, RuteNoteAlias $alias): JsonResponse|RedirectResponse
    {
        abort_unless((int) $alias->rute_id === (int) $rute->id, 404);
        $alias->delete();

        return $this->aliasResponse($request, 'Alias nota berhasil dihapus.');
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
