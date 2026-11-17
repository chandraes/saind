<?php

namespace App\Http\Middleware;

use App\Support\ReminderRegistry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * HRD hanya membuka menu Potongan Absensi & Pengingat (semuanya GET), dashboard, dan logout;
 * satu-satunya penulisan: pengaturan tampilan Pengingat milik akun sendiri.
 */
class EnsureHrdWebAccess
{
    public const READABLE_ROUTES = [
        'home', 'mobile.workspace.show',
        'hrd.attendance-deductions', 'hrd.attendance-deductions.show', 'hrd.attendance-deductions.pdf',
        'reminders.index', 'reminders.data',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isHrd()) {
            abort_unless($request->routeIs('logout', ReminderRegistry::PREFERENCE_ROUTE) || ($request->isMethodSafe() && $request->routeIs(...self::READABLE_ROUTES)), 403);
        }

        return $next($request);
    }
}
