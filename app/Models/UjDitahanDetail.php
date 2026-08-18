<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UjDitahanDetail extends Model
{
    protected $guarded = [];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id', 'id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }

    public function ujDitahan()
    {
        return $this->belongsTo(UjDitahan::class, 'uj_ditahan_id', 'id');
    }
}
