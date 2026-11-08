<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class LoadNoteDraft extends Model
{
    use HasFactory;

    protected $fillable = ['transfer_override', 'revision', 'transaksi_id', 'customer_id', 'rute_id', 'header_name', 'unit_code', 'route_reference', 'date_source', 'vendor_id', 'vehicle_id', 'created_by', 'request_id', 'payload_hash', 'ticket_number', 'mine_name', 'loaded_on', 'vehicle_number', 'driver_name', 'gross_kg', 'tare_kg', 'net_kg', 'photo_path', 'photo_mime', 'status', 'notes'];

    protected $hidden = ['photo_path', 'payload_hash'];

    protected function casts(): array
    {
        return ['transfer_override' => 'integer', 'revision' => 'integer', 'transaksi_id' => 'integer', 'customer_id' => 'integer', 'rute_id' => 'integer', 'vendor_id' => 'integer', 'vehicle_id' => 'integer', 'created_by' => 'integer', 'gross_kg' => 'integer', 'tare_kg' => 'integer', 'net_kg' => 'integer', 'loaded_on' => 'date'];
    }

    public static function cachedPendingReviewCount(): int
    {
        $connection = (new static)->getConnection();
        $scope = hash('sha256', json_encode([$connection->getName(), $connection->getConfig('host'), $connection->getDatabaseName(), config('mobile.workspace_id')]));

        return (int) Cache::remember('load-notes:pending-count:'.$scope, now()->addSeconds(30),
            fn (): int => static::where('status', 'draft')->count());
    }

    public function events(): HasMany
    {
        return $this->hasMany(LoadNoteEvent::class)->latest('id');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Menunggu pemeriksaan', 'rejected' => 'Ditolak', 'cancelled' => 'Dibatalkan', 'processed' => 'Sudah diproses', default => $this->status,
        };
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function rute(): BelongsTo
    {
        return $this->belongsTo(Rute::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        $search = trim($search);
        if ($search === '') {
            return $query;
        }
        $term = '%'.$search.'%';

        return $query->where(fn (Builder $query): Builder => $query->where('ticket_number', 'like', $term)
            ->orWhere('vehicle_number', 'like', $term)->orWhere('mine_name', 'like', $term));
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
