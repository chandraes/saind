<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveKaryawanRequest;
use App\Models\Jabatan;
use App\Models\Karyawan;
use App\Models\KasBon;
use App\Models\KasBonCicilan;
use App\Models\RekapGajiDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class KaryawanController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $status = $request->input('status', 'aktif');
        if (! in_array($status, ['aktif', 'nonaktif', 'semua'], true)) {
            $status = 'aktif';
        }
        if (! $request->ajax()) {
            return view('database.karyawan.index', ['jabatan' => Jabatan::orderBy('nama')->get(['id', 'nama']), 'status' => $status]);
        }
        $request->merge(['length' => min(100, max(1, $request->integer('length', 10))), 'start' => max(0, $request->integer('start')), 'columns' => []]);
        $query = DB::table('karyawans')->leftJoin('jabatans', 'karyawans.jabatan_id', '=', 'jabatans.id')
            ->select('karyawans.id', 'karyawans.nama', 'karyawans.nickname', 'karyawans.status', 'jabatans.nama as jabatan');

        return DataTables::of($query)->filter(function (Builder $query) use ($request, $status): void {
            if ($status !== 'semua') {
                $query->where('karyawans.status', $status);
            }
            $search = mb_substr($request->string('search.value')->trim()->toString(), 0, 100);
            if ($search !== '') {
                $query->where(function (Builder $query) use ($search): void {
                    foreach (['karyawans.nama', 'karyawans.nickname', 'jabatans.nama'] as $column) {
                        $query->orWhere($column, 'like', '%'.$search.'%');
                    }
                });
            }
        })->order(function (Builder $query) use ($request): void {
            $columns = [1 => 'karyawans.nama', 2 => 'karyawans.nickname', 3 => 'jabatans.nama', 4 => 'karyawans.status'];
            $query->orderBy($columns[$request->integer('order.0.column', 1)] ?? 'karyawans.nama', $request->input('order.0.dir') === 'desc' ? 'desc' : 'asc')->orderBy('karyawans.id');
        })->addIndexColumn()->addColumn('action', fn (object $employee): string => view('database.karyawan.actions', compact('employee'))->render())->rawColumns(['action'])->toJson();
    }

    public function create(): View
    {
        return view('database.karyawan.create', ['jabatan' => Jabatan::orderBy('nama')->get(['id', 'nama']), 'karyawan' => new Karyawan, 'photos' => ['foto_ktp' => null, 'foto_diri' => null]]);
    }

    public function store(SaveKaryawanRequest $request): JsonResponse|RedirectResponse
    {
        return $this->saveEmployee($request);
    }

    public function show(Request $request, Karyawan $karyawan): View|Response
    {
        $karyawan->load('jabatan');
        $dates = ['lahir' => $this->formatDate($karyawan->tanggal_lahir), 'mulai' => $this->formatDate($karyawan->mulai_bekerja)];
        $photos = $this->photoPaths($karyawan);
        if ($request->input('format') === 'html') {
            return view('database.karyawan.detail', compact('karyawan', 'dates', 'photos'));
        }

        return Pdf::loadView('database.karyawan.show', compact('karyawan', 'dates', 'photos'))->setOption('isRemoteEnabled', false)->setPaper('a4', 'landscape')->stream('biodata-pegawai-'.$karyawan->id.'.pdf');
    }

    public function edit(Karyawan $karyawan): View
    {
        return view('database.karyawan.edit', ['karyawan' => $karyawan, 'jabatan' => Jabatan::orderBy('nama')->get(['id', 'nama']), 'photos' => $this->photoPaths($karyawan)]);
    }

    public function update(SaveKaryawanRequest $request, Karyawan $karyawan): JsonResponse|RedirectResponse
    {
        return $this->saveEmployee($request, $karyawan);
    }

    private function saveEmployee(SaveKaryawanRequest $request, ?Karyawan $employee = null): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        foreach (['tunjangan_jabatan', 'tunjangan_keluarga'] as $field) {
            $data[$field] = $data[$field] ?? 0;
        }
        foreach (['bpjs_tk', 'bpjs_kesehatan'] as $field) {
            $data[$field] = $data[$field] ?? '-';
        }
        $newFiles = [];
        $oldFiles = [];
        try {
            foreach (['foto_ktp', 'foto_diri'] as $field) {
                unset($data[$field]);
                if ($request->hasFile($field)) {
                    $path = $request->file($field)->store('public/karyawan', 'local');
                    if (! $path) {
                        throw new RuntimeException('Penyimpanan foto pegawai gagal.');
                    }
                    $data[$field] = $path;
                    $newFiles[] = $path;
                }
            }
            DB::transaction(function () use ($data, $request, $employee, &$oldFiles): void {
                $position = Jabatan::whereKey($data['jabatan_id'])->lockForUpdate()->first();
                if (! $position) {
                    throw ValidationException::withMessages(['jabatan_id' => 'Jabatan tidak tersedia. Pilih jabatan lainnya.']);
                }
                if ($employee) {
                    $locked = Karyawan::whereKey($employee->id)->lockForUpdate()->firstOrFail();
                    foreach (['foto_ktp', 'foto_diri'] as $field) {
                        if (isset($data[$field]) && $locked->$field) {
                            $oldFiles[] = $locked->$field;
                        }
                    }
                    $locked->update(array_merge($data, ['updated_by' => $request->user()->id]));
                } else {
                    $last = Karyawan::orderByDesc('nomor')->lockForUpdate()->first(['nomor']);
                    Karyawan::create(array_merge($data, ['nomor' => ($last?->nomor ?? 0) + 1, 'created_by' => $request->user()->id]));
                }
            });
        } catch (Throwable $exception) {
            $this->deletePhotos($newFiles);
            if ($exception instanceof ValidationException) {
                throw $exception;
            }
            report($exception);

            return $this->failure($request, 'Data pegawai belum tersimpan. Silakan coba kembali.');
        }
        $this->deletePhotos($oldFiles);

        return $this->success($request, $employee ? 'Pegawai berhasil diperbarui.' : 'Pegawai berhasil ditambahkan.');
    }

    public function destroy(Request $request, Karyawan $karyawan): JsonResponse|RedirectResponse
    {
        try {
            DB::transaction(function () use ($karyawan): void {
                $employee = Karyawan::whereKey($karyawan->id)->lockForUpdate()->firstOrFail();
                if (KasBon::where('karyawan_id', $employee->id)->where('void', 0)->where('lunas', 0)->exists()) {
                    throw ValidationException::withMessages(['karyawan' => 'Pegawai masih memiliki kasbon yang belum lunas.']);
                }
                if (KasBonCicilan::where('karyawan_id', $employee->id)->where('lunas', 0)->exists()) {
                    throw ValidationException::withMessages(['karyawan' => 'Pegawai masih memiliki cicilan kasbon yang belum lunas.']);
                }
                if (KasBon::where('karyawan_id', $employee->id)->exists() || KasBonCicilan::where('karyawan_id', $employee->id)->exists() || RekapGajiDetail::where('karyawan_id', $employee->id)->exists()) {
                    throw ValidationException::withMessages(['karyawan' => 'Pegawai memiliki riwayat keuangan. Ubah status menjadi nonaktif agar riwayat tetap tersimpan.']);
                }
                $employee->delete();
                DB::afterCommit(fn () => $this->deletePhotos(array_filter([$employee->foto_ktp, $employee->foto_diri])));
            });
        } catch (ValidationException $exception) {
            return $this->failure($request, $exception->validator->errors()->first());
        } catch (Throwable $exception) {
            report($exception);

            return $this->failure($request, 'Pegawai belum dapat dihapus. Silakan coba kembali.');
        }

        return $this->success($request, 'Pegawai berhasil dihapus.');
    }

    public function jabatan_store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate(['nama_jabatan_tambah' => ['required', 'string', 'max:255', Rule::unique('jabatans', 'nama')]]);
        Jabatan::create(['nama' => $data['nama_jabatan_tambah']]);

        return $this->success($request, 'Jabatan berhasil ditambahkan.');
    }

    public function jabatan_update(Request $request, Jabatan $jabatan): JsonResponse|RedirectResponse
    {
        $data = $request->validate(['nama_jabatan' => ['required', 'string', 'max:255', Rule::unique('jabatans', 'nama')->ignore($jabatan->id)]]);
        DB::transaction(function () use ($jabatan, $data): void {
            $locked = Jabatan::whereKey($jabatan->id)->lockForUpdate()->firstOrFail();
            $locked->update(['nama' => $data['nama_jabatan']]);
            if (config('attendance.enabled')) {
                Karyawan::where('jabatan_id', $locked->id)->chunkById(100, function ($employees): void {
                    foreach ($employees as $employee) {
                        $employee->save();
                    }
                });
            }
        });

        return $this->success($request, 'Jabatan berhasil diperbarui.');
    }

    public function jabatan_destroy(Request $request, Jabatan $jabatan): JsonResponse|RedirectResponse
    {
        $deleted = DB::transaction(function () use ($jabatan): bool {
            $locked = Jabatan::whereKey($jabatan->id)->lockForUpdate()->firstOrFail();
            if ($locked->karyawan()->exists()) {
                return false;
            }
            $locked->delete();

            return true;
        });

        return $deleted ? $this->success($request, 'Jabatan berhasil dihapus.') : $this->failure($request, 'Jabatan masih digunakan pegawai dan tidak dapat dihapus.');
    }

    /** @param array<int, string> $paths */
    private function deletePhotos(array $paths): void
    {
        foreach ($paths as $path) {
            if (str_starts_with($path, 'public/karyawan/')) {
                try {
                    Storage::disk('local')->delete($path);
                } catch (Throwable $exception) {
                    report($exception);
                }
            }
        }
    }

    /** @return array<string, string|null> */
    private function photoPaths(Karyawan $employee): array
    {
        $photos = [];
        foreach (['foto_ktp', 'foto_diri'] as $field) {
            $photos[$field] = $employee->$field && Storage::disk('local')->exists($employee->$field) ? Storage::disk('local')->path($employee->$field) : null;
        }

        return $photos;
    }

    private function formatDate(?string $date): string
    {
        return $date ? Carbon::parse($date)->locale('id')->translatedFormat('d F Y') : '-';
    }

    private function success(Request $request, string $message): JsonResponse|RedirectResponse
    {
        return $request->expectsJson() ? response()->json(['message' => $message, 'redirect' => route('karyawan.index')]) : redirect()->route('karyawan.index')->with('success', $message);
    }

    private function failure(Request $request, string $message): JsonResponse|RedirectResponse
    {
        return $request->expectsJson() ? response()->json(['message' => $message], 422) : back()->withInput($request->except(['foto_ktp', 'foto_diri']))->with('error', $message);
    }
}
