<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersentaseAwal extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function pemegang_saham()
    {
        return $this->hasMany(PemegangSaham::class);
    }
}
