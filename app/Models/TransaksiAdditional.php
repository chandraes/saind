<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiAdditional extends Model
{
    protected $guarded = [];

    const JENIS = [
        'kompensasi_jr' => 'Kompensasi Jalan Rusak',
        'penyesuaian_bbm' => 'Penyesuaian BBM',
        'achievement' => 'Achievement',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function rute()
    {
        return $this->belongsTo(Rute::class);
    }

}
