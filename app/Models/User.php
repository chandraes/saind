<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'role',
        'name',
        'email',
        'password',
        'vendor_id',
        'customer_id',
        'driver_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /** Akun milik vendor: hanya boleh menyentuh data vendornya sendiri. */
    public const VENDOR_ACCOUNT_ROLES = ['vendor', 'vendor-induk', 'vendor-operational'];

    /**
     * Akun pemilik vendor (bukan vendor operational): menu & keuangan vendor sama.
     * Bedanya, vendor-induk mengganti maintenance lewat Form Maintenance admin.
     */
    public const VENDOR_OWNER_ROLES = ['vendor', 'vendor-induk'];

    public function isVendorAccount(): bool
    {
        return in_array($this->role, self::VENDOR_ACCOUNT_ROLES, true);
    }

    public function isVendorOwner(): bool
    {
        return in_array($this->role, self::VENDOR_OWNER_ROLES, true);
    }

    /**
     * Role operasional: menu sama dengan vendor-operational tetapi untuk seluruh
     * vendor, hanya melihat (tanpa keuangan, tanpa scan nota muat).
     */
    public function isOperasional(): bool
    {
        return $this->role === 'operasional';
    }

    /** Akun yang memakai menu vendor-operational: akun vendor dan operasional (semua vendor). */
    public function usesVendorMenus(): bool
    {
        return $this->isVendorAccount() || $this->isOperasional();
    }

    /** Akun vendor hanya boleh menyentuh data milik vendornya sendiri. */
    public function canAccessVendor(int|string|null $vendorId): bool
    {
        return ! $this->isVendorAccount() || ($this->vendor_id !== null && (int) $vendorId === (int) $this->vendor_id);
    }

    /** Vendor operational hanya boleh melihat, tanpa mengubah data apa pun. */
    public function isReadOnlyVendorAccount(): bool
    {
        return $this->role === 'vendor-operational';
    }

    /**
     * Boleh menambah/mengedit/menghapus riwayat ban, aki, filter & oli (vendor:
     * kendaraannya sendiri). Vendor-induk hanya melihat; penggantian lewat Form Maintenance.
     */
    public function canManageMaintenanceHistory(): bool
    {
        return in_array($this->role, ['admin', 'su', 'vendor'], true);
    }

    /** Mengatur Database Upah Gendong (vendor & vendor-induk: hanya kendaraannya sendiri). */
    public function canManageUpahGendong(): bool
    {
        return in_array($this->role, ['admin', 'su', 'vendor', 'vendor-induk'], true);
    }

    /**
     * Filter & Oli Mesin hanya untuk kendaraan yang diaktifkan admin
     * (pembatasan_filter_oli). Akun vendor mengelola sendiri tanpa pengaktifan.
     */
    public function requiresFilterOilActivation(): bool
    {
        return $this->role !== 'vendor';
    }

    /** Vendor & vendor-induk boleh melihat keuangannya sendiri; vendor operational hanya uang jalan. */
    public function canViewVendorFinance(): bool
    {
        return $this->isVendorOwner();
    }

    /** Kas UJ Ditahan per nolam (baca saja): vendor-induk untuk nolam vendornya, operasional semua nolam. */
    public function canViewNolamHeldCash(): bool
    {
        return in_array($this->role, ['vendor-induk', 'operasional'], true);
    }

    /**
     * Nolam Kas UJ Ditahan seperti halaman admin: vendor-induk hanya nolam vendornya,
     * operasional semua nolam; status nonaktif tetap ikut.
     */
    public function heldCashVehicles(): Builder
    {
        return Vehicle::query()->when(! $this->isOperasional(), fn ($query) => $query->where('vendor_id', $this->vendor_id ?? 0));
    }

    /** Nolam yang masih bersaldo UJ ditahan (Sparepart atau Ban) pada periode mana pun. */
    public function heldCashVehiclesWithBalance(): Builder
    {
        return $this->heldCashVehicles()->where(fn (Builder $query) => $query
            ->whereIn('id', UjDitahan::query()->select('vehicle_id')->where('saldo', '!=', 0))
            ->orWhereIn('id', UjDitahanBan::query()->select('vehicle_id')->where('saldo', '!=', 0)));
    }

    public function isDriver(): bool
    {
        return $this->role === 'driver';
    }

    /** HRD: hanya menu Potongan Absensi (baca saja, tanpa nominal gaji lain). */
    public function isHrd(): bool
    {
        return $this->role === 'hrd';
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function activeDriverVehicle(): ?Vehicle
    {
        if (! $this->isDriver() || $this->driver_id === null || ! $this->driver()->where('status', 'aktif')->exists()) {
            return null;
        }

        return Vehicle::where('driver_id', $this->driver_id)->where('status', '!=', 'nonaktif')->first();
    }

    /**
     * Statistik & maintenance versi vendor (baca saja, satu nolam): akun vendor
     * dan driver (dikunci ke nolam yang sedang ditugaskan).
     */
    public function readsVendorStatistics(): bool
    {
        return $this->isVendorAccount() || $this->isDriver();
    }

    /** Vendor pemilik data akun: vendor akun, atau vendor nolam yang ditugaskan ke driver. */
    public function effectiveVendorId(): ?int
    {
        return $this->isDriver() ? $this->activeDriverVehicle()?->vendor_id : $this->vendor_id;
    }

    /** Nolam yang boleh dipakai akun untuk nota muat: milik vendor akun, atau hanya nolam driver. */
    public function noteVehicles(): Builder
    {
        return Vehicle::query()->where('vehicles.vendor_id', $this->effectiveVendorId())
            ->when($this->isDriver(), fn (Builder $query): Builder => $query->where('vehicles.driver_id', $this->driver_id));
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
