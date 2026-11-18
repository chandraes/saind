<?php

namespace App\Http\Middleware;

use App\Models\LoadNoteDraft;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMobileVendorContext
{
    /** Endpoint aplikasi yang boleh dibuka driver (selain itu 403). */
    public const DRIVER_ROUTES = [
        'api.v1.load-notes.index', 'api.v1.load-notes.show', 'api.v1.load-notes.photo', 'api.v1.load-notes.vehicles',
        'api.v1.load-notes.inspect', 'api.v1.load-notes.scanned',
        'api.v1.unloading-notes.index', 'api.v1.unloading-notes.show', 'api.v1.driver-held-cash.index',
        'api.v1.unit-performance.index', 'api.v1.upah-gendong.vehicles', 'api.v1.upah-gendong.show',
        'api.v1.maintenance.vehicles', 'api.v1.filter-oil.vehicles', 'api.v1.tire-monitoring.show',
        'api.v1.tire-history.index', 'api.v1.tire-trips.index', 'api.v1.battery-monitoring.show', 'api.v1.battery-history.index',
        'api.v1.filter-oil-monitoring.show', 'api.v1.filter-oil-history.index', 'api.v1.filter-oil-trips.index',
    ];

    /**
     * Seluruh vendor biasanya hanya membaca; Admin & SU boleh ubah tanggal/hapus riwayat
     * maintenance dan mengatur Upah Gendong. Controller tetap memeriksa izin rinci.
     */
    public const ALL_VENDOR_WRITE_ROUTES = [
        'api.v1.tire-logs.update', 'api.v1.tire-logs.destroy', 'api.v1.battery-logs.update',
        'api.v1.battery-logs.destroy', 'api.v1.filter-oil-logs.update', 'api.v1.filter-oil-logs.destroy',
        'api.v1.upah-gendong-settings.store', 'api.v1.upah-gendong-settings.update', 'api.v1.upah-gendong-settings.destroy',
    ];

    /** Driver hanya boleh menulis untuk mengirim nota muat. */
    public const DRIVER_WRITE_ROUTES = ['api.v1.load-notes.inspect', 'api.v1.load-notes.scanned'];

    public function handle(Request $request, Closure $next, string $access = 'load-notes'): Response
    {
        if ($request->user()?->isDriver()) {
            return $this->driver($request, $next);
        }
        if ($request->user()?->seesAllVendors()) {
            // Seluruh vendor: hanya membaca (tanpa scan/kirim nota muat), kecuali Admin & SU
            // yang mengubah/menghapus riwayat maintenance.
            abort_unless($request->isMethodSafe() || ($request->routeIs(...self::ALL_VENDOR_WRITE_ROUTES)
                && in_array($request->user()->role, ['admin', 'su'], true)), 403);

            return $next($request);
        }
        if ($access === 'billing') {
            abort_unless((bool) $request->user()?->isVendorAccount(), 403);
        } else {
            abort_unless($request->user()?->can('viewAny', LoadNoteDraft::class), 403);
        }
        $expected = $request->header('X-Vendor-ID');
        abort_unless(is_string($expected) && ctype_digit($expected) && (int) $expected === (int) $request->user()->vendor_id, 409, 'Vendor akun telah berubah. Periksa ulang sesi sebelum melanjutkan.');

        return $next($request);
    }

    /** Driver: dikunci ke nolam yang sedang ditugaskan; tanpa header vendor. */
    private function driver(Request $request, Closure $next): Response
    {
        $vehicle = $request->user()->activeDriverVehicle();
        abort_if($vehicle === null, 403, 'Akses driver sudah tidak aktif.');
        abort_unless($request->routeIs(...self::DRIVER_ROUTES), 403);
        abort_unless($request->isMethodSafe() || $request->routeIs(...self::DRIVER_WRITE_ROUTES), 403);
        $routeVehicle = $request->route('vehicle');
        abort_unless($routeVehicle === null || (string) $routeVehicle === (string) $vehicle->getKey(), 404);
        $request->attributes->set('driverVehicle', $vehicle);

        return $next($request);
    }
}
