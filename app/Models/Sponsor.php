<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{
    use HasFactory;
    protected $fillable = [
        'kode',
        'nomor_kode_sponsor',
        'nama',
        'nomor_wa',
        'transfer_ke',
        'nama_bank',
        'nomor_rekening'
    ];

    public function vendor()
    {
        return $this->hasMany(Vendor::class);
    }
}
