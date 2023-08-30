<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasBesar extends Model
{
    use HasFactory;
    protected $fillable = [
        'tanggal',
        'uraian',
        'kode_deposit',
        'nomor_kode_deposit',
        'kode_kas_kecil',
        'nomor_kode_kas_kecil',
        'kode_kas_uang_jalan',
        'nomor_kode_kas_uang_jalan',
        'tipe_transaksi_id',
        'jenis_transaksi_id',
        'nominal_transaksi',
        'saldo',
        'transfer_ke',
        'bank',
        'no_rekening',
        'modal_investor',
        'modal_investor_terakhir',
    ];

    public function jenis_transaksi()
    {
        return $this->belongsTo(JenisTransaksi::class);
    }

    public function tipe_transaksi()
    {
        return $this->belongsTo(TipeTransaksi::class);
    }

    public function getTanggalAttribute($value)
    {
        return date('d-m-Y', strtotime($value));
    }

    public function setTanggalAttribute($value)
    {
        $this->attributes['tanggal'] = date('Y-m-d', strtotime($value));
    }


}
