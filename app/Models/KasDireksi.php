<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasDireksi extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function direksi()
    {
        return $this->belongsTo(Direksi::class);
    }
}
