<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpahGendong extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['nf_nominal', 'id_tanggal_masuk_driver', 'id_tanggal_masuk_pengurus'];

    /** Rute 50 km ke atas dihitung sebagai rute panjang (berhak upah gendong). */
    public const LONG_ROUTE_KM = 50;

    public static function isLongRoute(float $distanceKm): bool
    {
        return $distanceKm >= self::LONG_ROUTE_KM;
    }

    /** Tonase di atas minimum pada rute panjang; rute pendek selalu 0. */
    public function excessTonnage(float $distanceKm, float $unloadedTonnage): float
    {
        return self::isLongRoute($distanceKm) ? max(0, $unloadedTonnage - (float) $this->tonase_min) : 0.0;
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function getNfNominalAttribute()
    {
        return number_format($this->nominal, 0, ',', '.');
    }

    public function getIdTanggalMasukDriverAttribute()
    {
        return date('d-m-Y', strtotime($this->tanggal_masuk_driver));
    }

    public function getIdTanggalMasukPengurusAttribute()
    {
        return date('d-m-Y', strtotime($this->tanggal_masuk_pengurus));
    }
}
