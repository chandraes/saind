<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BanGantiInvoice extends Model
{
    protected $guarded = ['id'];

    // Konstanta Metode Pembayaran
    public const PEMBAYARAN_DIBAYAR_SENDIRI = 'dibayar_sendiri';
    public const PEMBAYARAN_KAS_BESAR       = 'kas_besar';

    /**
     * Opsi metode pembayaran yang tersedia.
     * Jika ada penambahan metode pembayaran di kemudian hari, cukup tambahkan di sini.
     */
    public static function getMetodePembayaranOptions(): array
    {
        return [
            self::PEMBAYARAN_DIBAYAR_SENDIRI => 'Dibayar Sendiri',
            self::PEMBAYARAN_KAS_BESAR       => 'Kas Besar',
        ];
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function details()
    {
        return $this->hasMany(BanGantiInvoiceDetail::class);
    }
}
