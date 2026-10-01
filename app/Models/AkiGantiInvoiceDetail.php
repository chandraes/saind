<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AkiGantiInvoiceDetail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function invoice()
    {
        return $this->belongsTo(AkiGantiInvoice::class, 'aki_ganti_invoice_id');
    }

    public function posisiAki()
    {
        return $this->belongsTo(PosisiAki::class, 'posisi_aki_id');
    }

    public function akiLog()
    {
        return $this->belongsTo(AkiLog::class, 'aki_log_id');
    }
}
