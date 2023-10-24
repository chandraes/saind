<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekapBarang extends Model
{
    use HasFactory;
    protected $fillable = [
        'tanggal',
        'jenis_transaksi',
        'barang_id',
        'nama_barang',
        'jumlah',
        'harga_satuan',
        'total',
    ];
}
