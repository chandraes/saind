<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function kas_bon()
    {
        return $this->hasMany(KasBon::class);
    }

    public function kas_bon_cicilan()
    {
        return $this->hasMany(KasBonCicilan::class);
    }

    // last nomor
    public function getNomor()
    {
        $last = $this->latest()->orderBy('id', 'desc')->first();
        if (!$last) {
            return 1;
        }
        return $last->nomor + 1;
    }
}
