<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UjDitahanPayoutCancellation extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['nominal' => 'integer'];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
