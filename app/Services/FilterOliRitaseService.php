<?php

namespace App\Services;

use App\Exceptions\FilterOliLimitException;
use App\Models\FilterOliGantiInvoiceDetail;
use App\Models\FilterOliLog;
use App\Models\FilterOliLogTransaksi;
use App\Models\KategoriFilterOliMesin;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

    public function assertWithinLimits(Vehicle $vehicle, ?User $actor = null): void
    {
        if (! $vehicle->pembatasan_filter_oli || in_array($actor?->role, ['admin', 'su'], true)) {
            return;
        }
        $categories = KategoriFilterOliMesin::orderBy('id')->get();
        if ($categories->isEmpty()) {
            throw ValidationException::withMessages(['vehicle_id' => 'Kategori filter & oli mesin belum diatur. Hubungi admin.']);
        }
        $latestLogs = FilterOliLog::where('vehicle_id', $vehicle->id)->where('created_at', '<=', now())
            ->latest()->orderByDesc('id')->get()->unique('kategori_filter_oli_mesin_id')->keyBy('kategori_filter_oli_mesin_id');
        $issues = [];
        $messages = [];
        foreach ($categories as $category) {
            $log = $latestLogs->get($category->id);
            if (! $log) {
                $messages[] = $category->nama.': log penggantian belum tersedia';
                $issues[] = ['category' => $category->nama, 'ritase' => null, 'limit' => (int) $category->limit_ritase, 'reason' => 'Belum ada data'];
            } elseif ((float) $log->ritase > $category->limit_ritase) {
                $messages[] = $category->nama.': ritase '.number_format((float) $log->ritase, 1, ',', '.').' melebihi limit '.$category->limit_ritase.' rit';
                $issues[] = ['category' => $category->nama, 'ritase' => (float) $log->ritase, 'limit' => (int) $category->limit_ritase, 'reason' => 'Melebihi limit'];
            }
        }
        if ($issues !== []) {
            throw new FilterOliLimitException($issues, 'Pengeluaran Uang Jalan ditolak. '.implode('; ', $messages).'. Lakukan penggantian filter & oli mesin terlebih dahulu.');
        }
    }

    public function replacementWarnings(Vehicle $vehicle): string
    {
        if (! $vehicle->pembatasan_filter_oli) {
            return '';
        }
        $latestLogs = FilterOliLog::where('vehicle_id', $vehicle->id)->where('created_at', '<=', now())
            ->latest()->orderByDesc('id')->get()->unique('kategori_filter_oli_mesin_id')->keyBy('kategori_filter_oli_mesin_id');
        $message = '';
        foreach (KategoriFilterOliMesin::orderBy('id')->get() as $category) {
            $log = $latestLogs->get($category->id);
            if (! $log) {
                continue;
            }
            $remaining = $category->limit_ritase - (float) $log->ritase;
            if ($remaining <= 1) {
                $remaining = rtrim(rtrim(number_format($remaining, 1, ',', ''), '0'), ',');
                $message .= "Ganti *{$category->nama}\nSisa {$remaining} ritase*\n\n";
            }
        }

        return $message;
    }

    public function recordTransaction(Transaksi $transaction): void
    {
        if ($transaction->void || $transaction->created_at->isFuture()) {
            return;
        }
        $vehicleId = DB::table('kas_uang_jalans')->where('id', $transaction->kas_uang_jalan_id)->value('vehicle_id');
        if (! $vehicleId) {
            return;
        }
        DB::transaction(function () use ($vehicleId, $transaction): void {
            Vehicle::whereKey($vehicleId)->lockForUpdate()->firstOrFail();
            $route = DB::table('kas_uang_jalans')->join('rutes', 'kas_uang_jalans.rute_id', '=', 'rutes.id')
                ->where('kas_uang_jalans.id', $transaction->kas_uang_jalan_id)->select('rutes.jarak')->first();
            if (! $route) {
                return;
            }
            $logs = FilterOliLog::where('vehicle_id', $vehicleId)->where('created_at', '<=', $transaction->created_at)
                ->lockForUpdate()->orderByDesc('created_at')->orderByDesc('id')->get()->unique('kategori_filter_oli_mesin_id');
            $additionalRitase = (float) $route->jarak > 50 ? 1.0 : 0.5;
            foreach ($logs as $log) {
                $audit = FilterOliLogTransaksi::firstOrCreate([
                    'filter_oli_log_id' => $log->id, 'transaksi_id' => $transaction->id,
                ], ['nilai_ritase' => $additionalRitase]);
                if ($audit->wasRecentlyCreated) {
                    $log->increment('ritase', $additionalRitase);
                    FilterOliGantiInvoiceDetail::where('filter_oli_log_id', $log->id)->update(['ritase' => $log->ritase]);
                }
            }
        });
    }

    public function rollbackTransaction(Transaksi $transaction): void
    {
        if (! $transaction->void) {
            return;
        }
        $vehicleId = DB::table('kas_uang_jalans')->where('id', $transaction->kas_uang_jalan_id)->value('vehicle_id');
        if (! $vehicleId) {
            return;
        }
        DB::transaction(function () use ($vehicleId, $transaction): void {
            Vehicle::whereKey($vehicleId)->lockForUpdate()->firstOrFail();
            $audits = FilterOliLogTransaksi::where('transaksi_id', $transaction->id)->lockForUpdate()->get();
            foreach ($audits as $audit) {
                $log = FilterOliLog::whereKey($audit->filter_oli_log_id)->lockForUpdate()->firstOrFail();
                $log->update(['ritase' => max(0, (float) $log->ritase - (float) $audit->nilai_ritase)]);
                FilterOliGantiInvoiceDetail::where('filter_oli_log_id', $log->id)->update(['ritase' => $log->ritase]);
                $audit->delete();
            }
        });
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
