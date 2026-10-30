<?php

namespace App\Services;

use App\Models\AkiGantiInvoice;
use App\Models\FilterOliGantiInvoice;
use App\Models\GroupWa;
use App\Models\UjDitahan;
use App\Models\UjDitahanBan;
use App\Models\UjDitahanDetail;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

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
        $invoice->loadMissing('vehicle.driver');
        $vehicle = $invoice->vehicle;
        $sparepart = (int) UjDitahan::where('vehicle_id', $invoice->vehicle_id)->sum('saldo');
        $ban = (int) UjDitahanBan::where('vehicle_id', $invoice->vehicle_id)->sum('saldo');
        $maintenance = $invoice instanceof AkiGantiInvoice ? 'AKI' : 'FILTER & OLI MESIN';
        $text = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
            '*FORM PENGGANTIAN '.$maintenance."*\n".
            "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
            'Invoice : '.$invoice->no_invoice."\n".
            'Uraian : Penggantian '.$maintenance.' Unit '.$vehicle->nomor_lambung."\n".
            "Pembayaran : UJ Ditahan Sparepart\n".
            'Nilai : *Rp. '.number_format((int) $invoice->total_nominal, 0, ',', '.')."*\n\n".
            "Ditransfer ke rek:\n\n".
            'Bank     : '.$invoice->nama_bank."\n".
            'Nama    : '.$invoice->nama_rekening."\n".
            'No. Rek : '.$invoice->nomor_rekening."\n\n".
            "==========================\n".
            'Total UJ Ditahan Sparepart: '.$vehicle->nomor_lambung."\n".
            'Rp. '.number_format($sparepart, 0, ',', '.')."\n\n".
            'Total UJ Ditahan Ban: '.$vehicle->nomor_lambung."\n".
            'Rp. '.number_format($ban, 0, ',', '.')."\n\n".
            'Grand Total UJ Ditahan: '.$vehicle->nomor_lambung."\n".
            'Rp. '.number_format($sparepart + $ban, 0, ',', '.')."\n\n".
            "Terima kasih 🙏🙏🙏\n";
        $messenger = app(GroupWa::class);
        $group = GroupWa::where('untuk', 'kas-uj-ditahan')->first();
        $failure = null;
        $recipients = [];
        if ($group && filled($group->nama_group)) {
            $recipients[] = $group->nama_group;
        } else {
            $failure = new RuntimeException('Grup WA UJ Ditahan belum dikonfigurasi.');
        }
        $phone = $this->driverPhone($vehicle->driver?->no_hp);
        if ($phone !== null) {
            $recipients[] = $phone;
        }
        foreach (array_unique($recipients) as $recipient) {
            try {
                $messenger->sendWa($recipient, $text);
            } catch (Throwable $exception) {
                report($exception);
                $failure ??= $exception;
            }
        }
        if ($failure) {
            throw $failure;
        }
    }

    private function driverPhone(?string $value): ?string
    {
        $phone = preg_replace('/[\s().-]+/', '', trim($value ?? ''));
        if (! preg_match('/^(?:\+?62|0)8[1-9][0-9]{7,10}$/D', $phone)) {
            return null;
        }

        return ltrim($phone, '+');
    }
}
