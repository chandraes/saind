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

    /** Akun milik vendor: hanya boleh melihat data vendornya sendiri, tanpa mengubah data. */
    public const VENDOR_ACCOUNT_ROLES = ['vendor', 'vendor-operational'];

    public function isVendorAccount(): bool
    {
        return in_array($this->role, self::VENDOR_ACCOUNT_ROLES, true);
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

    /** Boleh mengedit/menghapus riwayat ban dan aki (vendor: kendaraannya sendiri). */
    public function canManageMaintenanceHistory(): bool
    {
        return in_array($this->role, ['admin', 'su', 'vendor'], true);
    }

    /** Vendor boleh melihat keuangannya sendiri; vendor operational hanya uang jalan. */
    public function canViewVendorFinance(): bool
    {
        return $this->role === 'vendor';
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
