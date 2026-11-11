<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AkiLog;
use App\Models\BanLog;
use App\Models\FilterOliLog;
use App\Models\Vehicle;
use App\Services\MaintenanceLogWriter;
use App\Support\MaintenanceHistoryGuard;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Riwayat ban, aki, dan filter & oli dari aplikasi mobile akun vendor.
 * Tambah langsung menjadi histori kendaraan (tanpa Form Maintenance/otorisasi)
 * lewat MaintenanceLogWriter yang sama dengan otorisasi admin. Edit tanggal
 * dan hapus memakai MaintenanceHistoryGuard + konfirmasi password akun sendiri.
 */
class MobileMaintenanceHistoryController extends Controller
{
    public function __construct(private MaintenanceLogWriter $writer) {}

    public function storeTire(Request $request, int $vehicle): JsonResponse
    {
        $unit = $this->ownedVehicle($request, $vehicle);
        $data = $request->validate([
            'position_id' => ['required', 'integer', Rule::exists('posisi_bans', 'id')],
            'source' => ['required', Rule::in(['baru', 'serep'])],
            'brand' => ['required_if:source,baru', 'nullable', 'string', 'max:100'],
            'serial_number' => ['required_if:source,baru', 'nullable', 'string', 'max:100'],
            'condition' => ['required_if:source,baru', 'nullable', 'integer', 'min:1', 'max:100'],
            ...$this->dateRule(),
        ], $this->messages());
        if ($data['source'] === 'serep' && (int) $data['position_id'] === MaintenanceLogWriter::SPARE_TIRE_POSITION) {
            throw ValidationException::withMessages(['position_id' => 'Posisi serep tidak bisa diisi dari ban serep.']);
        }
        $log = DB::transaction(function () use ($unit, $data): BanLog {
            Vehicle::whereKey($unit->id)->lockForUpdate()->firstOrFail();
            $baru = $data['source'] === 'baru';

            return $this->writer->tire($unit->id, (int) $data['position_id'], $data['source'],
                $baru ? strtoupper(trim($data['brand'])) : null, $baru ? strtoupper(trim($data['serial_number'])) : null,
                $baru ? (int) $data['condition'] : null, $this->date($data));
        }, 3);

        return $this->created('Ban berhasil dicatat.', $log);
    }

    public function storeBattery(Request $request, int $vehicle): JsonResponse
    {
        $unit = $this->ownedVehicle($request, $vehicle);
        $data = $request->validate([
            'position_id' => ['required', 'integer', Rule::exists('posisi_akis', 'id')],
            'brand' => ['required', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'condition' => ['required', 'integer', 'min:1', 'max:100'],
            ...$this->dateRule(),
        ], $this->messages());
        $serial = trim((string) ($data['serial_number'] ?? ''));
        $log = $this->writer->battery($unit->id, (int) $data['position_id'], strtoupper(trim($data['brand'])),
            $serial === '' ? null : strtoupper($serial), (int) $data['condition'], $this->date($data));

        return $this->created('Aki berhasil dicatat.', $log);
    }

    public function storeFilterOil(Request $request, int $vehicle): JsonResponse
    {
        $unit = $this->ownedVehicle($request, $vehicle);
        $data = $request->validate([
            'category_id' => ['required', 'integer', Rule::exists('kategori_filter_oli_mesins', 'id')],
            'brand' => ['required', 'string', 'max:100'],
            'condition' => ['required', 'integer', 'min:1', 'max:100'],
            ...$this->dateRule(),
        ], $this->messages());
        $log = DB::transaction(function () use ($unit, $data): FilterOliLog {
            Vehicle::whereKey($unit->id)->lockForUpdate()->firstOrFail();
            $log = $this->writer->filterOil($unit->id, (int) $data['category_id'], strtoupper(trim($data['brand'])),
                (int) $data['condition'], $this->date($data));
            $this->writer->refreshFilterOil($unit->id);

            return $log;
        }, 3);

        return $this->created('Filter & oli berhasil dicatat.', $log);
    }

    public function updateTire(Request $request, int $vehicle, int $banLog): JsonResponse
    {
        return $this->reschedule($request, $this->log(BanLog::class, $request, $vehicle, $banLog));
    }

    public function destroyTire(Request $request, int $vehicle, int $banLog): JsonResponse
    {
        return $this->destroy($request, $this->log(BanLog::class, $request, $vehicle, $banLog));
    }

    public function updateBattery(Request $request, int $vehicle, int $akiLog): JsonResponse
    {
        return $this->reschedule($request, $this->log(AkiLog::class, $request, $vehicle, $akiLog));
    }

    public function destroyBattery(Request $request, int $vehicle, int $akiLog): JsonResponse
    {
        return $this->destroy($request, $this->log(AkiLog::class, $request, $vehicle, $akiLog));
    }

    public function updateFilterOil(Request $request, int $vehicle, int $log): JsonResponse
    {
        return $this->reschedule($request, $this->log(FilterOliLog::class, $request, $vehicle, $log));
    }

    public function destroyFilterOil(Request $request, int $vehicle, int $log): JsonResponse
    {
        return $this->destroy($request, $this->log(FilterOliLog::class, $request, $vehicle, $log));
    }

    private function ownedVehicle(Request $request, int $vehicle): Vehicle
    {
        $user = $request->user();
        abort_unless($user->isVendorAccount() && $user->canManageMaintenanceHistory(), 403);

        return Vehicle::query()->where('vendor_id', $user->vendor_id)->where('status', '!=', 'nonaktif')->findOrFail($vehicle);
    }

    /** @param class-string<BanLog|AkiLog|FilterOliLog> $model */
    private function log(string $model, Request $request, int $vehicle, int $id): BanLog|AkiLog|FilterOliLog
    {
        $unit = Vehicle::query()->where('vendor_id', $request->user()->vendor_id)->findOrFail($vehicle);
        $log = $model::query()->where('vehicle_id', $unit->id)->findOrFail($id);
        MaintenanceHistoryGuard::authorize($request, $log);

        return $log;
    }

    /** @return array<string, array<int, string>> */
    private function dateRule(): array
    {
        return ['replaced_on' => ['required', 'date_format:Y-m-d', 'before_or_equal:today']];
    }

    /** @param array<string, mixed> $data */
    private function date(array $data): Carbon
    {
        return Carbon::createFromFormat('Y-m-d', $data['replaced_on'])->startOfDay();
    }

    /** @return array<string, string> */
    private function messages(): array
    {
        return [
            'position_id.required' => 'Pilih posisi.',
            'category_id.required' => 'Pilih kategori.',
            'brand.required' => 'Merk wajib diisi.',
            'brand.required_if' => 'Merk wajib diisi untuk ban baru.',
            'serial_number.required_if' => 'Nomor seri wajib diisi untuk ban baru.',
            'condition.required' => 'Kondisi wajib diisi.',
            'condition.required_if' => 'Kondisi wajib diisi untuk ban baru.',
            'condition.min' => 'Kondisi antara 1 sampai 100.',
            'condition.max' => 'Kondisi antara 1 sampai 100.',
            'replaced_on.required' => 'Tanggal ganti wajib diisi.',
            'replaced_on.before_or_equal' => 'Tanggal ganti tidak boleh melebihi hari ini.',
        ];
    }

    private function created(string $message, BanLog|AkiLog|FilterOliLog $log): JsonResponse
    {
        return response()->json(['message' => $message, 'data' => ['id' => $log->id, 'tanggal_ganti' => $log->created_at->toISOString()]], 201);
    }

    private function confirmPassword(Request $request): void
    {
        $request->validate(['password' => ['required', 'string', 'max:1024']], ['password.required' => 'Masukkan password akun Anda.']);
        if (! MaintenanceHistoryGuard::passwordMatches($request)) {
            throw ValidationException::withMessages(['password' => 'Password salah.']);
        }
    }

    private function reschedule(Request $request, BanLog|AkiLog|FilterOliLog $log): JsonResponse
    {
        $data = $request->validate($this->dateRule(), $this->messages());
        $this->confirmPassword($request);
        if ($log instanceof FilterOliLog) {
            $this->writer->rescheduleFilterOil($log, $this->date($data));
        } else {
            $log->update(['created_at' => $this->date($data)]);
        }

        return response()->json(['message' => 'Tanggal ganti berhasil diubah.', 'data' => ['id' => $log->id, 'tanggal_ganti' => $log->fresh()->created_at->toISOString()]]);
    }

    private function destroy(Request $request, BanLog|AkiLog|FilterOliLog $log): JsonResponse
    {
        $this->confirmPassword($request);
        $log instanceof FilterOliLog ? $this->writer->removeFilterOil($log) : $log->delete();

        return response()->json(['message' => 'Riwayat berhasil dihapus.']);
    }
}
