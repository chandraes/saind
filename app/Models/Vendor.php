<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $append = ['sisa_kas', 'nf_sisa_kas', 'total_uang_jalan', 'nf_total_uang_jalan', 'total_bayar', 'nf_total_bayar'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendor_uang_jalan()
    {
        return $this->hasMany(VendorUangJalan::class);
    }

    public function rute()
    {
        return $this->belongsToMany(Rute::class, 'vendor_uang_jalans');
    }

    public function vehicle()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class);
    }

    public function kas_vendor()
    {
        return $this->hasMany(KasVendor::class);
    }

    public function kas_uang_jalan()
    {
        return $this->hasMany(KasUangJalan::class, 'vendor_id', 'id');
    }

    public function transaksi()
    {
        return $this->hasManyThrough(Transaksi::class, KasUangJalan::class, 'vendor_id', 'kas_uang_jalan_id', 'id', 'id');
    }

    public function getSisaKasAttribute()
    {
        $total = $this->kas_vendor->sortByDesc('id')->first()->sisa ?? 0;
        return $total;
    }

    public function getNfSisaKasAttribute()
    {
        return number_format($this->sisa_kas, 0, ',', '.');
    }

    public function getTotalUangJalanAttribute()
    {
        $total = $this->load(['kas_uang_jalan','transaksi' => function ($query) {
            $query->where('transaksis.void', 0)->where('transaksis.status', 3)->where('transaksis.bayar', 0);
        }])->kas_uang_jalan->sum('nominal_transaksi');

        return $total;
    }

    public function getNfTotalUangJalanAttribute()
    {
        return number_format($this->total_uang_jalan, 0, ',', '.');
    }

    public function getNominalBayarAttribute()
    {
        $total = $this->load(['transaksi' => function ($query) {
            $query->where('transaksis.void', 0)->where('transaksis.status', 3)->where('transaksis.bayar', 0);
        }])->transaksi->sum('nominal_bayar');

        return $total;
    }

    public function getTotalBayarAttribute()
    {
        $total = $this->nominal_bayar - $this->total_uang_jalan;
        return $total;
    }



}
