<?php

namespace App\Http\Middleware;

use App\Http\Controllers\AccountController;
use App\Support\ReminderRegistry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Eksekutif (General Manager & Direksi) hanya membaca: semua permintaan selain GET/HEAD ditolak, kecuali logout, pengaturan tampilan Pengingat, dan ganti username/password akun sendiri. */
class EnsureExecutiveReadOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isExecutive()) {
            abort_unless($request->isMethodSafe() || $request->routeIs('logout', ReminderRegistry::PREFERENCE_ROUTE, AccountController::UPDATE_ROUTE), 403);
        }

        return $next($request);
    }
}
