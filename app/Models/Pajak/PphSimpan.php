<?php

namespace App\Models\Pajak;

use App\Models\InvoiceBayar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PphSimpan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['nf_nominal'];

    public function getNfNominalAttribute()
    {
        return number_format($this->nominal, 0, ',', '.');
    }

    public function invoice()
    {
        return $this->belongsTo(InvoiceBayar::class, 'invoice_bayar_id');
    }
}
