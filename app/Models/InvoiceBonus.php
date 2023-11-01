<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceBonus extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class);
    }

      // has many transaksi from pivot table invoice_tagihan_details
    public function invoice_bonus_details()
    {
        return $this->hasMany(InvoiceBonusDetail::class);
    }


    public function transaksi()
    {
        return $this->hasManyThrough(
            Transaksi::class,
            InvoiceBonusDetail::class,
            'invoice_bonus_id',
            'id',
            'id',
            'transaksi_id'
        );
    }
}
