<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UjDitahanBan extends Model
{
    use HasFactory;

    protected $fillable = ['vehicle_id', 'bulan', 'tahun', 'total_masuk', 'total_keluar', 'saldo'];

    protected function casts(): array
    {
        return ['vehicle_id' => 'integer', 'bulan' => 'integer', 'tahun' => 'integer', 'total_masuk' => 'integer', 'total_keluar' => 'integer', 'saldo' => 'integer'];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(UjDitahanBanDetail::class);
    }
}
