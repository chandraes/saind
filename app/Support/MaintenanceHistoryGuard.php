<?php

namespace App\Support;

use App\Models\AkiLog;
use App\Models\BanLog;
use App\Models\FilterOliLog;
use App\Models\PasswordKonfirmasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Satu pemeriksaan untuk edit/hapus riwayat ban, aki, dan filter & oli: peran yang boleh,
 * kepemilikan kendaraan untuk akun vendor, dan konfirmasi password.
 */
class MaintenanceHistoryGuard
{
    /** Hentikan request bila akun tidak boleh mengubah riwayat kendaraan ini. */
    public static function authorize(Request $request, AkiLog|BanLog|FilterOliLog $log): void
    {
        $user = $request->user();
        abort_unless($user->canManageMaintenanceHistory(), 403);
        abort_unless($user->canAccessVendor($log->vehicle()->value('vendor_id')), 404);
    }

    /** Vendor memakai password akunnya; admin dan su memakai password konfirmasi. */
    public static function passwordMatches(Request $request): bool
    {
        $password = (string) $request->input('password');
        if ($request->user()->isVendorAccount()) {
            return Hash::check($password, $request->user()->password);
        }

        return $password !== '' && $password === PasswordKonfirmasi::first()?->password;
    }
}
