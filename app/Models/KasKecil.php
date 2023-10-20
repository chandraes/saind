<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasKecil extends Model
{
    use HasFactory;
    protected $fillable = [
        'tanggal',
        'nomor_kode_kas_kecil',
        'uraian',
        'jenis_transaksi_id',
        'nominal_transaksi',
        'saldo',
        'transfer_ke',
        'bank',
        'no_rekening',
        'void',
    ];

    public function jenis_transaksi()
    {
        return $this->belongsTo(JenisTransaksi::class);
    }

    public function getTanggalAttribute($value)
    {
        return date('d-m-Y', strtotime($value));
    }
}
