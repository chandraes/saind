<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriBarangMaintenance extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function barang_maintenance()
    {
        return $this->hasMany(BarangMaintenance::class);
    }
}
