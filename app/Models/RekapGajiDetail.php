<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekapGajiDetail extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function rekap_gaji()
    {
        return $this->belongsTo(RekapGaji::class);
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}
