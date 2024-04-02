<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AktivasiMaintenance extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['id_tanggal_mulai'];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function getIdTanggalMulaiAttribute()
    {
        return date('d-m-Y', strtotime($this->tanggal_mulai));
    }
}
