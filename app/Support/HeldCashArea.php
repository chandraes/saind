<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Halaman UJ Ditahan admin (Sparepart | Ban) juga dibuka vendor-induk dan
 * operasional lewat area vendor-operational: tampilan sama, baca saja, dan
 * vendor-induk hanya melihat nolam milik vendornya.
 */
final class HeldCashArea
{
    public static function name(): string
    {
        return request()->routeIs('vendor-operational.*') ? 'vendor-operational' : 'billing';
    }

    public static function readOnly(): bool
    {
        return self::name() === 'vendor-operational';
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $accounts  akun uj_ditahans / uj_ditahan_bans
     * @return Builder<TModel>
     */
    public static function scope(Builder $accounts, ?User $user): Builder
    {
        return $accounts->when($user?->role === 'vendor-induk',
            fn (Builder $query) => $query->whereHas('vehicle', fn (Builder $vehicle) => $vehicle->where('vendor_id', $user->vendor_id ?? 0)));
    }
}
