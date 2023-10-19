<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;
    protected $fillable = [
        'nama',
        'nickname',
        'tipe',
        'jabatan',
        'perusahaan',
        'npwp',
        'alamat',
        'no_hp',
        'email',
        'bank',
        'no_rekening',
        'nama_rekening',
        'status',
        'sponsor_id',
        'user_id',
        'bank_uj',
        'no_rekening_uj',
        'nama_rekening_uj',
        'pembayaran',
        'ppn',
        'pph',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendor_uang_jalan()
    {
        return $this->hasMany(VendorUangJalan::class);
    }

    public function rute()
    {
        return $this->belongsToMany(Rute::class, 'vendor_uang_jalans');
    }

    public function vehicle()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class);
    }
}
