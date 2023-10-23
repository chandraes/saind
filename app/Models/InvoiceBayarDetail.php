<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceBayarDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'invoice_bayar_id',
        'transaksi_id',
    ];
}
