<?php

namespace App\Http\Middleware;

use App\Models\LoadNoteDraft;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMobileVendorContext
{
    public function handle(Request $request, Closure $next, string $access = 'load-notes'): Response
    {
        if ($access === 'billing') {
            abort_unless($request->user()?->role === 'vendor-operational', 403);
        } else {
            abort_unless($request->user()?->can('viewAny', LoadNoteDraft::class), 403);
        }
        $expected = $request->header('X-Vendor-ID');
        abort_unless(is_string($expected) && ctype_digit($expected) && (int) $expected === (int) $request->user()->vendor_id, 409, 'Vendor akun telah berubah. Periksa ulang sesi sebelum melanjutkan.');

        return $next($request);
    }
}
