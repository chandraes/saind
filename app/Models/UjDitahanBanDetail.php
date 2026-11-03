<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UjDitahanBanDetail extends Model
{
    use HasFactory;

    protected $fillable = ['uj_ditahan_ban_id', 'transaksi_id', 'driver_id', 'jenis', 'nominal', 'keterangan', 'bank', 'no_rekening', 'nama_rekening', 'file_pdf', 'request_token', 'ban_ganti_invoice_id', 'payout_origin'];

    protected function casts(): array
    {
        return ['nominal' => 'integer'];
    }

    public function ujDitahanBan(): BelongsTo
    {
        return $this->belongsTo(UjDitahanBan::class);
    }

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function banGantiInvoice(): BelongsTo
    {
        return $this->belongsTo(BanGantiInvoice::class);
    }

    public function cancellation(): HasOne
    {
        return $this->hasOne(UjDitahanPayoutCancellation::class, 'detail_id')->where('fund', 'ban');
    }

    public function isManualPayout(): bool
    {
        return $this->payout_origin === 'manual' && $this->jenis === 'keluar'
            && filled($this->file_pdf) && ! $this->transaksi_id
            && ! $this->aki_ganti_invoice_id && ! $this->filter_oli_ganti_invoice_id && ! $this->ban_ganti_invoice_id;
    }
}
