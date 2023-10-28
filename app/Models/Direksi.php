<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Direksi extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function kas_direksi()
    {
        return $this->hasMany(KasDireksi::class);
    }

    public function kas_direksi_terakhir()
    {
        return $this->kas_direksi()->latest()->orderBy('id', 'desc')->first();
    }
}
