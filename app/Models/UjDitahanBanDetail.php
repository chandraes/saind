<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UjDitahanBanDetail extends Model
{
    use HasFactory;

    protected $fillable = ['uj_ditahan_ban_id', 'transaksi_id', 'driver_id', 'jenis', 'nominal', 'keterangan', 'bank', 'no_rekening', 'nama_rekening', 'file_pdf', 'request_token', 'ban_ganti_invoice_id'];

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
}
