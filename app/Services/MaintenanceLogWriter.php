<?php

namespace App\Services;

use App\Models\AkiLog;
use App\Models\BanLog;
use App\Models\FilterOliGantiInvoiceDetail;
use App\Models\FilterOliLog;
use App\Models\Transaksi;
use App\Models\Vehicle;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya tempat pencatatan histori penggantian ban, aki, dan filter & oli.
 * Dipakai otorisasi Form Maintenance (admin) dan input langsung akun vendor,
 * sehingga aturan ritase dan tukar serep selalu sama di kedua jalur.
 */
class MaintenanceLogWriter
{
    public const SPARE_TIRE_POSITION = 11;

    public function __construct(private FilterOliRitaseService $filterRitase) {}

    /**
     * Catat ban terpasang. Sumber "serep" menukar ban serep ke posisi tujuan dan
     * menurunkan ban lama menjadi serep; sumber "baru" menghitung ritase dari
     * transaksi sejak tanggal ganti (jarak > 50 km = 1, selainnya 0,5).
     */
    public function tire(int $vehicleId, int $positionId, string $source, ?string $brand, ?string $serial, ?int $condition, CarbonInterface $date, float $fallbackRitase = 0): BanLog
    {
        if ($source === 'serep') {
            $spare = BanLog::where('vehicle_id', $vehicleId)->where('posisi_ban_id', self::SPARE_TIRE_POSITION)->orderBy('id', 'desc')->first();
            $old = BanLog::where('vehicle_id', $vehicleId)->where('posisi_ban_id', $positionId)->orderBy('id', 'desc')->first();
            $log = BanLog::create([
                'vehicle_id' => $vehicleId, 'posisi_ban_id' => $positionId,
                'merk' => $spare->merk ?? '-', 'no_seri' => $spare->no_seri ?? '-',
                'kondisi' => $spare->kondisi ?? 100, 'ritase' => $spare->ritase ?? 0, 'created_at' => $date,
            ]);
            if ($old) {
                BanLog::create([
                    'vehicle_id' => $vehicleId, 'posisi_ban_id' => self::SPARE_TIRE_POSITION,
                    'merk' => $old->merk, 'no_seri' => $old->no_seri, 'kondisi' => $old->kondisi,
                    'ritase' => $old->ritase, 'created_at' => $date,
                ]);
            }

            return $log;
        }

        $trips = Transaksi::select('transaksis.id', 'rutes.jarak')
            ->join('kas_uang_jalans', 'transaksis.kas_uang_jalan_id', '=', 'kas_uang_jalans.id')
            ->join('rutes', 'kas_uang_jalans.rute_id', '=', 'rutes.id')
            ->where('kas_uang_jalans.vehicle_id', $vehicleId)
            ->where('transaksis.void', 0)
            ->whereBetween('transaksis.created_at', [$date, now()])
            ->get()
            ->map(fn ($trip): array => ['transaksi_id' => $trip->id, 'nilai_ritase' => (float) $trip->jarak > 50 ? 1.0 : 0.5]);
        $counted = $trips->sum('nilai_ritase');

        $log = BanLog::create([
            'vehicle_id' => $vehicleId, 'posisi_ban_id' => $positionId,
            'merk' => $brand, 'no_seri' => $serial, 'kondisi' => $condition,
            'ritase' => $counted > 0 ? $counted : $fallbackRitase, 'created_at' => $date,
        ]);

        $now = now();
        foreach ($trips->chunk(500) as $chunk) {
            DB::table('ban_log_transaksis')->insert($chunk->map(fn (array $trip): array => $trip + [
                'ban_log_id' => $log->id, 'created_at' => $now, 'updated_at' => $now,
            ])->values()->all());
        }

        return $log;
    }

    public function battery(int $vehicleId, int $positionId, string $brand, ?string $serial, int $condition, CarbonInterface $date): AkiLog
    {
        return AkiLog::create([
            'vehicle_id' => $vehicleId, 'posisi_aki_id' => $positionId,
            'merk' => $brand, 'no_seri' => $serial, 'kondisi' => $condition, 'created_at' => $date,
        ]);
    }

    /** Catat filter & oli tanpa menghitung ulang; panggil refreshFilterOil() setelah semua item. */
    public function filterOil(int $vehicleId, int $categoryId, string $brand, int $condition, CarbonInterface $date, float $ritase = 0): FilterOliLog
    {
        return FilterOliLog::create([
            'vehicle_id' => $vehicleId, 'kategori_filter_oli_mesin_id' => $categoryId,
            'merk' => $brand, 'kondisi' => $condition, 'ritase' => $ritase, 'created_at' => $date,
        ]);
    }

    public function refreshFilterOil(int $vehicleId): void
    {
        $this->filterRitase->refreshVehicle($vehicleId);
    }

    /** Ubah tanggal ganti filter & oli lalu hitung ulang ritase seluruh periode kendaraan. */
    public function rescheduleFilterOil(FilterOliLog $log, CarbonInterface $date): void
    {
        DB::transaction(function () use ($log, $date): void {
            Vehicle::whereKey($log->vehicle_id)->lockForUpdate()->firstOrFail();
            $record = FilterOliLog::whereKey($log->id)->lockForUpdate()->firstOrFail();
            $record->update(['created_at' => $date]);
            FilterOliGantiInvoiceDetail::where('filter_oli_log_id', $record->id)->update(['created_at' => $date]);
            $this->refreshFilterOil($record->vehicle_id);
        }, 3);
    }

    /** Hapus histori filter & oli; invoice yang menunjuk log ini tetap tersimpan. */
    public function removeFilterOil(FilterOliLog $log): void
    {
        DB::transaction(function () use ($log): void {
            Vehicle::whereKey($log->vehicle_id)->lockForUpdate()->firstOrFail();
            $record = FilterOliLog::whereKey($log->id)->lockForUpdate()->firstOrFail();
            FilterOliGantiInvoiceDetail::where('filter_oli_log_id', $record->id)->update(['filter_oli_log_id' => null]);
            $record->delete();
            $this->refreshFilterOil($record->vehicle_id);
        }, 3);
    }
}
