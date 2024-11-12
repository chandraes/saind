<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceBayar extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function getIdTanggalAttribute()
    {
        return Carbon::parse($this->tanggal)->format('d-m-Y');
    }

    public function invoice_bayar_details()
    {
        return $this->hasMany(InvoiceBayarDetail::class);
    }


    public function transaksi()
    {
        return $this->hasManyThrough(
            Transaksi::class,
            InvoiceBayarDetail::class,
            'invoice_bayar_id',
            'id',
            'id',
            'transaksi_id'
        );
    }
}
