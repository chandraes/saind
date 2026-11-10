<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BanLog;
use App\Models\PosisiBan;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileTireHistoryController extends Controller
{
    private function vehicle(Request $request, int $id): Vehicle
    {
        return Vehicle::query()->where('vendor_id', $request->user()->vendor_id)->findOrFail($id);
    }

    public function history(Request $request, int $vehicle, int $position): JsonResponse
    {
        $filters = $request->validate(['page' => ['sometimes', 'integer', 'min:1'], 'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'], 'search' => ['nullable', 'string', 'max:100']]);
        $record = $this->vehicle($request, $vehicle);
        $tirePosition = PosisiBan::query()->findOrFail($position);
        $query = BanLog::query()->where('vehicle_id', $record->id)->where('posisi_ban_id', $tirePosition->id);
        $search = trim($filters['search'] ?? '');
        if ($search !== '') {
            $query->where(fn ($q) => $q->where('merk', 'like', '%'.$search.'%')->orWhere('no_seri', 'like', '%'.$search.'%'));
        }
        $page = $query->orderByDesc('created_at')->orderByDesc('id')->simplePaginate($filters['per_page'] ?? 20);
        $data = $page->getCollection()->map(fn ($log) => [
            'id' => $log->id, 'vehicle_id' => $log->vehicle_id, 'posisi_ban_id' => $log->posisi_ban_id,
            'merk' => $log->merk, 'no_seri' => $log->no_seri, 'kondisi' => $log->kondisi,
            'ritase' => $log->ritase, 'tanggal_ganti' => $log->created_at?->toISOString(),
        ]);

        return response()->json(['data' => $data, 'vehicle' => ['id' => $record->id, 'nomor_lambung' => $record->nomor_lambung],
            'position' => ['id' => $tirePosition->id, 'nama' => $tirePosition->nama],
            'meta' => ['current_page' => $page->currentPage(), 'per_page' => $page->perPage(), 'has_more' => $page->hasMorePages(), 'next_page' => $page->hasMorePages() ? $page->currentPage() + 1 : null]]);
    }

    public function trips(Request $request, int $vehicle, int $banLog): JsonResponse
    {
        $filters = $request->validate(['page' => ['sometimes', 'integer', 'min:1'], 'per_page' => ['sometimes', 'integer', 'min:1', 'max:100']]);
        $record = $this->vehicle($request, $vehicle);
        $log = BanLog::query()->where('vehicle_id', $record->id)->findOrFail($banLog);
        $page = DB::table('ban_log_transaksis as pivot')
            ->join('transaksis as t', 'pivot.transaksi_id', '=', 't.id')
            ->join('kas_uang_jalans as kuj', 't.kas_uang_jalan_id', '=', 'kuj.id')
            ->join('rutes as r', 'kuj.rute_id', '=', 'r.id')
            ->where('pivot.ban_log_id', $log->id)
            ->where('kuj.vendor_id', $request->user()->vendor_id)->where('kuj.vehicle_id', $record->id)
            ->orderByDesc('kuj.tanggal')->orderByDesc('t.id')->orderByDesc('pivot.id')
            ->select('pivot.id', 't.id as transaksi_id', 'kuj.tanggal', 'kuj.nomor_uang_jalan', 'r.nama as rute', 'r.jarak', 'pivot.nilai_ritase')
            ->simplePaginate($filters['per_page'] ?? 20);

        return response()->json(['data' => $page->items(),
            'tire' => ['id' => $log->id, 'vehicle_id' => $log->vehicle_id, 'posisi_ban_id' => $log->posisi_ban_id, 'merk' => $log->merk, 'no_seri' => $log->no_seri],
            'meta' => ['current_page' => $page->currentPage(), 'per_page' => $page->perPage(), 'has_more' => $page->hasMorePages(), 'next_page' => $page->hasMorePages() ? $page->currentPage() + 1 : null]]);
    }
}
