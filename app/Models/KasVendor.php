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
        'bbm_storing_id',
        'quantity',
        'harga_satuan',
        'tanggal',
        'uraian',
        'pinjaman',
        'bayar',
        'sisa',
        'storing',
        'void',
        'jasa',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function bbm_storing()
    {
        return $this->belongsTo(BbmStoring::class);
    }

    public function sisa_terakhir()
    {
        return $this->latest()->orderBy('id', 'desc')->first()->sisa;
    }
}
