<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleChecker
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Vendor-induk memakai seluruh rute vendor; pembedaan maintenance ada di User.
        if (in_array('vendor', $roles, true)) {
            $roles[] = 'vendor-induk';
        }
        // Eksekutif (GM & Direksi) membaca semua menu operasional (penulisan diblok EnsureExecutiveReadOnly);
        // `executive` adalah alias semua role eksekutif.
        if (in_array('operasional', $roles, true) || in_array('executive', $roles, true)) {
            array_push($roles, ...User::EXECUTIVE_ROLES);
        }
        // Admin & SU di aplikasi mobile ikut membaca menu eksekutif (lihat User::isMobileAdministrator()).
        $mobileAdministrator = in_array('executive', $roles, true) && $request->user()->isMobileAdministrator();
        if (! $mobileAdministrator && ! in_array($request->user()->role, $roles)) {
            abort(403);
        }

        return $next($request);

    }
}
