<?php

namespace App\Services;

use App\Models\BanGantiInvoice;
use App\Models\UjDitahanBan;
use App\Models\UjDitahanBanDetail;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MaintenanceBanUjDitahanService
{
    public function balance(int $vehicleId): int
    {
        return (int) UjDitahanBan::where('vehicle_id', $vehicleId)->where('saldo', '>', 0)->sum('saldo');
    }

    /** @return Collection<int, UjDitahanBan> */
    private function lockedBalances(int $vehicleId, int $nominal): Collection
    {
        Vehicle::whereKey($vehicleId)->lockForUpdate()->firstOrFail();
        $balances = UjDitahanBan::where('vehicle_id', $vehicleId)->where('saldo', '>', 0)
            ->orderBy('tahun')->orderBy('bulan')->orderBy('id')->lockForUpdate()->get();
        if ($nominal <= 0 || $balances->sum('saldo') < $nominal) {
            throw ValidationException::withMessages(['total_nominal' => 'Saldo UJ Ditahan Ban tidak mencukupi. Tersedia Rp '.number_format((int) $balances->sum('saldo'), 0, ',', '.').'.']);
        }

        return $balances;
    }

    public function assertAvailable(int $vehicleId, int $nominal): void
    {
        DB::transaction(fn () => $this->lockedBalances($vehicleId, $nominal));
    }

    public function debit(BanGantiInvoice $invoice): void
    {
        DB::transaction(function () use ($invoice): void {
            foreach (['nama_bank', 'nomor_rekening', 'nama_rekening'] as $field) {
                if (trim((string) $invoice->{$field}) === '') {
                    throw ValidationException::withMessages(['rekening' => 'Rekening tujuan pembayaran ban wajib lengkap.']);
                }
            }
            $remaining = (int) $invoice->total_nominal;
            $balances = $this->lockedBalances($invoice->vehicle_id, $remaining);
            if (UjDitahanBanDetail::where('ban_ganti_invoice_id', $invoice->id)->exists()) {
                throw ValidationException::withMessages(['invoice' => 'Pembayaran invoice ini sudah dicatat.']);
            }
            foreach ($balances as $balance) {
                $amount = min($remaining, $balance->saldo);
                $balance->details()->create([
                    'ban_ganti_invoice_id' => $invoice->id, 'jenis' => 'keluar', 'nominal' => $amount,
                    'keterangan' => 'Pembayaran ban luar '.$invoice->no_invoice,
                    'bank' => $invoice->nama_bank, 'no_rekening' => $invoice->nomor_rekening,
                    'nama_rekening' => $invoice->nama_rekening,
                ]);
                $balance->update(['saldo' => $balance->saldo - $amount, 'total_keluar' => $balance->total_keluar + $amount]);
                $remaining -= $amount;
                if ($remaining === 0) {
                    break;
                }
            }
        });
    }

    public function notify(BanGantiInvoice $invoice): void
    {
        app(UjDitahanWhatsAppService::class)->maintenance($invoice);
    }
}
