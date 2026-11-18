<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AkiLog;
use App\Models\BanLog;
use App\Models\FilterOliLog;
use App\Services\VendorMaintenanceInput;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Riwayat ban, aki, dan filter & oli dari aplikasi mobile. Catat baru: akun vendor.
 * Ubah tanggal/hapus: akun vendor (nolam sendiri, password akun) serta Admin & SU
 * (semua nolam, password konfirmasi). Aturan isian, kepemilikan nolam, dan password ada di
 * VendorMaintenanceInput (dipakai bersama halaman web vendor).
 */
class MobileMaintenanceHistoryController extends Controller
{
    public function __construct(private VendorMaintenanceInput $input) {}

    public function storeTire(Request $request, int $vehicle): JsonResponse
    {
        return $this->created('Ban berhasil dicatat.', $this->input->storeTire($request, $this->input->vehicle($request, $vehicle)));
    }

    public function storeBattery(Request $request, int $vehicle): JsonResponse
    {
        return $this->created('Aki berhasil dicatat.', $this->input->storeBattery($request, $this->input->vehicle($request, $vehicle)));
    }

    public function storeFilterOil(Request $request, int $vehicle): JsonResponse
    {
        return $this->created('Filter & oli berhasil dicatat.', $this->input->storeFilterOil($request, $this->input->vehicle($request, $vehicle)));
    }

    public function updateTire(Request $request, int $vehicle, int $banLog): JsonResponse
    {
        return $this->reschedule($request, $this->input->log(BanLog::class, $request, $vehicle, $banLog));
    }

    public function destroyTire(Request $request, int $vehicle, int $banLog): JsonResponse
    {
        return $this->destroy($request, $this->input->log(BanLog::class, $request, $vehicle, $banLog));
    }

    public function updateBattery(Request $request, int $vehicle, int $akiLog): JsonResponse
    {
        return $this->reschedule($request, $this->input->log(AkiLog::class, $request, $vehicle, $akiLog));
    }

    public function destroyBattery(Request $request, int $vehicle, int $akiLog): JsonResponse
    {
        return $this->destroy($request, $this->input->log(AkiLog::class, $request, $vehicle, $akiLog));
    }

    public function updateFilterOil(Request $request, int $vehicle, int $log): JsonResponse
    {
        return $this->reschedule($request, $this->input->log(FilterOliLog::class, $request, $vehicle, $log));
    }

    public function destroyFilterOil(Request $request, int $vehicle, int $log): JsonResponse
    {
        return $this->destroy($request, $this->input->log(FilterOliLog::class, $request, $vehicle, $log));
    }

    private function created(string $message, BanLog|AkiLog|FilterOliLog $log): JsonResponse
    {
        return response()->json(['message' => $message, 'data' => ['id' => $log->id, 'tanggal_ganti' => $log->created_at->toISOString()]], 201);
    }

    private function reschedule(Request $request, BanLog|AkiLog|FilterOliLog $log): JsonResponse
    {
        $log = $this->input->reschedule($request, $log);

        return response()->json(['message' => 'Tanggal ganti berhasil diubah.', 'data' => ['id' => $log->id, 'tanggal_ganti' => $log->created_at->toISOString()]]);
    }

    private function destroy(Request $request, BanLog|AkiLog|FilterOliLog $log): JsonResponse
    {
        $this->input->destroy($request, $log);

        return response()->json(['message' => 'Riwayat berhasil dihapus.']);
    }
}
