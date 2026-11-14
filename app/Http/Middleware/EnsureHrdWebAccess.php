<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** HRD hanya membuka menu Potongan Absensi (semuanya GET), dashboard, dan logout. */
class EnsureHrdWebAccess
{
    public const READABLE_ROUTES = [
        'home', 'mobile.workspace.show',
        'hrd.attendance-deductions', 'hrd.attendance-deductions.show', 'hrd.attendance-deductions.pdf',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isHrd()) {
            abort_unless($request->routeIs('logout') || ($request->isMethodSafe() && $request->routeIs(...self::READABLE_ROUTES)), 403);
        }

        return $next($request);
    }
}
