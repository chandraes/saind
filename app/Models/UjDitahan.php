<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UjDitahan extends Model
{
    protected $guarded = [];

    public function details()
    {
        return $this->hasMany(UjDitahanDetail::class, 'uj_ditahan_id', 'id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'id');
    }
}
