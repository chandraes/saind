<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceBonusDetail extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function invoice_bonus()
    {
        return $this->belongsTo(InvoiceBonus::class);
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'transaksi_id');
    }
}
