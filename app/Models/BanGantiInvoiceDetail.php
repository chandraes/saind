<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BanGantiInvoiceDetail extends Model
{
    protected $guarded = ['id'];
    
    public function invoice()
    {
        return $this->belongsTo(BanGantiInvoice::class, 'ban_ganti_invoice_id');
    }

    public function posisiBan()
    {
        return $this->belongsTo(PosisiBan::class);
    }
}
