<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerTagihan extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_id',
        'rute_id',
        'harga_tagihan',
        'opname',
        'titipan',
        'titipan_khusus',
    ];

    protected $appends = [
        'nf_harga_tagihan',
        'nf_opname',
        'nf_titipan',
        'nf_titipan_khusus',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function rute()
    {
        return $this->belongsTo(Rute::class);
    }

    public function getNfHargaTagihanAttribute()
    {
        return number_format($this->harga_tagihan, 0, ',', '.');
    }

    public function getNfOpnameAttribute()
    {
        return number_format($this->opname, 0, ',', '.');
    }

    public function getNfTitipanAttribute()
    {
        return number_format($this->titipan, 0, ',', '.');
    }

    public function getNfTitipanKhususAttribute()
    {
        return number_format($this->titipan_khusus, 0, ',', '.');
    }
}
