<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;
    protected $fillable = [
        'kategori_barang_id',
        'nama',
        'harga_jual',
        'stok',
    ];

    public function kategori_barang()
    {
        return $this->belongsTo(KategoriBarang::class);
    }
}
