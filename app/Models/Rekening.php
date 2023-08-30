<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rekening extends Model
{
    use HasFactory;
    protected $fillable = [
        'untuk',
        'nama_bank',
        'nomor_rekening',
        'nama_rekening',
    ];
}
