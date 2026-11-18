<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MobileDatabaseVehicleResource;
use App\Http\Resources\MobileVendorResource;
use App\Models\Driver;
use App\Models\Karyawan;
use App\Models\Vehicle;
use App\Models\Vendor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Database (baca saja) di aplikasi mobile untuk Admin & SU: biodata vendor, vehicle,
 * driver, dan karyawan (termasuk gaji, sama dengan web), serta foto SIM/KTP/diri.
 */
class DatabaseController extends Controller
{
    public const EMPLOYEE_PHOTOS = ['foto_ktp', 'foto_diri'];

    /** @return array{search?: ?string, page?: int, per_page?: int} */
    private function filters(Request $request): array
    {
        return $request->validate(['search' => ['nullable', 'string', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50']]);
    }

    public function vendors(Request $request): AnonymousResourceCollection
    {
        $filters = $this->filters($request);
        $query = Vendor::query()->select(['id', 'nama', 'nickname', 'perusahaan', 'status']);
        if (($filters['search'] ?? '') !== '') {
            $term = '%'.$filters['search'].'%';
            $query->where(fn ($q) => $q->where('nama', 'like', $term)->orWhere('nickname', 'like', $term)->orWhere('perusahaan', 'like', $term));
        }

        return MobileVendorResource::collection($query->orderBy('nama')->orderBy('id')->paginate($filters['per_page'] ?? 20));
    }

    public function vendor(int $vendor): MobileVendorResource
    {
        return new MobileVendorResource(Vendor::query()->withCount('vehicle')->findOrFail($vendor));
    }

    public function vehicles(Request $request): AnonymousResourceCollection
    {
        $filters = $this->filters($request);
        $query = Vehicle::query()->select(['id', 'vendor_id', 'nomor_lambung', 'nopol', 'tipe', 'tahun', 'status'])->with('vendor:id,nama,perusahaan');
        if (($filters['search'] ?? '') !== '') {
            $term = '%'.$filters['search'].'%';
            $query->where(fn ($q) => $q->where('nomor_lambung', 'like', $term)->orWhere('nopol', 'like', $term));
        }

        return MobileDatabaseVehicleResource::collection($query->orderBy('nomor_lambung')->orderBy('id')->paginate($filters['per_page'] ?? 20));
    }

    public function vehicle(int $vehicle): MobileDatabaseVehicleResource
    {
        return new MobileDatabaseVehicleResource(Vehicle::query()->with(['vendor:id,nama,perusahaan', 'driver:id,nama'])->findOrFail($vehicle));
    }

    public function drivers(Request $request): JsonResponse
    {
        $filters = $this->filters($request);
        $query = Driver::query()->select(['id', 'nama', 'no_sim', 'masa_berlaku_sim', 'no_hp', 'status']);
        if (($filters['search'] ?? '') !== '') {
            $term = '%'.$filters['search'].'%';
            $query->where(fn ($q) => $q->where('nama', 'like', $term)->orWhere('no_sim', 'like', $term)->orWhere('no_hp', 'like', $term));
        }

        return $this->page($query->orderBy('nama')->orderBy('id')->paginate($filters['per_page'] ?? 20), fn (Driver $driver): array => [
            'id' => $driver->id, 'nama' => $driver->nama, 'no_sim' => $driver->no_sim,
            'masa_berlaku_sim' => $driver->masa_berlaku_sim?->toDateString(), 'no_hp' => $driver->no_hp, 'status' => $driver->status,
        ]);
    }

    public function driver(int $driver): JsonResponse
    {
        $driver = Driver::findOrFail($driver);
        $vehicle = Vehicle::query()->where('driver_id', $driver->id)->first(['id', 'nomor_lambung']);

        return response()->json(['data' => [
            'id' => $driver->id, 'nama' => $driver->nama, 'no_sim' => $driver->no_sim,
            'masa_berlaku_sim' => $driver->masa_berlaku_sim?->toDateString(), 'no_hp' => $driver->no_hp, 'alamat' => $driver->alamat,
            'bank' => $driver->bank, 'no_rek' => $driver->no_rek, 'nama_rek' => $driver->nama_rek,
            'status' => $driver->status, 'keterangan' => $driver->keterangan,
            'nomor_lambung' => $vehicle?->nomor_lambung,
            'has_sim_photo' => $this->publicPath($driver->foto_sim) !== null,
        ]]);
    }

    public function driverSimPhoto(int $driver): Response
    {
        $path = $this->publicPath(Driver::findOrFail($driver)->foto_sim);
        abort_if($path === null, 404);

        return Storage::disk('public')->response($path, 'sim-'.$driver.'.'.pathinfo($path, PATHINFO_EXTENSION), $this->privateHeaders());
    }

    public function employees(Request $request): JsonResponse
    {
        $filters = $this->filters($request) + $request->validate(['status' => ['nullable', 'in:aktif,nonaktif,semua']]);
        $status = $filters['status'] ?? 'aktif';
        $query = Karyawan::query()->leftJoin('jabatans', 'karyawans.jabatan_id', '=', 'jabatans.id')
            ->select(['karyawans.id', 'karyawans.nama', 'karyawans.nickname', 'karyawans.status', 'jabatans.nama as jabatan'])
            ->when($status !== 'semua', fn ($q) => $q->where('karyawans.status', $status));
        if (($filters['search'] ?? '') !== '') {
            $term = '%'.$filters['search'].'%';
            $query->where(fn ($q) => $q->where('karyawans.nama', 'like', $term)->orWhere('karyawans.nickname', 'like', $term)->orWhere('jabatans.nama', 'like', $term));
        }

        return $this->page($query->orderBy('karyawans.nama')->orderBy('karyawans.id')->paginate($filters['per_page'] ?? 20), fn (Karyawan $employee): array => [
            'id' => $employee->id, 'nama' => $employee->nama, 'nickname' => $employee->nickname,
            'jabatan' => $employee->jabatan, 'status' => $employee->status,
        ]);
    }

    public function employee(int $employee): JsonResponse
    {
        $employee = Karyawan::query()->with('jabatan:id,nama')->findOrFail($employee);
        $data = ['id' => $employee->id, 'jabatan' => $employee->jabatan?->nama];
        foreach (['nomor', 'nama', 'nickname', 'nik', 'npwp', 'tempat_lahir', 'tanggal_lahir', 'alamat', 'no_hp', 'no_wa',
            'mulai_bekerja', 'status', 'bank', 'no_rekening', 'nama_rekening', 'bpjs_tk', 'bpjs_kesehatan'] as $field) {
            $data[$field] = $employee->getAttribute($field);
        }
        foreach (['gaji_pokok', 'tunjangan_jabatan', 'tunjangan_keluarga'] as $field) {
            $data[$field] = (int) $employee->getAttribute($field);
        }
        $data['apa_bpjs_tk'] = (bool) $employee->apa_bpjs_tk;
        $data['apa_bpjs_kesehatan'] = (bool) $employee->apa_bpjs_kesehatan;
        foreach (self::EMPLOYEE_PHOTOS as $document) {
            $data['has_'.$document] = $this->employeePhotoPath($employee, $document) !== null;
        }
        $data['has_perjanjian_kerja'] = (bool) $employee->perjanjian_kerja;

        return response()->json(['data' => $data]);
    }

    public function employeePhoto(int $employee, string $document): Response
    {
        $path = $this->employeePhotoPath(Karyawan::findOrFail($employee), $document);
        abort_if($path === null, 404);

        return Storage::disk('local')->response($path, $document.'-'.$employee.'.'.pathinfo($path, PATHINFO_EXTENSION), $this->privateHeaders());
    }

    /** Foto KTP/diri karyawan: disk lokal di bawah public/karyawan/, sama dengan web. */
    private function employeePhotoPath(Karyawan $employee, string $document): ?string
    {
        $path = in_array($document, self::EMPLOYEE_PHOTOS, true) ? $employee->$document : null;

        return $path && str_starts_with($path, 'public/karyawan/') && ! str_contains($path, '..')
            && Storage::disk('local')->exists($path) ? $path : null;
    }

    /** Foto SIM driver: disk public di bawah drivers_sim/. */
    private function publicPath(?string $path): ?string
    {
        return $path && str_starts_with($path, 'drivers_sim/') && ! str_contains($path, '..')
            && Storage::disk('public')->exists($path) ? $path : null;
    }

    /** @return array<string, string> */
    private function privateHeaders(): array
    {
        return ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff'];
    }

    private function page(LengthAwarePaginator $page, callable $present): JsonResponse
    {
        return response()->json([
            'data' => $page->getCollection()->map($present)->values(),
            'meta' => ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'total' => $page->total()],
        ]);
    }
}
