<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasUangJalan extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function jenis_transaksi()
    {
        return $this->belongsTo(JenisTransaksi::class);
    }

    public function getTanggalAttribute($value)
    {
        return date('d-m-Y', strtotime($value));
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function rute()
    {
        return $this->belongsTo(Rute::class);
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class);
    }
}
