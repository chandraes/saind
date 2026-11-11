<?php

namespace App\Http\Middleware;

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
        if (! in_array($request->user()->role, $roles)) {
            abort(403);
        }

        return $next($request);

    }
}
