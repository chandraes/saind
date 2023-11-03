<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function kas_uang_jalan()
    {
        return $this->belongsTo(KasUangJalan::class);
    }

    public function getTanggalMuatAttribute($value)
    {
        return date('d-m-Y', strtotime($value));
    }

    // public function getTanggalBongkarAttribute($value)
    // {
    //     return date('d-m-Y', strtotime($value));
    // }

}
