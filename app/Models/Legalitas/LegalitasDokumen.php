<?php

namespace App\Models\Legalitas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalitasDokumen extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function kategori()
    {
        return $this->belongsTo(LegalitasKategori::class, 'legalitas_kategori_id');
    }
}
