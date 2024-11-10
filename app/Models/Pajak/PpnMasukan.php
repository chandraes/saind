<?php

namespace App\Models\Pajak;

use App\Models\InvoiceBayar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PpnMasukan extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['tanggal', 'nf_nominal'];

    public function invoiceBayar()
    {
        return $this->belongsTo(InvoiceBayar::class, 'invoice_bayar_id');
    }

    public function getTanggalAttribute()
    {
        return date('d-m-Y', strtotime($this->created_at));
    }

    public function getNfNominalAttribute()
    {
        return number_format($this->nominal, 0, ',', '.');
    }
}
