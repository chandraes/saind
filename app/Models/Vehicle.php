<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['id_tanggal_pajak_stnk', 'id_tanggal_kir', 'id_tanggal_kimper', 'id_tanggal_sim'];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeNonaktif($query)
    {
        return $query->where('status', 'nonaktif');
    }

    public static function nextNomorLambung()
    {
        return static::max('nomor_lambung') + 1;
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function kas_uang_jalan()
    {
        return $this->hasMany(KasUangJalan::class);
    }

    public function getIdTanggalPajakStnkAttribute()
    {
        return Carbon::parse($this->tanggal_pajak_stnk)->format('d-m-Y');
    }

    public function getIdTanggalKirAttribute()
    {
        return $this->tanggal_kir != null ? Carbon::parse($this->tanggal_kir)->format('d-m-Y') : 00-00-0000;
    }

    public function getIdTanggalKimperAttribute()
    {
        return Carbon::parse($this->tanggal_kimper)->format('d-m-Y');
    }

    public function getIdTanggalSimAttribute()
    {
        return Carbon::parse($this->tanggal_sim)->format('d-m-Y');
    }

}
