<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BanLog;
use App\Models\PosisiBan;
use App\Models\Vehicle;
use App\Support\MobileVendorScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileTireMonitoringController extends Controller
{
    public function show(Request $request, int $vehicle): JsonResponse
    {
        $record = Vehicle::query()->leftJoin('upah_gendongs as ug', 'vehicles.id', '=', 'ug.vehicle_id')
            ->where(fn ($query) => MobileVendorScope::apply($query, $request, 'vehicles.vendor_id'))
            ->where('vehicles.id', $vehicle)
            ->select('vehicles.id', 'vehicles.nomor_lambung', 'ug.nama_driver', 'ug.nama_pengurus')
            ->firstOrFail();

        $logs = BanLog::query()->where('vehicle_id', $record->id)
            ->where('id', function ($latest): void {
                $latest->from('ban_logs as latest')->select('latest.id')
                    ->whereColumn('latest.vehicle_id', 'ban_logs.vehicle_id')
                    ->whereColumn('latest.posisi_ban_id', 'ban_logs.posisi_ban_id')
                    ->orderByDesc('latest.created_at')->orderByDesc('latest.id')->limit(1);
            })
            ->select('id', 'posisi_ban_id', 'merk', 'no_seri', 'kondisi', 'ritase', 'created_at')
            ->get()->keyBy('posisi_ban_id');

        $positions = PosisiBan::query()->orderBy('id')->get()->values()->map(function ($position, $index) use ($logs) {
            $log = $logs->get($position->id);

            return [
                'id' => $position->id,
                'nama' => $position->nama,
                'urutan' => $index + 1,
                'current_log' => $log ? [
                    'id' => $log->id,
                    'merk' => $log->merk,
                    'no_seri' => $log->no_seri,
                    'kondisi' => $log->kondisi,
                    'ritase' => $log->ritase,
                    'tanggal_ganti' => $log->created_at?->toISOString(),
                ] : null,
            ];
        });

        return response()->json(['data' => [
            'vehicle' => ['id' => $record->id, 'nomor_lambung' => $record->nomor_lambung,
                'nama_driver' => $record->nama_driver, 'pengurus' => $record->nama_pengurus],
            'positions' => $positions,
        ]]);
    }
}
