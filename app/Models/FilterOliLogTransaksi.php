<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FilterOliLogTransaksi extends Model
{
    protected $fillable = ['filter_oli_log_id', 'transaksi_id', 'nilai_ritase'];

    protected function casts(): array
    {
        return ['nilai_ritase' => 'decimal:1'];
    }

    public function log(): BelongsTo
    {
        return $this->belongsTo(FilterOliLog::class, 'filter_oli_log_id');
    }

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class);
    }
}
