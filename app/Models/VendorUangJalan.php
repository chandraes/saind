<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorUangJalan extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'rute_id',
        'hk_uang_jalan',
    ];

    protected $appends = [
        'nf_hk_uang_jalan',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function rute()
    {
        return $this->belongsTo(Rute::class);
    }

    public function getNfHkUangJalanAttribute()
    {
        return number_format($this->hk_uang_jalan, 0, ',', '.');
    }
}
