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
 * Versi akun juga berubah saat dinonaktifkan agar sesi lama tetap ditolak setelah aktivasi ulang.
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
            $guard = Auth::guard('web');
            $sameUser = (string) $id === (string) $user->getAuthIdentifier();
            $stale = $sameUser
                ? ! hash_equals((string) $fingerprint, self::fingerprint($user))
                : (int) $user->account_session_version > 0 && ! $guard->viaRemember();
            if (! $user->isActive() || $stale) {
                $guard->logoutCurrentDevice();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                $message = match (true) {
                    ! $user->isActive() => 'Akun telah dinonaktifkan.',
                    default => 'Sesi akun tidak berlaku lagi. Silakan login kembali.',
                };

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
        $password = (string) $user->getAuthPassword();
        $version = (int) $user->account_session_version;

        return hash_hmac('sha256', $version === 0 ? $password : $password.':'.$version, (string) config('app.key'));
    }
}
