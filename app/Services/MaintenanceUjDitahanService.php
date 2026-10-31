<?php

namespace App\Services;

use App\Models\AkiGantiInvoice;
use App\Models\FilterOliGantiInvoice;
use App\Models\UjDitahan;
use App\Models\UjDitahanDetail;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MaintenanceUjDitahanService
{
    public function balance(int $vehicleId): int
    {
        return (int) UjDitahan::where('vehicle_id', $vehicleId)->where('saldo', '>', 0)->sum('saldo');
    }

    /** @return Collection<int, UjDitahan> */
    private function lockedBalances(int $vehicleId, int $nominal): Collection
    {
        Vehicle::whereKey($vehicleId)->lockForUpdate()->firstOrFail();
        $balances = UjDitahan::where('vehicle_id', $vehicleId)->where('saldo', '>', 0)
            ->orderBy('tahun')->orderBy('bulan')->orderBy('id')->lockForUpdate()->get();
        if ($nominal <= 0 || (int) $balances->sum('saldo') < $nominal) {
            throw ValidationException::withMessages(['total_nominal' => 'Saldo UJ Ditahan kendaraan tidak mencukupi. Tersedia Rp '.number_format((int) $balances->sum('saldo'), 0, ',', '.').'.']);
        }

        return $balances;
    }

    public function assertAvailable(int $vehicleId, int $nominal): void
    {
        DB::transaction(fn () => $this->lockedBalances($vehicleId, $nominal));
    }

    public function debit(AkiGantiInvoice|FilterOliGantiInvoice $invoice): void
    {
        DB::transaction(function () use ($invoice): void {
            $remaining = (int) $invoice->total_nominal;
            $balances = $this->lockedBalances($invoice->vehicle_id, $remaining);
            $reference = $invoice instanceof AkiGantiInvoice ? 'aki_ganti_invoice_id' : 'filter_oli_ganti_invoice_id';
            if (UjDitahanDetail::where($reference, $invoice->id)->exists()) {
                throw ValidationException::withMessages(['invoice' => 'Pembayaran invoice ini sudah dicatat.']);
            }
            foreach ($balances as $balance) {
                $amount = min($remaining, (int) $balance->saldo);
                UjDitahanDetail::create([
                    'uj_ditahan_id' => $balance->id, $reference => $invoice->id,
                    'jenis' => 'keluar', 'nominal' => $amount,
                    'keterangan' => 'Pembayaran maintenance '.$invoice->no_invoice,
                    'bank' => $invoice->nama_bank, 'no_rekening' => $invoice->nomor_rekening,
                    'nama_rekening' => $invoice->nama_rekening,
                ]);
                $balance->update(['saldo' => (int) $balance->saldo - $amount, 'total_keluar' => (int) $balance->total_keluar + $amount]);
                $remaining -= $amount;
                if ($remaining === 0) {
                    break;
                }
            }
        });
    }

    public function notify(AkiGantiInvoice|FilterOliGantiInvoice $invoice): void
    {
        app(UjDitahanWhatsAppService::class)->maintenance($invoice);
    }
}
