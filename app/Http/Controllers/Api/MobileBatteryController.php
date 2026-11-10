<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AkiLog;
use App\Models\PosisiAki;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MobileBatteryController extends Controller
{
    public function show(Request $request, int $vehicle): JsonResponse
    {
        $record = Vehicle::query()->leftJoin('upah_gendongs as ug', 'vehicles.id', '=', 'ug.vehicle_id')
            ->where('vehicles.vendor_id', $request->user()->vendor_id)
            ->where('vehicles.id', $vehicle)
            ->select('vehicles.id', 'vehicles.nomor_lambung', 'ug.nama_driver', 'ug.nama_pengurus')
            ->firstOrFail();
        $logs = AkiLog::query()->where('vehicle_id', $record->id)
            ->orderByDesc('created_at')->orderByDesc('id')->get()
            ->unique('posisi_aki_id')->keyBy('posisi_aki_id');
        $today = today();
        $positions = PosisiAki::query()->orderBy('id')->get()->values()->map(function ($position, $index) use ($logs, $today) {
            $log = $logs->get($position->id);

            return [
                'id' => $position->id,
                'nama' => $position->nama,
                'urutan' => $index + 1,
                'current_log' => $log ? [
                    'id' => $log->id,
                    'merk' => $log->merk,
                    'kondisi' => (int) $log->kondisi,
                    'tanggal_ganti' => $log->created_at?->toISOString(),
                    'umur_hari' => max(0, (int) $log->created_at->copy()->startOfDay()->diffInDays($today, false)),
                ] : null,
            ];
        });

        return response()->json(['data' => [
            'vehicle' => ['id' => $record->id, 'nomor_lambung' => $record->nomor_lambung,
                'nama_driver' => $record->nama_driver, 'pengurus' => $record->nama_pengurus],
            'positions' => $positions,
        ]]);
    }

    public function history(Request $request, int $vehicle, int $position): JsonResponse
    {
        $filters = $request->validate(['page' => ['sometimes', 'integer', 'min:1'], 'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'], 'search' => ['nullable', 'string', 'max:100']]);
        $record = Vehicle::query()->where('vendor_id', $request->user()->vendor_id)->findOrFail($vehicle);
        $batteryPosition = PosisiAki::query()->findOrFail($position);
        $nextReplacement = DB::table('aki_logs as next_log')
            ->select('next_log.created_at')
            ->whereColumn('next_log.vehicle_id', 'aki_logs.vehicle_id')
            ->whereColumn('next_log.posisi_aki_id', 'aki_logs.posisi_aki_id')
            ->where(function ($query): void {
                $query->whereColumn('next_log.created_at', '>', 'aki_logs.created_at')
                    ->orWhere(function ($query): void {
                        $query->whereColumn('next_log.created_at', 'aki_logs.created_at')
                            ->whereColumn('next_log.id', '>', 'aki_logs.id');
                    });
            })
            ->orderBy('next_log.created_at')->orderBy('next_log.id')->limit(1);
        $query = AkiLog::query()->where('vehicle_id', $record->id)->where('posisi_aki_id', $batteryPosition->id);
        $search = trim($filters['search'] ?? '');
        if ($search !== '') {
            $query->where('merk', 'like', '%'.$search.'%');
        }
        $page = $query->select('aki_logs.*')->addSelect(['tanggal_selesai' => $nextReplacement])
            ->orderByDesc('created_at')->orderByDesc('id')->simplePaginate($filters['per_page'] ?? 20);
        $today = today();
        $data = $page->getCollection()->map(function ($log) use ($today) {
            $end = $log->tanggal_selesai ? Carbon::parse($log->tanggal_selesai) : $today;

            return [
                'id' => $log->id, 'merk' => $log->merk, 'kondisi' => (int) $log->kondisi,
                'tanggal_ganti' => $log->created_at?->toISOString(),
                'tanggal_selesai' => $log->tanggal_selesai ? Carbon::parse($log->tanggal_selesai)->toISOString() : null,
                'umur_hari' => max(0, (int) $log->created_at->copy()->startOfDay()->diffInDays($end->copy()->startOfDay(), false)),
                'masih_digunakan' => $log->tanggal_selesai === null,
            ];
        });

        return response()->json(['data' => $data, 'vehicle' => ['id' => $record->id, 'nomor_lambung' => $record->nomor_lambung],
            'position' => ['id' => $batteryPosition->id, 'nama' => $batteryPosition->nama],
            'meta' => ['current_page' => $page->currentPage(), 'per_page' => $page->perPage(), 'has_more' => $page->hasMorePages(), 'next_page' => $page->hasMorePages() ? $page->currentPage() + 1 : null]]);
    }
}
