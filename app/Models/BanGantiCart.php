<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BanGantiCart extends Model
{
    protected $guarded = ['id'];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function posisiBan()
    {
        return $this->belongsTo(PosisiBan::class);
    }
}
