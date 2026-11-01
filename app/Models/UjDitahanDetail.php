<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UjDitahanDetail extends Model
{
    protected $guarded = [];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id', 'id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }

    public function ujDitahan()
    {
        return $this->belongsTo(UjDitahan::class, 'uj_ditahan_id', 'id');
    }

    public function cancellation(): HasOne
    {
        return $this->hasOne(UjDitahanPayoutCancellation::class, 'detail_id')->where('fund', 'sparepart');
    }

    public function isManualPayout(): bool
    {
        return $this->payout_origin === 'manual' && $this->jenis === 'keluar'
            && filled($this->file_pdf) && ! $this->transaksi_id
            && ! $this->aki_ganti_invoice_id && ! $this->filter_oli_ganti_invoice_id && ! $this->ban_ganti_invoice_id;
    }
}
