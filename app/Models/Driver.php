<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'nama', 'no_sim', 'masa_berlaku_sim', 'no_hp',
        'no_rek', 'nama_rek', 'bank', 'alamat',
        'foto_sim', 'status', 'keterangan'
    ];

    protected $casts = [
        'masa_berlaku_sim' => 'date',
    ];
}
