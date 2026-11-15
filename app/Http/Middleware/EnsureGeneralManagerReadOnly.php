<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** General Manager hanya membaca: semua permintaan selain GET/HEAD (kecuali logout) ditolak. */
class EnsureGeneralManagerReadOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isGeneralManager()) {
            abort_unless($request->isMethodSafe() || $request->routeIs('logout'), 403);
        }

        return $next($request);
    }
}
