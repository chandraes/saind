<?php

namespace App\Models;

use Database\Factories\KategoriFilterOliMesinFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriFilterOliMesin extends Model
{
    /** @use HasFactory<KategoriFilterOliMesinFactory> */
    use HasFactory;

    protected $fillable = ['nama', 'limit_ritase'];

    protected function casts(): array
    {
        return ['limit_ritase' => 'integer'];
    }
}
