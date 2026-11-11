<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UpahGendong;
use App\Models\Vehicle;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Statistik Upah Gendong versi mobile: rekap bulanan satu kendaraan milik
 * vendor akun, dengan rumus yang sama dengan halaman web.
 */
class MobileUpahGendongController extends Controller
{
    private const PER_PAGE = 25;

    public function show(Request $request, int $vehicle): JsonResponse
    {
        $filters = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12', 'required_with:year'],
            'year' => ['nullable', 'integer', 'between:2000,2100', 'required_with:month'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);
        $vendorId = (int) $request->user()->vendor_id;
        abort_if($vendorId <= 0, 403);
        $unit = Vehicle::whereKey($vehicle)->where('vendor_id', $vendorId)->firstOrFail(['id', 'nomor_lambung', 'nopol', 'vendor_id']);
        $rate = UpahGendong::where('vehicle_id', $unit->id)->first();
        if (! $rate) {
            // Bukan error: kendaraan memang belum diberi tarif oleh admin.
            return response()->json(['data' => [], 'meta' => [
                'configured' => false, 'message' => 'Data upah gendong kendaraan ini belum diatur admin.',
                'vehicle' => ['id' => $unit->id, 'nomor_lambung' => $unit->nomor_lambung, 'nopol' => $unit->nopol],
                'current_page' => 1, 'last_page' => 1, 'total' => 0,
            ]]);
        }

        $start = CarbonImmutable::create((int) ($filters['year'] ?? now()->year), (int) ($filters['month'] ?? now()->month), 1);
        $trips = DB::table('transaksis as t')
            ->join('kas_uang_jalans as kuj', 'kuj.id', '=', 't.kas_uang_jalan_id')
            ->join('vehicles as v', 'v.id', '=', 'kuj.vehicle_id')
            ->join('rutes as r', 'r.id', '=', 'kuj.rute_id')
            ->where('t.void', 0)
            ->where('kuj.vehicle_id', $unit->id)
            ->whereColumn('kuj.vendor_id', 'v.vendor_id')
            ->whereBetween('kuj.tanggal', [$start->toDateString(), $start->endOfMonth()->toDateString()]);

        $longKm = UpahGendong::LONG_ROUTE_KM;
        $totals = (clone $trips)->selectRaw('COUNT(*) as trips, COALESCE(SUM(r.jarak), 0) as distance,
            COALESCE(SUM(CASE WHEN r.jarak < ? THEN t.timbangan_bongkar ELSE 0 END), 0) as short_tonnage,
            COALESCE(SUM(CASE WHEN r.jarak >= ? THEN t.timbangan_bongkar ELSE 0 END), 0) as long_tonnage,
            COALESCE(SUM(CASE WHEN r.jarak >= ? AND t.timbangan_bongkar > ? THEN t.timbangan_bongkar - ? ELSE 0 END), 0) as excess',
            [$longKm, $longKm, $longKm, $rate->tonase_min, $rate->tonase_min])->first();
        $page = (int) ($filters['page'] ?? 1);
        $rows = (clone $trips)->orderBy('kuj.tanggal')->orderBy('t.id')
            ->offset(($page - 1) * self::PER_PAGE)->limit(self::PER_PAGE)
            ->get(['t.id', 'kuj.tanggal', 'r.nama as rute', 'r.jarak', 't.timbangan_bongkar']);
        $firstDate = DB::table('kas_uang_jalans')->where('vehicle_id', $unit->id)->where('vendor_id', $vendorId)->min('tanggal');
        $firstYear = $firstDate ? min(CarbonImmutable::parse($firstDate)->year, $start->year) : $start->year;
        $excess = round((float) $totals->excess, 3);

        return response()->json([
            'data' => $rows->map(function ($row) use ($rate): array {
                $distance = (float) $row->jarak;
                $tonnage = (float) ($row->timbangan_bongkar ?? 0);
                $long = UpahGendong::isLongRoute($distance);
                $excess = round($rate->excessTonnage($distance, $tonnage), 3);

                return [
                    'id' => $row->id,
                    'date' => CarbonImmutable::parse($row->tanggal)->toDateString(),
                    'route' => $row->rute,
                    'distance_km' => $distance,
                    'short_tonnage' => $long ? 0.0 : $tonnage,
                    'long_tonnage' => $long ? $tonnage : 0.0,
                    'excess_tonnage' => $excess,
                    'amount' => (int) round($excess * $rate->nominal),
                ];
            })->values(),
            'meta' => [
                'configured' => true,
                'current_page' => $page,
                'last_page' => max(1, (int) ceil($totals->trips / self::PER_PAGE)),
                'total' => (int) $totals->trips,
                'month' => $start->month,
                'year' => $start->year,
                'years' => range(max($start->year, (int) now()->year), $firstYear),
                'long_route_min_km' => $longKm,
                'vehicle' => ['id' => $unit->id, 'nomor_lambung' => $unit->nomor_lambung, 'nopol' => $unit->nopol],
                'rate' => [
                    'nominal' => (int) $rate->nominal,
                    'tonase_min' => (float) $rate->tonase_min,
                    'driver_name' => $rate->nama_driver,
                    'driver_joined_on' => $rate->tanggal_masuk_driver,
                    'manager_name' => $rate->nama_pengurus,
                    'manager_joined_on' => $rate->tanggal_masuk_pengurus,
                    'bank' => $rate->bank,
                    'account_name' => $rate->nama_rek,
                    'account_number' => $rate->no_rek,
                ],
                'totals' => [
                    'distance_km' => round((float) $totals->distance, 1),
                    'short_tonnage' => round((float) $totals->short_tonnage, 3),
                    'long_tonnage' => round((float) $totals->long_tonnage, 3),
                    'excess_tonnage' => $excess,
                    'amount' => (int) round($excess * $rate->nominal),
                ],
            ],
        ]);
    }
}
