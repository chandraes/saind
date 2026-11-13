<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Services\DriverHeldCash;
use App\Support\MobileVendorScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Kas UJ ditahan di aplikasi (Sparepart | Ban), baca saja. Driver: mutasinya
 * sendiri pada nolam yang ditugaskan. Vendor-induk & operasional: seluruh
 * mutasi satu nolam yang tidak nonaktif (vendor-induk hanya nolam vendornya).
 */
class MobileDriverHeldCashController extends Controller
{
    public function __invoke(Request $request, string $fund): JsonResponse
    {
        abort_unless($request->user()->isDriver(), 403);
        $vehicle = $request->attributes->get('driverVehicle');

        return $this->respond($request, new DriverHeldCash((int) $request->user()->driver_id, (int) MobileVendorScope::vehicleId($request), $fund), $fund, $vehicle);
    }

    public function vehicle(Request $request, int $vehicle, string $fund): JsonResponse
    {
        abort_unless($request->user()->canViewNolamHeldCash(), 403);
        $model = MobileVendorScope::apply($request->user()->heldCashVehicles(), $request)->findOrFail($vehicle);

        return $this->respond($request, new DriverHeldCash(null, (int) $model->id, $fund), $fund, $model);
    }

    private function respond(Request $request, DriverHeldCash $cash, string $fund, Vehicle $vehicle): JsonResponse
    {
        $filters = $request->validate(['month' => ['nullable', 'integer', 'between:1,12'], 'year' => ['nullable', 'integer', 'between:2000,2100'], 'page' => ['sometimes', 'integer', 'min:1']]);
        $month = (int) ($filters['month'] ?? now()->month);
        $year = (int) ($filters['year'] ?? now()->year);
        $page = $cash->period($month, $year)->with(['cancellation', 'driver'])->orderBy('created_at')->orderBy('id')->simplePaginate(25);
        $balances = $cash->periodBalances();
        $totals = $cash->totals($month, $year);

        return response()->json([
            'data' => $page->getCollection()->map(fn ($detail): array => [
                'id' => (int) $detail->id,
                'date' => $detail->created_at?->toDateString(),
                'description' => (string) $detail->keterangan,
                'type' => $detail->jenis === 'keluar' ? 'out' : 'in',
                'amount' => (int) $detail->nominal,
                'reversal' => $detail->payout_origin === 'payout_reversal',
                'cancelled' => $detail->cancellation !== null,
                'cancel_reason' => $detail->cancellation?->reason,
                'driver' => $detail->driver?->nama,
                'account' => $detail->bank && $detail->no_rekening ? ['bank' => $detail->bank, 'number' => $detail->no_rekening, 'name' => $detail->nama_rekening] : null,
            ])->values(),
            'meta' => [
                'fund' => $fund, 'month' => $month, 'year' => $year,
                'vehicle_id' => (int) $vehicle->id,
                'vehicle_number' => (string) $vehicle->nomor_lambung,
                'current_page' => $page->currentPage(), 'has_more' => $page->hasMorePages(),
                'total_in' => $totals['masuk'], 'total_out' => $totals['keluar'], 'balance' => $totals['saldo'],
                // Saldo seluruh periode & bulan bersaldo (penanda ❗ seperti web).
                'total_balance' => (int) $balances->sum('saldo'),
                'months_with_balance' => $balances->filter(fn ($period): bool => (int) $period->saldo !== 0)
                    ->map(fn ($period): array => ['year' => (int) $period->tahun, 'month' => (int) $period->bulan, 'balance' => (int) $period->saldo])->values(),
            ],
        ]);
    }
}
