<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AkiLog;
use App\Models\BanLog;
use App\Models\Vehicle;
use App\Support\MaintenanceHistoryGuard;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Edit tanggal ganti dan hapus riwayat ban/aki dari aplikasi mobile.
 * Aturannya sama dengan web (MaintenanceHistoryGuard): hanya kendaraan milik
 * vendor akun, dikonfirmasi password akun sendiri; vendor operational ditolak.
 */
class MobileMaintenanceHistoryController extends Controller
{
    public function updateTire(Request $request, int $vehicle, int $banLog): JsonResponse
    {
        return $this->update($request, $this->log(BanLog::class, $request, $vehicle, $banLog));
    }

    public function destroyTire(Request $request, int $vehicle, int $banLog): JsonResponse
    {
        return $this->destroy($request, $this->log(BanLog::class, $request, $vehicle, $banLog));
    }

    public function updateBattery(Request $request, int $vehicle, int $akiLog): JsonResponse
    {
        return $this->update($request, $this->log(AkiLog::class, $request, $vehicle, $akiLog));
    }

    public function destroyBattery(Request $request, int $vehicle, int $akiLog): JsonResponse
    {
        return $this->destroy($request, $this->log(AkiLog::class, $request, $vehicle, $akiLog));
    }

    /** @param class-string<BanLog|AkiLog> $model */
    private function log(string $model, Request $request, int $vehicle, int $id): BanLog|AkiLog
    {
        $unit = Vehicle::query()->where('vendor_id', $request->user()->vendor_id)->findOrFail($vehicle);
        $log = $model::query()->where('vehicle_id', $unit->id)->findOrFail($id);
        MaintenanceHistoryGuard::authorize($request, $log);

        return $log;
    }

    private function confirmPassword(Request $request): void
    {
        $request->validate(['password' => ['required', 'string', 'max:1024']], ['password.required' => 'Masukkan password akun Anda.']);
        if (! MaintenanceHistoryGuard::passwordMatches($request)) {
            throw ValidationException::withMessages(['password' => 'Password salah.']);
        }
    }

    private function update(Request $request, BanLog|AkiLog $log): JsonResponse
    {
        $data = $request->validate(['replaced_on' => ['required', 'date_format:Y-m-d', 'before_or_equal:today']],
            ['replaced_on.before_or_equal' => 'Tanggal ganti tidak boleh melebihi hari ini.']);
        $this->confirmPassword($request);
        $log->update(['created_at' => Carbon::createFromFormat('Y-m-d', $data['replaced_on'])->startOfDay()]);

        return response()->json(['message' => 'Tanggal ganti berhasil diubah.', 'data' => ['id' => $log->id, 'tanggal_ganti' => $log->created_at->toISOString()]]);
    }

    private function destroy(Request $request, BanLog|AkiLog $log): JsonResponse
    {
        $this->confirmPassword($request);
        $log->delete();

        return response()->json(['message' => 'Riwayat berhasil dihapus.']);
    }
}
