<?php

namespace App\Http\Middleware;

use App\Models\Vehicle;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVendorOperationalVehicle
{
    public function handle(Request $request, Closure $next): Response
    {
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

        return $next($request);
    }
}
