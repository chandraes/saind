<?php

namespace App\Services;

use App\Models\Rekening;
use App\Models\Transaksi;
use App\Models\UjDitahanBan;
use App\Models\UjDitahanBanDetail;
use App\Models\Vehicle;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UjDitahanBanService
{
    public function credit(Transaksi $transaction, Vehicle $vehicle, int $nominal, CarbonInterface $period, ?Rekening $rekening): void
    {
        if ($nominal <= 0) {
            throw ValidationException::withMessages(['rute_id' => 'Nominal UJ Ditahan Ban belum diisi.']);
        }
        DB::transaction(function () use ($transaction, $vehicle, $nominal, $period, $rekening): void {
            Vehicle::whereKey($vehicle->id)->lockForUpdate()->firstOrFail();
            if (UjDitahanBanDetail::where('transaksi_id', $transaction->id)->where('jenis', 'masuk')->exists()) {
                return;
            }
            $master = UjDitahanBan::firstOrCreate([
                'vehicle_id' => $vehicle->id, 'bulan' => $period->month, 'tahun' => $period->year,
            ], ['total_masuk' => 0, 'total_keluar' => 0, 'saldo' => 0]);
            $master->increment('total_masuk', $nominal);
            $master->increment('saldo', $nominal);
            UjDitahanBanDetail::create([
                'uj_ditahan_ban_id' => $master->id, 'transaksi_id' => $transaction->id,
                'driver_id' => $vehicle->driver_id, 'jenis' => 'masuk', 'nominal' => $nominal,
                'keterangan' => 'UJ Ditahan Ban (Trx UJ'.sprintf('%02d', $transaction->kas_uang_jalan->nomor_uang_jalan).')',
                'bank' => $rekening?->nama_bank, 'no_rekening' => $rekening?->nomor_rekening,
                'nama_rekening' => $rekening?->nama_rekening,
            ]);
        });
    }

    public function reverse(Transaksi $transaction, Vehicle $vehicle, string $reason, ?Rekening $rekening): int
    {
        return DB::transaction(function () use ($transaction, $vehicle, $reason, $rekening): int {
            Vehicle::whereKey($vehicle->id)->lockForUpdate()->firstOrFail();
            $incoming = UjDitahanBanDetail::where('transaksi_id', $transaction->id)->where('jenis', 'masuk')->lockForUpdate()->first();
            if (! $incoming || UjDitahanBanDetail::where('transaksi_id', $transaction->id)->where('jenis', 'keluar')->exists()) {
                return 0;
            }
            $master = UjDitahanBan::whereKey($incoming->uj_ditahan_ban_id)->lockForUpdate()->firstOrFail();
            UjDitahanBanDetail::create([
                'uj_ditahan_ban_id' => $master->id, 'transaksi_id' => $transaction->id,
                'driver_id' => $incoming->driver_id, 'jenis' => 'keluar', 'nominal' => $incoming->nominal,
                'keterangan' => 'Void UJ'.sprintf('%02d', $transaction->kas_uang_jalan->nomor_uang_jalan).' - '.$reason,
                'bank' => $rekening?->nama_bank, 'no_rekening' => $rekening?->nomor_rekening,
                'nama_rekening' => $rekening?->nama_rekening,
            ]);
            $master->decrement('saldo', $incoming->nominal);
            $master->increment('total_keluar', $incoming->nominal);

            return (int) $incoming->nominal;
        });
    }
}
