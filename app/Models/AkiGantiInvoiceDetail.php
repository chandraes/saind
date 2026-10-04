<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AkiGantiInvoiceDetail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function previousAkiLog(int $vehicleId): ?AkiLog
    {
        return AkiLog::where('vehicle_id', $vehicleId)
            ->where('posisi_aki_id', $this->posisi_aki_id)
            ->where('created_at', '<=', $this->created_at)
            ->when($this->aki_log_id, fn ($query) => $query->where('id', '!=', $this->aki_log_id))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();
    }

    public static function usageDays(?CarbonInterface $installedAt, CarbonInterface $replacedAt): ?int
    {
        if ($installedAt === null || $installedAt->greaterThan($replacedAt)) {
            return null;
        }

        return (int) $installedAt->copy()->startOfDay()->diffInDays($replacedAt->copy()->startOfDay(), false);
    }

    public function invoice()
    {
        return $this->belongsTo(AkiGantiInvoice::class, 'aki_ganti_invoice_id');
    }

    public function posisiAki()
    {
        return $this->belongsTo(PosisiAki::class, 'posisi_aki_id');
    }

    public function akiLog()
    {
        return $this->belongsTo(AkiLog::class, 'aki_log_id');
    }
}
