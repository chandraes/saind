<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasVendor extends Model
{
    use HasFactory;
    protected $fillable = [
        'vendor_id',
        'vehicle_id',
        'quantity',
        'harga_satuan',
        'tanggal',
        'uraian',
        'pinjaman',
        'bayar',
        'sisa',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
