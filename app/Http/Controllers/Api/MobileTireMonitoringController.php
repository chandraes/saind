<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BanLog;
use App\Models\PosisiBan;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileTireMonitoringController extends Controller
{
    public function show(Request $request, int $vehicle): JsonResponse
    {
        $record = Vehicle::query()->leftJoin('upah_gendongs as ug', 'vehicles.id', '=', 'ug.vehicle_id')
            ->where('vehicles.vendor_id', $request->user()->vendor_id)
            ->where('vehicles.id', $vehicle)
            ->select('vehicles.id', 'vehicles.nomor_lambung', 'ug.nama_driver', 'ug.nama_pengurus')
            ->firstOrFail();

        $logs = BanLog::query()->where('vehicle_id', $record->id)
            ->orderByDesc('created_at')->orderByDesc('id')->get()
            ->unique('posisi_ban_id')->keyBy('posisi_ban_id');

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
