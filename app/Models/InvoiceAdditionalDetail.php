<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceAdditionalDetail extends Model
{
    protected $guarded = ['id'];

    public function invoiceAdditional()
    {
        return $this->belongsTo(InvoiceAdditional::class);
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }
}
