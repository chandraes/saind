<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasBonCicilan extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['id_tanggal', 'nf_nominal', 'nf_total_bayar', 'nf_sisa_kas', 'nf_cicilan_nominal'];

    public function getIdTanggalAttribute()
    {
        return date('d-m-Y', strtotime($this->tanggal));
    }

    public function getNfNominalAttribute()
    {
        return number_format($this->nominal, 0, ',', '.');
    }

    public function getNfTotalBayarAttribute()
    {
        return number_format($this->total_bayar, 0, ',', '.');
    }

    public function getNfSisaKasAttribute()
    {
        return number_format($this->sisa_kas, 0, ',', '.');
    }

    public function getNfCicilanNominalAttribute()
    {
        return number_format($this->cicilan_nominal, 0, ',', '.');
    }

    public function getTanggalMulaiAttribute()
    {
        // create date M Y in Indonesian from column mulai_bulan for Mont and mulai_tahun for Year with Carbon
        return Carbon::createFromDate($this->mulai_tahun, $this->mulai_bulan)->translatedFormat('F Y');

    }

    public function getTanggalSelesaiAttribute()
    {
        // create date M Y in Indonesian from column selesai_bulan for Mont and selesai_tahun for Year with Carbon then add month from value of cicil_kali
        return Carbon::createFromDate($this->mulai_tahun, $this->mulai_bulan)->addMonths($this->cicil_kali)->translatedFormat('F Y');

    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}
