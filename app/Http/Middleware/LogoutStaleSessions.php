<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sesi web menyimpan [id user, sidik password]. Bila password akun diganti (Pengaturan akun), sesi lain milik
 * user yang sama tidak cocok lagi dan dikeluarkan; sesi yang mengganti password diperbarui setelah respons.
 * Berbeda dengan AuthenticateSession bawaan, sesi yang berganti user (actingAs/login baru) cukup dicatat ulang.
 */
class LogoutStaleSessions
{
    public const SESSION_KEY = 'account_password_fingerprint';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        // User yang tidak tersimpan (mis. factory()->make() di tes) tidak punya password yang bisa diganti.
        if ($user?->exists && $request->hasSession()) {
            [$id, $fingerprint] = $request->session()->get(self::SESSION_KEY) ?? [null, null];
            if ((string) $id === (string) $user->getAuthIdentifier() && ! hash_equals((string) $fingerprint, self::fingerprint($user))) {
                Auth::guard('web')->logoutCurrentDevice();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                $message = 'Password akun telah diganti. Silakan login kembali.';

                return $request->expectsJson()
                    ? response()->json(['message' => $message], 401)
                    : redirect()->route('login')->withErrors(['username' => $message]);
            }
        }

        $response = $next($request);

        if (($user = $request->user())?->exists && $request->hasSession()) {
            $request->session()->put(self::SESSION_KEY, [$user->getAuthIdentifier(), self::fingerprint($user)]);
        }

        return $response;
    }

    private static function fingerprint(User $user): string
    {
        return hash_hmac('sha256', (string) $user->getAuthPassword(), (string) config('app.key'));
    }
}
