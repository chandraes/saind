<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasUangJalan extends Model
{
    use HasFactory;
    protected $fillable = [
        'tanggal',
        'nama_vendor',
        'nomor_lambung',
        'kode_kas_uang_jalan',
        'nomor_kas_uang_jalan',
        'kode_uang_jalan',
        'nomor_uang_jalan',
        'jenis_transaksi_id',
        'nominal_transaksi',
        'saldo',
        'tambang',
        'rute',
        'transfer_ke',
        'bank',
        'no_rekening',
    ];
}
