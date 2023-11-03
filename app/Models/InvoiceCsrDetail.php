<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceCsrDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function invoice_csr()
    {
        return $this->belongsTo(InvoiceCsr::class);
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'transaksi_id');
    }
}
