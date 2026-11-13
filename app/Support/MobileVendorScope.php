<?php

namespace App\Support;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Cakupan vendor untuk API mobile: akun vendor selalu vendornya sendiri;
 * role operasional melihat seluruh vendor, atau satu vendor lewat filter
 * `vendor_id` (null = seluruh vendor). Driver: vendor nolamnya dan hanya
 * nolam yang sedang ditugaskan (lihat vehicleId()).
 */
class MobileVendorScope
{
    public static function id(Request $request): ?int
    {
        $user = $request->user();
        if (! $user->isOperasional()) {
            return (int) $user->effectiveVendorId();
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

    /** Nolam driver (null untuk role lain = tanpa batas nolam). */
    public static function vehicleId(Request $request): ?int
    {
        if (! $request->user()->isDriver()) {
            return null;
        }
        $vehicle = $request->attributes->get('driverVehicle') ?? $request->user()->activeDriverVehicle();
        abort_if($vehicle === null, 403, 'Akses driver sudah tidak aktif.');

        return (int) $vehicle->getKey();
    }

    /**
     * Batasi kolom vendor bila cakupannya satu vendor; untuk driver juga
     * kolom nolam (`id` pada query kendaraan, selain itu `vehicle_id`).
     */
    public static function apply($query, Request $request, string $column = 'vendor_id')
    {
        $vendorId = self::id($request);
        $query->when($vendorId !== null, fn ($query) => $query->where($column, $vendorId));
        if (($vehicleId = self::vehicleId($request)) !== null) {
            $prefix = str_contains($column, '.') ? Str::beforeLast($column, '.').'.' : '';
            $isVehicle = $query instanceof EloquentBuilder && $query->getModel() instanceof Vehicle;
            $query->where($prefix.($isVehicle ? 'id' : 'vehicle_id'), $vehicleId);
        }

        return $query;
    }
}
