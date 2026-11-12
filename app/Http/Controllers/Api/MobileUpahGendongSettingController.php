<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UpahGendong;
use App\Models\Vehicle;
use App\Support\MaintenanceHistoryGuard;
use App\Support\UpahGendongData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Database Upah Gendong untuk akun vendor di aplikasi mobile: mengaktifkan,
 * mengubah dan menghapus data upah gendong kendaraan miliknya sendiri,
 * dengan aturan isi yang sama dengan menu Database admin.
 */
class MobileUpahGendongSettingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorizeVendor($request);
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:100'], 'page' => ['sometimes', 'integer', 'min:1']]);
        $search = trim($filters['search'] ?? '');
        $page = Vehicle::query()->where('vendor_id', $request->user()->vendor_id)->where('status', '!=', 'nonaktif')
            ->when($search !== '', fn ($query) => $query->where('nomor_lambung', 'like', '%'.$search.'%'))
            ->orderBy('nomor_lambung')->orderBy('id')->paginate(20, ['id', 'nomor_lambung', 'nopol', 'status']);
        $settings = UpahGendong::query()->whereIn('vehicle_id', $page->getCollection()->modelKeys())->get()->keyBy('vehicle_id');

        return response()->json([
            'data' => $page->getCollection()->map(fn (Vehicle $vehicle): array => [
                'id' => $vehicle->id, 'nomor_lambung' => $vehicle->nomor_lambung, 'nopol' => $vehicle->nopol, 'status' => $vehicle->status,
                'setting' => $this->present($settings->get($vehicle->id)),
            ])->values(),
            'meta' => ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'total' => $page->total()],
        ]);
    }

    public function store(Request $request, int $vehicle): JsonResponse
    {
        $unit = $this->vehicle($request, $vehicle);
        if (UpahGendong::query()->where('vehicle_id', $unit->id)->exists()) {
            throw ValidationException::withMessages(['vehicle_id' => UpahGendongData::messages()['vehicle_id.unique']]);
        }
        $data = UpahGendongData::normalize($request->validate(UpahGendongData::rules(), UpahGendongData::messages()));
        $setting = UpahGendongData::write(fn () => UpahGendong::create($data + ['vehicle_id' => $unit->id]));

        return response()->json(['message' => 'Upah gendong nolam '.$unit->nomor_lambung.' diaktifkan.', 'data' => $this->present($setting)], 201);
    }

    public function update(Request $request, int $vehicle): JsonResponse
    {
        $setting = $this->setting($request, $vehicle);
        $setting->update(UpahGendongData::normalize($request->validate(UpahGendongData::rules($setting), UpahGendongData::messages())));

        return response()->json(['message' => 'Data upah gendong diperbarui.', 'data' => $this->present($setting->refresh())]);
    }

    public function destroy(Request $request, int $vehicle): JsonResponse
    {
        $setting = $this->setting($request, $vehicle);
        $request->validate(['password' => ['required', 'string', 'max:1024']], ['password.required' => 'Masukkan password akun Anda.']);
        if (! MaintenanceHistoryGuard::passwordMatches($request)) {
            throw ValidationException::withMessages(['password' => 'Password salah.']);
        }
        $setting->delete();

        return response()->json(['message' => 'Data upah gendong dihapus.']);
    }

    private function authorizeVendor(Request $request): void
    {
        abort_unless($request->user()->isVendorAccount() && $request->user()->canManageUpahGendong(), 403);
    }

    private function vehicle(Request $request, int $id): Vehicle
    {
        $this->authorizeVendor($request);

        return Vehicle::query()->where('vendor_id', $request->user()->vendor_id)->where('status', '!=', 'nonaktif')->findOrFail($id);
    }

    private function setting(Request $request, int $vehicle): UpahGendong
    {
        return UpahGendong::query()->where('vehicle_id', $this->vehicle($request, $vehicle)->id)->firstOrFail();
    }

    /** @return ?array<string, mixed> */
    private function present(?UpahGendong $setting): ?array
    {
        return $setting === null ? null : [
            'nominal' => (int) $setting->nominal, 'tonase_min' => (float) $setting->tonase_min,
            'nama_driver' => $setting->nama_driver, 'tanggal_masuk_driver' => $setting->tanggal_masuk_driver,
            'nama_pengurus' => $setting->nama_pengurus, 'tanggal_masuk_pengurus' => $setting->tanggal_masuk_pengurus,
            'bank' => $setting->bank, 'nama_rek' => $setting->nama_rek, 'no_rek' => $setting->no_rek,
        ];
    }
}
