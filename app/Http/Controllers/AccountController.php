<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AccountCredentials;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Pengaturan akun web (semua role): ganti username/password akun sendiri, sama seperti aplikasi mobile.
 * Dikecualikan dari middleware baca-saja eksekutif, HRD, dan driver.
 */
class AccountController extends Controller
{
    public const EDIT_ROUTE = 'account.edit';

    public const UPDATE_ROUTE = 'account.update';

    public function edit(): View
    {
        return view('account.edit');
    }

    public function update(Request $request, AccountCredentials $credentials): RedirectResponse
    {
        $passwordChanged = $request->filled('password');
        // Password berubah: semua login aplikasi mobile dicabut, cookie "ingat saya" lama tidak berlaku lagi,
        // dan sesi web di browser lain keluar lewat LogoutStaleSessions (sidik password di sesi tidak cocok).
        $user = $credentials->update($request, function (User $user): void {
            $user->tokens()->delete();
            $user->setRememberToken(Str::random(60));
        });

        if ($passwordChanged) {
            // Sesi browser ini tetap masuk: guard memegang user baru sehingga LogoutStaleSessions mencatat
            // sidik password baru setelah respons; cookie "ingat saya" browser ini diterbitkan ulang.
            $guard = Auth::guard('web');
            $guard->login($user, $request->hasCookie($guard->getRecallerName()));
        }

        return redirect()->route(self::EDIT_ROUTE)->with('success', $passwordChanged
            ? 'Akun diperbarui. Sesi di perangkat lain dan aplikasi mobile telah dikeluarkan.'
            : 'Username diperbarui.');
    }
}
