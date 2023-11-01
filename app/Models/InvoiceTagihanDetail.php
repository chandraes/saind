<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceTagihanDetail extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function invoice_tagihan()
    {
        return $this->belongsTo(InvoiceTagihan::class);
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'transaksi_id');
    }
}
