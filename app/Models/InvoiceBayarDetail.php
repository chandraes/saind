<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceBayarDetail extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function invoice_bayar()
    {
        return $this->belongsTo(InvoiceBayar::class);
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'transaksi_id');
    }
}
