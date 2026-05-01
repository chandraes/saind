<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class InvoiceAdditional extends Model
{

    protected $guarded = ['id'];
    protected $appends = ['tanggal', 'dpp', 'nf_dpp'];

    public function getTanggalAttribute()
    {
        return Carbon::parse($this->updated_at)->format('Y-m-d');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function details()
    {
        return $this->hasMany(InvoiceAdditionalDetail::class);
    }

    public function getNfNominalAttribute()
    {
        return number_format($this->nominal, 0 ,',','.');
    }

    public function getDppAttribute()
    {
        return $this->nominal * 0.98;
    }

    public function getNfDppAttribute()
    {
        return number_format($this->dpp, 0 ,',','.');
    }
}
