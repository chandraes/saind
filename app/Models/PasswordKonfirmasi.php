<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordKonfirmasi extends Model
{
    use HasFactory;
    protected $fillable = [
        'password',
    ];
}
