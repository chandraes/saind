<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorBayar extends Model
{
    use HasFactory;
    protected $fillable = [
        'vendor_id',
        'customer_id',
        'rute_id',
        'hk_opname',
        'hk_titipan',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function rute()
    {
        return $this->belongsTo(Rute::class);
    }

}
