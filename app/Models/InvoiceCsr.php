<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceCsr extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoiceCsrDetail()
    {
        return $this->hasMany(InvoiceCsrDetail::class);
    }

    public function transaksi()
    {
        return $this->hasManyThrough(
            Transaksi::class,
            InvoiceCsrDetail::class,
            'invoice_csr_id',
            'id',
            'id',
            'transaksi_id'
        );
    }
}
