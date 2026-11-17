<?php

namespace App\Http\Middleware;

use App\Support\ReminderRegistry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Eksekutif (General Manager & Direksi) hanya membaca: semua permintaan selain GET/HEAD ditolak, kecuali logout dan pengaturan tampilan Pengingat akun sendiri. */
class EnsureExecutiveReadOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isExecutive()) {
            abort_unless($request->isMethodSafe() || $request->routeIs('logout', ReminderRegistry::PREFERENCE_ROUTE), 403);
        }

        return $next($request);
    }
}
