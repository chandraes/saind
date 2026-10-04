<?php

namespace App\Services;

use App\Models\FilterOliGantiInvoiceDetail;
use App\Models\FilterOliLog;
use App\Models\FilterOliLogTransaksi;
use App\Models\Transaksi;
use App\Models\Vehicle;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FilterOliRitaseService
{
    /** @return Collection<int, Transaksi> */
    public function transactions(int $vehicleId, CarbonInterface $from, ?CarbonInterface $until = null): Collection
    {
        return Transaksi::query()
            ->join('kas_uang_jalans', 'transaksis.kas_uang_jalan_id', '=', 'kas_uang_jalans.id')
            ->join('rutes', 'kas_uang_jalans.rute_id', '=', 'rutes.id')
            ->where('kas_uang_jalans.vehicle_id', $vehicleId)
            ->where('transaksis.void', 0)
            ->where('transaksis.created_at', '>=', $from)
            ->where('transaksis.created_at', '<=', now())
            ->when($until, fn ($query) => $query->where('transaksis.created_at', '<', $until))
            ->select('transaksis.id', 'transaksis.created_at', 'rutes.jarak', 'rutes.nama as rute', 'kas_uang_jalans.nomor_uang_jalan')
            ->orderBy('transaksis.created_at')->orderBy('transaksis.id')->get()
            ->each(fn ($transaction) => $transaction->setAttribute('nilai_ritase', (float) $transaction->jarak > 50 ? 1.0 : 0.5));
    }

    /** @return Collection<int, Transaksi> */
    public function preview(int $vehicleId, int $categoryId, CarbonInterface $from): Collection
    {
        $next = FilterOliLog::where('vehicle_id', $vehicleId)
            ->where('kategori_filter_oli_mesin_id', $categoryId)->where('created_at', '>', $from)
            ->orderBy('created_at')->orderBy('id')->first();

        return $this->transactions($vehicleId, $from, $next?->created_at);
    }

    public function refreshVehicle(int $vehicleId): void
    {
        DB::transaction(function () use ($vehicleId): void {
            Vehicle::whereKey($vehicleId)->lockForUpdate()->firstOrFail();
            $logs = FilterOliLog::where('vehicle_id', $vehicleId)->lockForUpdate()
                ->orderBy('created_at')->orderBy('id')->get()->groupBy('kategori_filter_oli_mesin_id');
            foreach ($logs as $categoryLogs) {
                $categoryLogs = $categoryLogs->values();
                foreach ($categoryLogs as $index => $log) {
                    $this->syncLog($log, $categoryLogs->get($index + 1)?->created_at);
                }
            }
        });
    }

    public function refreshForTransaction(Transaksi $transaction): void
    {
        $vehicleId = DB::table('kas_uang_jalans')->where('id', $transaction->kas_uang_jalan_id)->value('vehicle_id');
        if ($vehicleId && FilterOliLog::where('vehicle_id', $vehicleId)->exists()) {
            $this->refreshVehicle((int) $vehicleId);
        }
    }

    private function syncLog(FilterOliLog $log, ?CarbonInterface $until): void
    {
        $transactions = $this->transactions($log->vehicle_id, $log->created_at, $until);
        FilterOliLogTransaksi::where('filter_oli_log_id', $log->id)->whereNotIn('transaksi_id', $transactions->modelKeys())->delete();
        $timestamp = now();
        $rows = $transactions->map(fn ($transaction) => [
            'filter_oli_log_id' => $log->id, 'transaksi_id' => $transaction->id,
            'nilai_ritase' => $transaction->nilai_ritase, 'created_at' => $timestamp, 'updated_at' => $timestamp,
        ])->all();
        foreach (array_chunk($rows, 500) as $chunk) {
            FilterOliLogTransaksi::upsert($chunk, ['filter_oli_log_id', 'transaksi_id'], ['nilai_ritase', 'updated_at']);
        }
        $ritase = (float) $transactions->sum('nilai_ritase');
        $log->update(['ritase' => $ritase]);
        FilterOliGantiInvoiceDetail::where('filter_oli_log_id', $log->id)->update(['ritase' => $ritase]);
    }
}
