<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorBayar extends Model
{
    use HasFactory;
    protected $fillable = [
        'nama',
        'vendor_id',
        'customer_id',
        'harga_kesepakatan',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

}
