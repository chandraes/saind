<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FilterOliGantiInvoice extends Model
{
    use HasFactory;

    protected $fillable = ['no_invoice', 'user_id', 'vehicle_id', 'authorized_by', 'authorized_at', 'pembayaran', 'total_nominal', 'nama_bank', 'nomor_rekening', 'nama_rekening', 'status'];

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $attributes = ['status' => 'pending', 'total_nominal' => 0];

    protected function casts(): array
    {
        return ['total_nominal' => 'decimal:2', 'authorized_at' => 'datetime'];
    }

    public function details(): HasMany
    {
        return $this->hasMany(FilterOliGantiInvoiceDetail::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
}
