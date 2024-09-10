<?php

namespace App\Models\Legalitas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalitasKategori extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function dokumen()
    {
        return $this->hasMany(LegalitasDokumen::class, 'legalitas_kategori_id');
    }
}
