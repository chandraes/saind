<?php

namespace App\Http\Middleware;

use App\Services\MobileWorkspace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMobileWorkspace
{
    public function __construct(private MobileWorkspace $workspace) {}

    public function handle(Request $request, Closure $next): Response
    {
        $identity = $this->workspace->identity();
        abort_unless($request->header('X-Workspace-ID') === $identity['id'], 409, 'Ruang kerja tidak sesuai. Pilih ulang ruang kerja sebelum login atau mengirim data.');

        return $next($request);
    }
}
