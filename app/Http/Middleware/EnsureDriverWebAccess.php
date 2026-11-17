<?php

namespace App\Http\Middleware;

use App\Http\Controllers\AccountController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureDriverWebAccess
{
    /** Halaman yang boleh dibuka driver (semuanya GET, dikunci ke nolamnya). */
    public const READABLE_ROUTES = [
        'home', 'driver.notes', 'driver.notes.show', 'driver.held-cash',
        'statistik.perform-unit', 'statistik.upah-gendong',
        'statistik.ban-luar', 'statistik.ban-luar.histori', 'statistik.ban-luar.histori-data', 'statistik.ban-luar.transaksi-ritase',
        'statistik.aki', 'statistik.aki.histori', 'statistik.aki.histori-data',
        'statistik.filter-oli', 'statistik.filter-oli.histori', 'statistik.filter-oli.transaksi-ritase',
        AccountController::EDIT_ROUTE,
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user?->isDriver()) {
            return $next($request);
        }

        if (($vehicle = $user->activeDriverVehicle()) === null) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $request->expectsJson()
                ? response()->json(['message' => 'Akses driver sudah tidak aktif.'], 401)
                : redirect()->route('login')->withErrors(['username' => 'Akses driver sudah tidak aktif.']);
        }

        abort_unless($request->routeIs('logout', AccountController::UPDATE_ROUTE) || ($request->isMethodSafe() && $request->routeIs(...self::READABLE_ROUTES)), 403);
        $request->attributes->set('driverVehicle', $vehicle);

        return $next($request);
    }
}
