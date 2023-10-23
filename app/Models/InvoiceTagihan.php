<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceTagihan extends Model
{
    use HasFactory;
    protected $fillable = [
        'tanggal',
        'periode',
        'no_invoice',
        'customer_id',
        'total_tagihan',
        'total_bayar',
        'sisa_tagihan',
        'lunas',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // has many transaksi through invoice_tagihan_details
    public function transaksi()
    {
        return $this->hasManyThrough(Transaksi::class, InvoiceTagihanDetail::class);
    }
}
