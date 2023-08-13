<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CustomerRute extends Pivot
{
    protected $table = 'customer_rute';
    protected $fillable = [
        'customer_id',
        'rute_id',
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
