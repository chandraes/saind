<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceAddVendorDetail extends Model
{
    protected $guarded = [];

    public function invoiceAddVendor()
    {
        return $this->belongsTo(InvoiceAddVendor::class);
    }

    public function transaksiAdditional()
    {
        return $this->belongsTo(TransaksiAdditional::class);
    }


}
