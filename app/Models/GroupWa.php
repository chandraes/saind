<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupWa extends Model
{
    use HasFactory;
    protected $fillable = [
        'untuk',
        'nama_grup',
    ];
}
