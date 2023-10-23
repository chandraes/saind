<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceBayar extends Model
{
    use HasFactory;
    protected $fillable = [
        'tanggal',
        'periode',
        'no_invoice',
        'vendor_id',
        'total_bayar',
        'bayar',
        'sisa_bayar',
        'lunas',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
