<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MobileUnitPerformanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d'],
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $start = CarbonImmutable::parse($filters['start_date'] ?? now()->startOfMonth()->toDateString());
        $end = CarbonImmutable::parse($filters['end_date'] ?? now()->toDateString());
        if ($end->lessThan($start) || $start->diffInDays($end) > 31) {
            throw ValidationException::withMessages(['end_date' => 'Rentang tanggal harus berurutan dan maksimal 31 hari.']);
        }

        $vendorId = (int) $request->user()->vendor_id;
        abort_if($vendorId <= 0, 403);
        $search = trim($filters['search'] ?? '');

        $vehicles = DB::table('vehicles')
            ->where('vendor_id', $vendorId)
            ->where('status', '!=', 'nonaktif')
            ->when($search !== '', fn ($query) => $query->where('nomor_lambung', 'like', '%'.$search.'%'))
            ->orderBy('nomor_lambung')->orderBy('id')
            ->select('id', 'nomor_lambung', 'nopol')
            ->paginate(20)->withQueryString();

        $journeys = DB::table('transaksis as t')
            ->join('kas_uang_jalans as kuj', 'kuj.id', '=', 't.kas_uang_jalan_id')
            ->join('vehicles as v', 'v.id', '=', 'kuj.vehicle_id')
            ->join('rutes as r', 'r.id', '=', 'kuj.rute_id')
            ->where('v.vendor_id', $vendorId)
            ->whereIn('v.id', $vehicles->pluck('id'))
            ->where('t.void', 0)
            ->whereBetween('kuj.tanggal', [$start->toDateString(), $end->toDateString()])
            ->orderBy('kuj.tanggal')->orderBy('t.id')
            ->select('v.id as vehicle_id', 'kuj.tanggal', 'r.nama as rute', 'r.jarak', 't.timbangan_bongkar')
            ->get()->groupBy('vehicle_id');

        $data = $vehicles->getCollection()->map(function ($vehicle) use ($journeys) {
            $items = $journeys->get($vehicle->id, collect());

            return [
                'id' => $vehicle->id,
                'vehicle_number' => $vehicle->nomor_lambung,
                'plate_number' => $vehicle->nopol,
                'long_route_count' => $items->filter(fn ($item) => $item->jarak > 50)->count(),
                'short_route_count' => $items->filter(fn ($item) => $item->jarak > 0 && $item->jarak <= 50)->count(),
                'total_tonnage' => $items->sum(fn ($item) => (float) ($item->timbangan_bongkar ?? 0)),
                'journeys' => $items->map(fn ($item) => [
                    'date' => $item->tanggal,
                    'route' => $item->rute,
                    'tonnage' => $item->timbangan_bongkar ?? 0,
                ])->values(),
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $vehicles->currentPage(),
                'last_page' => $vehicles->lastPage(),
                'total' => $vehicles->total(),
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
            ],
        ]);
    }
}
