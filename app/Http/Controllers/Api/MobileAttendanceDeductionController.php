<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HrdAttendanceDeductions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Potongan Absensi di aplikasi untuk role HRD (baca saja), sama dengan menu web. */
class MobileAttendanceDeductionController extends Controller
{
    public function __construct(private HrdAttendanceDeductions $deductions) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'bulan' => ['nullable', 'integer', 'between:1,12'], 'tahun' => ['nullable', 'integer', 'between:2000,2100'],
            'search' => ['nullable', 'string', 'max:100'], 'deductions_only' => ['sometimes', 'boolean'], 'page' => ['sometimes', 'integer', 'min:1'],
        ]);
        $period = $this->deductions->resolve(isset($filters['bulan']) ? (int) $filters['bulan'] : null, isset($filters['tahun']) ? (int) $filters['tahun'] : null);
        $rows = $this->deductions->rows($period, $filters['search'] ?? '', $request->boolean('deductions_only'));
        $perPage = HrdAttendanceDeductions::PER_PAGE;
        $page = (int) ($filters['page'] ?? 1);
        $offset = ($page - 1) * $perPage;

        return response()->json([
            'data' => $rows->slice($offset, $perPage)->values()->map(fn (array $row, int $index): array => ['number' => $offset + $index + 1] + $row),
            'meta' => [
                'current_page' => $page, 'last_page' => max(1, (int) ceil($rows->count() / $perPage)), 'total' => $rows->count(),
                'total_deduction' => (int) $rows->sum('potongan_absensi'), 'deducted_count' => $rows->where('potongan_absensi', '>', 0)->count(),
                'period' => $period, 'periods' => $this->deductions->periods(), 'status' => $this->deductions->status($period),
            ],
        ]);
    }

    public function show(Request $request, int $key): JsonResponse
    {
        $filters = $request->validate(['bulan' => ['required', 'integer', 'between:1,12'], 'tahun' => ['required', 'integer', 'between:2000,2100']]);
        $period = $this->deductions->resolve((int) $filters['bulan'], (int) $filters['tahun']);

        return response()->json(['data' => $this->deductions->detail($period, $key) + ['period' => $period]]);
    }
}
