<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpahGendong extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $appends = ['nf_nominal', 'id_tanggal_masuk_driver', 'id_tanggal_masuk_pengurus'];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function getNfNominalAttribute()
    {
        return number_format($this->nominal, 0, ',', '.');
    }

    public function getIdTanggalMasukDriverAttribute()
    {
        return date('d-m-Y', strtotime($this->tanggal_masuk_driver));
    }

    public function getIdTanggalMasukPengurusAttribute()
    {
        return date('d-m-Y', strtotime($this->tanggal_masuk_pengurus));
    }
}
