<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FilterOliGantiCart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'vehicle_id', 'kategori_filter_oli_mesin_id', 'merk', 'kondisi', 'ritase', 'created_at'];

    protected $attributes = ['kondisi' => 100, 'ritase' => 0];

    protected function casts(): array
    {
        return ['kondisi' => 'integer', 'ritase' => 'decimal:1'];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriFilterOliMesin::class, 'kategori_filter_oli_mesin_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
}
