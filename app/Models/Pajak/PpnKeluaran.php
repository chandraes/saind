<?php

namespace App\Models\Pajak;

use App\Models\InvoiceTagihan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PpnKeluaran extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['tanggal', 'nf_nominal'];

    public function invoiceTagihan()
    {
        return $this->belongsTo(InvoiceTagihan::class, 'invoice_tagihan_id');
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
