<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class InvoiceAdditional extends Model
{

    protected $guarded = ['id'];
    protected $appends = ['tanggal', 'dpp_nominal', 'nf_dpp_nominal'];

    public function getTanggalAttribute()
    {
        return Carbon::parse($this->updated_at)->format('Y-m-d');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function dataTahun()
    {
        return $this->selectRaw('YEAR(updated_at) tahun')->groupBy('tahun')->orderBy('tahun', 'desc')->get();
    }

    public function details()
    {
        return $this->hasMany(InvoiceAdditionalDetail::class);
    }

    public function getNfNominalAttribute()
    {
        return number_format($this->nominal, 0 ,',','.');
    }

    public function getDppNominalAttribute()
    {
        return $this->nominal * 0.98;
    }

    public function getNfDppNominalAttribute()
    {
        return number_format($this->dpp_nominal, 0 ,',','.');
    }
}
