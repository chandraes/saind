<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerTagihan extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_id',
        'rute_id',
        'harga_tagihan',
        'opname',
        'titipan',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function rute()
    {
        return $this->belongsTo(Rute::class);
    }
}
