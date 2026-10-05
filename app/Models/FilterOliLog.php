<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FilterOliLog extends Model
{
    use HasFactory;

    protected $fillable = ['kategori_filter_oli_mesin_id', 'vehicle_id', 'merk', 'kondisi', 'ritase', 'limit_ritase', 'created_at'];

    protected $attributes = ['kondisi' => 100, 'ritase' => 0];

    protected function casts(): array
    {
        return ['kondisi' => 'integer', 'ritase' => 'decimal:1'];
    }

    public function transaksiLogs(): HasMany
    {
        return $this->hasMany(FilterOliLogTransaksi::class);
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
