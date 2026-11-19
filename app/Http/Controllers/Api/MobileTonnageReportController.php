<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\MineTonnageReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MobileTonnageReportController extends Controller
{
    public function customers(): JsonResponse
    {
        $customers = Customer::query()->where('status', 1)->orderBy('nama')->get(['id', 'nama', 'singkatan']);

        return response()->json(['data' => $customers->map(fn (Customer $customer): array => [
            'id' => (int) $customer->id,
            'name' => $customer->nama,
            'short' => $customer->singkatan,
        ])]);
    }

    public function show(Request $request, Customer $customer, MineTonnageReport $report): JsonResponse
    {
        abort_unless((int) $customer->status === 1, 404);
        $filters = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:1900,2100'],
            'route_id' => ['sometimes', 'integer', 'min:1'],
        ]);
        $month = (int) $filters['month'];
        $year = (int) $filters['year'];
        $routeId = isset($filters['route_id']) ? (int) $filters['route_id'] : null;
        ['statistics' => $statistics, 'dbRute' => $routes, 'date' => $days] = $report->forMonth($customer, $month, $year);
        if ($routeId !== null && ! $routes->contains('id', $routeId)) {
            throw ValidationException::withMessages(['route_id' => 'Rute tidak tersedia untuk pelanggan dan periode ini.']);
        }

        $rows = collect(range(1, $days))->map(function (int $day) use ($statistics, $routeId): array {
            $daily = $statistics[$day] ?? [];
            $selected = collect($routeId === null ? $daily : [$daily[$routeId]]);

            return ['day' => $day, 'trips' => $selected->sum('data.ritase'),
                'loaded_tons' => $selected->sum('data.tonase_muat'),
                'unloaded_tons' => $selected->sum('data.tonase_bongkar')];
        });

        return response()->json(['data' => $rows, 'meta' => [
            'routes' => $routes->sortBy('nama')->map(fn ($route): array => ['id' => (int) $route->id, 'name' => $route->nama])->values(),
            'totals' => [
                'trips' => $rows->sum('trips'),
                'loaded_tons' => $rows->sum('loaded_tons'),
                'unloaded_tons' => $rows->sum('unloaded_tons'),
            ],
        ]]);
    }
}
