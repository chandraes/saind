<?php

namespace App\Http\Controllers;

use App\Services\DriverHeldCash;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverHeldCashController extends Controller
{
    public function __invoke(Request $request, string $fund): View|JsonResponse
    {
        $request->validate(['bulan' => ['nullable', 'integer', 'between:1,12'], 'tahun' => ['nullable', 'integer', 'between:2000,2100']]);
        $bulan = $request->integer('bulan') ?: now()->month;
        $tahun = $request->integer('tahun') ?: now()->year;
        $vehicle = $request->attributes->get('driverVehicle');
        $cash = new DriverHeldCash((int) $request->user()->driver_id, (int) $vehicle->id, $fund);
        $details = $cash->period($bulan, $tahun)->with(['driver', 'cancellation.actor'])->orderBy('created_at')->orderBy('id')->simplePaginate(25)->withQueryString();

        $driverLedger = true;
        $offset = $details->firstItem() - 1;
        if ($request->expectsJson()) {
            return response()->json([
                'rows' => view('billing.uj-ditahan.ledger-rows', compact('details', 'driverLedger', 'offset'))->render(),
                'next' => $details->nextPageUrl(),
            ]);
        }

        $periodBalances = $cash->periodBalances();
        $totalBalance = (int) $periodBalances->sum('saldo');
        $monthsWithBalance = $periodBalances->filter(fn ($period): bool => (int) $period->saldo !== 0);
        $activeBalanceMonths = $monthsWithBalance->where('tahun', $tahun)->pluck('bulan')->map(fn ($month): int => (int) $month)->all();
        $activePeriodCount = $monthsWithBalance->count();
        $totals = $cash->totals($bulan, $tahun);

        $master = (object) [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'vehicle' => $vehicle,
            'total_masuk' => $totals['masuk'],
            'total_keluar' => $totals['keluar'],
            'saldo' => $totals['saldo'],
        ];

        return view('billing.uj-ditahan.show', compact('master', 'details', 'driverLedger', 'offset', 'activeBalanceMonths', 'totalBalance', 'activePeriodCount') + [
            'fundLabel' => $fund === 'ban' ? 'Ban' : 'Sparepart',
        ]);
    }
}
