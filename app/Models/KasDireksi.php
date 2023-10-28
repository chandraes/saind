<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class KasDireksi extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function direksi()
    {
        return $this->belongsTo(Direksi::class);
    }

    public function getTanggalAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }

    public function sisa_kas_terakhir()
    {
        return $this->latest()->orderBy('id', 'desc')->first()->sisa_kas;
    }


}
