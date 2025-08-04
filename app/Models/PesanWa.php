<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesanWa extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    public function short_pesan()
    {
        return substr($this->pesan, 0, 1000) . '.............';
    }
}
