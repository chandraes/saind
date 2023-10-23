<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceTagihanDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'invoice_tagihan_id',
        'transaksi_id',
    ];
}
