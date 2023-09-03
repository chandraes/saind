<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'nomor_lambung',
        'nopol',
        'nama_stnk',
        'no_rangka',
        'no_mesin',
        'tipe',
        'tahun',
        'no_kartu_gps',
        'status',
        'transfer_ke',
        'bank',
        'no_rekening',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeNonaktif($query)
    {
        return $query->where('status', 'nonaktif');
    }

    public static function nextNomorLambung()
    {
        return static::max('nomor_lambung') + 1;
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
