<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AkiGantiCart extends Model
{
    use HasFactory;

    protected $table = 'aki_ganti_carts';

    protected $fillable = [
        'vehicle_id',
        'posisi_aki_id',
        'merk',
        'no_seri',
        'kondisi',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function posisiAki()
    {
        return $this->belongsTo(PosisiAki::class, 'posisi_aki_id');
    }
}
