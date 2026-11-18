<?php

namespace App\Services;

use App\Models\UjDitahan;
use App\Models\UjDitahanBan;
use App\Models\UjDitahanBanDetail;
use App\Models\UjDitahanDetail;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Cutoff saldo UJ Ditahan satu periode ke rekening driver (saldo menjadi nol).
 * Sparepart di sini; Ban lewat UjDitahanBanPayoutService. Dipakai halaman web
 * UJ Ditahan dan API mobile (Admin & SU). Periode bersaldo nol ditolak, jadi
 * ketukan ulang tidak mencairkan dua kali.
 */
class UjDitahanCutoff
{
    public function __construct(private UjDitahanBanPayoutService $ban) {}

    public function cutoff(UjDitahan|UjDitahanBan $account, string $token): UjDitahanDetail|UjDitahanBanDetail
    {
        if ($account instanceof UjDitahanBan) {
            return $this->ban->cutoff($account, $token);
        }

        return DB::transaction(function () use ($account): UjDitahanDetail {
            $vehicle = Vehicle::with('driver')->whereKey($account->vehicle_id)->lockForUpdate()->firstOrFail();
            $locked = UjDitahan::whereKey($account->id)->lockForUpdate()->firstOrFail();
            if ($locked->saldo <= 0) {
                throw ValidationException::withMessages(['saldo' => 'Fitur cutoff saldo minus sedang dalam pengembangan!.']);
            }
            $driver = $vehicle->driver;
            if (! $driver) {
                throw ValidationException::withMessages(['rekening' => 'Nolam ini belum punya driver. Lengkapi driver sebelum cutoff UJ Ditahan.']);
            }
            $nominal = $locked->saldo;
            $detail = UjDitahanDetail::create([
                'uj_ditahan_id' => $locked->id, 'jenis' => 'keluar', 'nominal' => $nominal, 'keterangan' => 'Cutoff',
                'bank' => $driver->bank, 'no_rekening' => $driver->no_rek, 'nama_rekening' => $driver->nama_rek,
            ]);
            $locked->decrement('saldo', $nominal);
            $locked->increment('total_keluar', $nominal);

            return $detail;
        });
    }
}
