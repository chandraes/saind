<?php

namespace App\Http\Middleware;

use App\Models\BanLog;
use App\Models\FilterOliLog;
use App\Models\Vehicle;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVendorOperationalVehicle
{
    public function handle(Request $request, Closure $next, string $context = 'vehicle'): Response
    {
        if ($request->user()->isDriver()) {
            return $this->driver($request, $next);
        }
        if ($request->user()->isOperasional()) {
            // Operasional melihat seluruh vendor, tetapi hanya membaca.
            abort_unless($request->isMethod('GET') || $request->isMethod('HEAD'), 403);

            return $next($request);
        }
        if ($context === 'statistics' && ! $request->user()->isVendorAccount()) {
            return $next($request);
        }
        // Statistik & maintenance: hanya akun yang boleh mengelola histori (vendor) yang bisa mengubah;
        // vendor-operational dan vendor-induk hanya melihat.
        if (in_array($context, ['statistics', 'maintenance'], true)) {
            abort_if(! $request->user()->canManageMaintenanceHistory() && ! $request->isMethod('GET') && ! $request->isMethod('HEAD'), 403);
        }
        $vendorId = $request->user()->vendor_id;
        abort_unless($vendorId && $request->user()->vendor()->exists(), 403, 'Akun belum terhubung dengan vendor.');
        $vehicleIds = [$request->route('vehicle'), $request->input('vehicle_id'), $request->input('vehicle')];
        foreach ($vehicleIds as $vehicleId) {
            if ($vehicleId === null) {
                continue;
            }
            abort_unless(is_scalar($vehicleId) && ctype_digit((string) $vehicleId), 404);
            abort_unless(Vehicle::whereKey($vehicleId)->where('vendor_id', $vendorId)->exists(), 404);
        }

        if ($context === 'statistics') {
            foreach (['banLogId' => BanLog::class, 'log' => FilterOliLog::class] as $parameter => $model) {
                if ($request->route($parameter) !== null) {
                    $log = $model::findOrFail($request->route($parameter));
                    abort_unless(Vehicle::whereKey($log->vehicle_id)->where('vendor_id', $vendorId)->exists(), 404);
                }
            }
            $request->merge(['vendor' => $vendorId]);
        }

        return $next($request);
    }

    /**
     * Driver: baca saja dan dikunci ke nolam yang sedang ditugaskan. Nolam itu
     * selalu menjadi vehicle_id sehingga tidak ada halaman pemilih nolam.
     */
    private function driver(Request $request, Closure $next): Response
    {
        abort_unless($request->isMethodSafe(), 403);
        $vehicle = $request->attributes->get('driverVehicle') ?? $request->user()->activeDriverVehicle();
        abort_if($vehicle === null, 403);
        foreach ([$request->route('vehicle'), $request->input('vehicle_id'), $request->input('vehicle')] as $vehicleId) {
            abort_unless($vehicleId === null || (is_scalar($vehicleId) && (string) $vehicleId === (string) $vehicle->getKey()), 404);
        }
        foreach (['banLogId' => BanLog::class, 'log' => FilterOliLog::class] as $parameter => $model) {
            if ($request->route($parameter) !== null) {
                abort_unless((int) $model::findOrFail($request->route($parameter))->vehicle_id === $vehicle->getKey(), 404);
            }
        }
        $request->merge(['vehicle_id' => $vehicle->getKey()]);

        return $next($request);
    }
}
