<?php

namespace App\Http\Middleware;

use App\Services\MobileWorkspace;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class EnsureMobileToken
{
    public function __construct(private MobileWorkspace $workspace) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();
        abort_unless($request->bearerToken() && $token instanceof PersonalAccessToken
            && $token->can('workspace:'.$this->workspace->identity()['id']), 401, 'Token mobile tidak valid.');
        abort_unless(in_array($user->role, config('mobile.roles'), true), 403, 'Akun tidak diizinkan menggunakan API mobile.');
        if ($user->role === 'vendor-operational') {
            abort_unless($user->vendor_id && $user->vendor()->exists(), 403, 'Akun belum terhubung dengan vendor.');
        }

        return $next($request);
    }
}
