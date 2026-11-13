<?php

namespace App\Services;

use App\Models\UjDitahan;
use App\Models\UjDitahanBan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Kas UJ ditahan per nolam (web & mobile), baca saja. Driver: master kas dibatasi
 * ke nolam yang ditugaskan dan mutasi ke driver_id akun (mutasi lama tanpa
 * driver_id tidak ikut karena pemiliknya tidak bisa dipastikan). Tanpa driver
 * (vendor-induk & operasional): seluruh mutasi nolam, sama dengan ledger admin.
 */
class DriverHeldCash
{
    private Builder $accounts;

    private Builder $mutations;

    private HasMany $relation;

    public function __construct(?int $driverId, int $vehicleId, string $fund)
    {
        $account = $fund === 'ban' ? new UjDitahanBan : new UjDitahan;
        $this->accounts = $account->newQuery()->where('vehicle_id', $vehicleId);
        $this->relation = $account->details();
        $this->mutations = $this->relation->getRelated()->newQuery()->when($driverId !== null, fn (Builder $query) => $query->where('driver_id', $driverId));
    }

    /** Mutasi (milik driver, bila dibatasi) pada satu bulan & tahun. */
    public function period(int $month, int $year): Builder
    {
        return (clone $this->mutations)->whereIn($this->relation->getForeignKeyName(),
            (clone $this->accounts)->where('tahun', $year)->where('bulan', $month)->select('id'));
    }

    /** @return array{masuk: int, keluar: int, saldo: int} */
    public function totals(int $month, int $year): array
    {
        $totals = $this->period($month, $year)->selectRaw("SUM(CASE WHEN jenis = 'masuk' THEN nominal ELSE 0 END) AS masuk, SUM(CASE WHEN jenis = 'keluar' THEN nominal ELSE 0 END) AS keluar")->first();

        return ['masuk' => (int) $totals->masuk, 'keluar' => (int) $totals->keluar, 'saldo' => (int) $totals->masuk - (int) $totals->keluar];
    }

    /** Saldo per periode (tahun, bulan, saldo) untuk seluruh mutasi (driver) pada nolam ini. */
    public function periodBalances(): Collection
    {
        return (clone $this->mutations)
            ->joinSub((clone $this->accounts)->select('id', 'bulan', 'tahun'), 'periods', 'periods.id', '=', $this->relation->getQualifiedForeignKeyName())
            ->groupBy('periods.tahun', 'periods.bulan')
            ->select('periods.tahun', 'periods.bulan')
            ->selectRaw("SUM(CASE WHEN jenis = 'masuk' THEN nominal WHEN jenis = 'keluar' THEN -nominal ELSE 0 END) AS saldo")
            ->get();
    }
}
