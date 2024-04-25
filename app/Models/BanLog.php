<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BanLog extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function posisi_ban()
    {
        return $this->belongsTo(PosisiBan::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
