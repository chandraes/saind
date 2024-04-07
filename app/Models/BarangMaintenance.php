<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangMaintenance extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $appends = ['nf_harga_jual'];

    public function getNfHargaJualAttribute()
    {
        return number_format($this->harga_jual, 0, ',', '.');
    }

    public function jualVendorStore($data)
    {
        $kv = new KasVendor();

        
    }
}
