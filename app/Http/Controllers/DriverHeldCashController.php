<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Services\DriverHeldCash;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Kas UJ Ditahan versi baca saja (Sparepart | Ban). Driver: mutasinya sendiri
 * pada nolam yang ditugaskan. Vendor-induk & operasional: memilih nolam yang
 * tidak nonaktif (vendor-induk hanya nolam vendornya) lalu melihat seluruh
 * mutasi nolam tersebut.
 */
class DriverHeldCashController extends Controller
{
    public function __invoke(Request $request, string $fund): View|JsonResponse
    {
        $vehicle = $request->attributes->get('driverVehicle');

        return $this->ledger($request, $fund, $vehicle, new DriverHeldCash((int) $request->user()->driver_id, (int) $vehicle->id, $fund));
    }

    public function vehicle(Request $request, string $fund): View|JsonResponse
    {
        $user = $request->user();
        abort_unless($user->canViewNolamHeldCash(), 403);
        $request->validate(['vehicle_id' => ['nullable', 'integer']]);
        $vehicle = $request->filled('vehicle_id') ? $user->heldCashVehicles()->findOrFail($request->integer('vehicle_id')) : null;
        $options = $user->heldCashVehicles()->with('vendor:id,nama,nickname')->orderBy('nomor_lambung')->orderBy('id')->get(['id', 'vendor_id', 'nomor_lambung']);
        $picker = ['vehicleOptions' => $options, 'showVehicleVendor' => $user->isOperasional()];

        return $this->ledger($request, $fund, $vehicle, $vehicle ? new DriverHeldCash(null, (int) $vehicle->id, $fund) : null, $picker);
    }

    /** @param  array<string, mixed>  $extra */
    private function ledger(Request $request, string $fund, ?Vehicle $vehicle, ?DriverHeldCash $cash, array $extra = []): View|JsonResponse
    {
        $request->validate(['bulan' => ['nullable', 'integer', 'between:1,12'], 'tahun' => ['nullable', 'integer', 'between:2000,2100']]);
        $bulan = $request->integer('bulan') ?: now()->month;
        $tahun = $request->integer('tahun') ?: now()->year;
        $details = $cash?->period($bulan, $tahun)->with(['driver', 'cancellation.actor'])->orderBy('created_at')->orderBy('id')->simplePaginate(25)->withQueryString();

        $driverLedger = true;
        $offset = $details ? $details->firstItem() - 1 : 0;
        if ($request->expectsJson()) {
            abort_if($details === null, 404);

            return response()->json([
                'rows' => view('billing.uj-ditahan.ledger-rows', compact('details', 'driverLedger', 'offset'))->render(),
                'next' => $details->nextPageUrl(),
            ]);
        }

        $periodBalances = $cash?->periodBalances() ?? collect();
        $totalBalance = (int) $periodBalances->sum('saldo');
        $monthsWithBalance = $periodBalances->filter(fn ($period): bool => (int) $period->saldo !== 0);
        $activeBalanceMonths = $monthsWithBalance->where('tahun', $tahun)->pluck('bulan')->map(fn ($month): int => (int) $month)->all();
        $activePeriodCount = $monthsWithBalance->count();
        $totals = $cash?->totals($bulan, $tahun) ?? ['masuk' => 0, 'keluar' => 0, 'saldo' => 0];

        $master = (object) [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'vehicle' => $vehicle,
            'total_masuk' => $totals['masuk'],
            'total_keluar' => $totals['keluar'],
            'saldo' => $totals['saldo'],
        ];

        return view('billing.uj-ditahan.show', compact('master', 'details', 'driverLedger', 'offset', 'activeBalanceMonths', 'totalBalance', 'activePeriodCount') + $extra + [
            'fundLabel' => $fund === 'ban' ? 'Ban' : 'Sparepart',
        ]);
    }
}
