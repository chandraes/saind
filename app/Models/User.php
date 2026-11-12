<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
