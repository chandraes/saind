<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Cakupan vendor untuk API mobile: akun vendor selalu vendornya sendiri;
 * role operasional melihat seluruh vendor, atau satu vendor lewat filter
 * `vendor_id` (null = seluruh vendor).
 */
class MobileVendorScope
{
    public static function id(Request $request): ?int
    {
        $user = $request->user();
        if (! $user->isOperasional()) {
            return (int) $user->vendor_id;
        }
        $vendorId = $request->validate(['vendor_id' => ['nullable', 'integer', 'exists:vendors,id']])['vendor_id'] ?? null;

        return $vendorId === null ? null : (int) $vendorId;
    }

    /** Untuk data yang selalu per vendor (Nota Bayar): operasional wajib memilih vendor. */
    public static function required(Request $request): int
    {
        $vendorId = self::id($request);
        if ($vendorId === null) {
            throw ValidationException::withMessages(['vendor_id' => 'Pilih vendor terlebih dahulu.']);
        }

        return $vendorId;
    }

    /** Batasi kolom vendor bila cakupannya satu vendor. */
    public static function apply($query, Request $request, string $column = 'vendor_id')
    {
        $vendorId = self::id($request);

        return $query->when($vendorId !== null, fn ($query) => $query->where($column, $vendorId));
    }
}
