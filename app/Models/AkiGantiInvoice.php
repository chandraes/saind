<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AkiGantiInvoice extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    const STATUS_PENDING  = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    const PEMBAYARAN_KAS_BESAR     = 'kas_besar';
    const PEMBAYARAN_DIBAYAR_SENDIRI = 'dibayar_sendiri';

    public static function getMetodePembayaranOptions(): array
    {
        return [
            self::PEMBAYARAN_DIBAYAR_SENDIRI => 'Dibayar Sendiri',
            self::PEMBAYARAN_KAS_BESAR       => 'Kas Besar',
        ];
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function details()
    {
        return $this->hasMany(AkiGantiInvoiceDetail::class, 'aki_ganti_invoice_id');
    }
}
