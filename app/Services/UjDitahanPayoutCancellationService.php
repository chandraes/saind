<?php

namespace App\Services;

use App\Models\UjDitahan;
use App\Models\UjDitahanBan;
use App\Models\UjDitahanBanDetail;
use App\Models\UjDitahanDetail;
use App\Models\UjDitahanPayoutCancellation;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UjDitahanPayoutCancellationService
{
    public function cancel(string $fund, int $detailId, User $actor, string $reason): UjDitahanPayoutCancellation
    {
        abort_unless(in_array($actor->role, ['admin', 'su'], true), 403);
        abort_unless(in_array($fund, ['sparepart', 'ban'], true), 404);
        if (trim($reason) === '' || mb_strlen($reason) > 500) {
            throw ValidationException::withMessages(['reason' => 'Alasan pembatalan wajib diisi, maksimal 500 karakter.']);
        }
        $detailClass = $fund === 'ban' ? UjDitahanBanDetail::class : UjDitahanDetail::class;
        $accountClass = $fund === 'ban' ? UjDitahanBan::class : UjDitahan::class;
        $foreign = $fund === 'ban' ? 'uj_ditahan_ban_id' : 'uj_ditahan_id';
        $source = $detailClass::findOrFail($detailId);

        return DB::transaction(function () use ($source, $accountClass, $detailClass, $foreign, $fund, $actor, $reason): UjDitahanPayoutCancellation {
            $account = $accountClass::findOrFail($source->{$foreign});
            Vehicle::whereKey($account->vehicle_id)->lockForUpdate()->firstOrFail();
            $account = $accountClass::whereKey($account->id)->lockForUpdate()->firstOrFail();
            $detail = $detailClass::whereKey($source->id)->lockForUpdate()->firstOrFail();
            if (! $detail->isManualPayout()) {
                throw ValidationException::withMessages(['payout' => 'Hanya pencairan manual yang dapat dibatalkan. Maintenance, cutoff, dan transaksi uang jalan tidak dapat dibatalkan melalui fitur ini.']);
            }
            if ($detail->cancellation()->lockForUpdate()->first()) {
                throw ValidationException::withMessages(['payout' => 'Pencairan ini sudah dibatalkan.']);
            }
            $amount = (int) $detail->nominal;
            if ($amount <= 0 || (int) $account->total_keluar < $amount) {
                throw ValidationException::withMessages(['payout' => 'Data pengeluaran tidak konsisten. Pembatalan tidak dapat diproses.']);
            }
            $cancellation = UjDitahanPayoutCancellation::create(['fund' => $fund, 'detail_id' => $detail->id, 'account_id' => $account->id, 'nominal' => $amount, 'reason' => trim($reason), 'cancelled_by' => $actor->id]);
            $reversal = $detailClass::create([
                $foreign => $account->id,
                'jenis' => 'masuk', 'nominal' => $amount, 'payout_origin' => 'payout_reversal',
                'driver_id' => $detail->driver_id,
                'keterangan' => mb_substr('Pembatalan pencairan #'.$detail->id.' — '.trim($reason), 0, 255),
            ]);
            $cancellation->update(['reversal_detail_id' => $reversal->id]);
            $account->update(['saldo' => (int) $account->saldo + $amount, 'total_masuk' => (int) $account->total_masuk + $amount]);

            return $cancellation;
        }, 3);
    }
}
