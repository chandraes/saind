<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    use HasFactory;
    protected $guarded  = ['id'];

    protected $appends = ['nf_nilai'];

    public function getNfNilaiAttribute()
    {
        return number_format($this->nilai, 0, ',', '.');
    }
}
