<?php

namespace App\Http\Controllers;

use App\Services\HrdAttendanceDeductions;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/** Menu Potongan Absensi role HRD (baca saja). */
class HrdAttendanceDeductionController extends Controller
{
    public function __construct(private HrdAttendanceDeductions $deductions) {}

    public function index(Request $request): View|JsonResponse
    {
        $filters = $request->validate([
            'bulan' => ['nullable', 'integer', 'between:1,12'], 'tahun' => ['nullable', 'integer', 'between:2000,2100'],
            'search' => ['nullable', 'string', 'max:100'], 'potongan' => ['sometimes', 'boolean'], 'page' => ['sometimes', 'integer', 'min:1'],
        ]);
        $period = $this->period($filters);
        $search = trim($filters['search'] ?? '');
        $rows = $this->deductions->rows($period, $search, $request->boolean('potongan'));
        $page = LengthAwarePaginator::resolveCurrentPage();
        $paginator = new LengthAwarePaginator($rows->forPage($page, HrdAttendanceDeductions::PER_PAGE)->values(), $rows->count(), HrdAttendanceDeductions::PER_PAGE, $page,
            ['path' => $request->url(), 'query' => $request->except('page')]);

        if ($request->ajax()) {
            // Infinite scroll: hanya baris halaman berikutnya.
            return response()->json(['html' => $paginator->isEmpty() ? '' : view('hrd.attendance-deduction-rows', ['rows' => $paginator, 'period' => $period, 'filtered' => true])->render(),
                'next' => $paginator->nextPageUrl()]);
        }

        return view('hrd.attendance-deductions', [
            'period' => $period, 'periods' => $this->deductions->periods(), 'status' => $this->deductions->status($period),
            'rows' => $paginator, 'search' => $search, 'deductionsOnly' => $request->boolean('potongan'),
            'total' => $rows->sum('potongan_absensi'), 'deductedCount' => $rows->where('potongan_absensi', '>', 0)->count(),
        ]);
    }

    public function show(Request $request, int $key): JsonResponse
    {
        $period = $this->period($this->periodFilters($request));
        $row = $this->deductions->detail($period, $key);

        return response()->json(['data' => $row + ['period' => $period,
            'pdf_url' => route('hrd.attendance-deductions.pdf', ['key' => $key, 'bulan' => $period['month'], 'tahun' => $period['year']])]]);
    }

    public function pdf(Request $request, int $key): Response
    {
        $period = $this->period($this->periodFilters($request));
        $row = $this->deductions->detail($period, $key);

        return Pdf::loadView('billing.gaji.attendance-pdf', ['name' => $row['name'], 'period' => Carbon::create($period['year'], $period['month'], 1),
            'detail' => $row['detail'], 'saved' => $period['final']])
            ->setPaper('a4', 'landscape')->download('Rincian_Absensi_'.sprintf('%04d_%02d', $period['year'], $period['month']).'_'.$key.'.pdf');
    }

    /** @return array<string, mixed> */
    private function periodFilters(Request $request): array
    {
        return $request->validate(['bulan' => ['required', 'integer', 'between:1,12'], 'tahun' => ['required', 'integer', 'between:2000,2100']]);
    }

    /** @return array{month: int, year: int, final: bool, label: string} */
    private function period(array $filters): array
    {
        return $this->deductions->resolve(isset($filters['bulan']) ? (int) $filters['bulan'] : null, isset($filters['tahun']) ? (int) $filters['tahun'] : null);
    }
}
