<?php

namespace App\Services;

use App\Models\AkiGantiInvoice;
use App\Models\BanGantiInvoice;
use App\Models\FilterOliGantiInvoice;
use App\Models\GroupWa;
use App\Models\UjDitahan;
use App\Models\UjDitahanBan;
use App\Models\UjDitahanBanDetail;
use App\Models\UjDitahanDetail;
use App\Models\Vehicle;
use RuntimeException;
use Throwable;

class UjDitahanWhatsAppService
{
    public function maintenance(AkiGantiInvoice|FilterOliGantiInvoice|BanGantiInvoice $invoice): void
    {
        $invoice->loadMissing('vehicle.driver');
        $vehicle = $invoice->vehicle;
        $sparepart = (int) UjDitahan::where('vehicle_id', $invoice->vehicle_id)->sum('saldo');
        $ban = (int) UjDitahanBan::where('vehicle_id', $invoice->vehicle_id)->sum('saldo');
        $maintenance = match (true) {
            $invoice instanceof AkiGantiInvoice => 'AKI',
            $invoice instanceof BanGantiInvoice => 'BAN LUAR',
            default => 'FILTER & OLI MESIN',
        };
        $fund = $invoice instanceof BanGantiInvoice ? 'Ban' : 'Sparepart';
        $text = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
            '*FORM PENGGANTIAN '.$maintenance."*\n".
            "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
            'Invoice : '.$invoice->no_invoice."\n".
            'Uraian : Penggantian '.$maintenance.' Unit '.$vehicle->nomor_lambung."\n".
            'Pembayaran : UJ Ditahan '.$fund."\n".
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
        $this->sendToGroupAndDriver($vehicle, $text);
    }

    public function payout(UjDitahan|UjDitahanBan $account, UjDitahanDetail|UjDitahanBanDetail $detail): void
    {
        $account->loadMissing('vehicle.driver');
        $vehicle = $account->vehicle;
        $sparepart = UjDitahan::where('saldo', '>', 0);
        $ban = UjDitahanBan::where('saldo', '>', 0);
        $grandTotal = (int) (clone $sparepart)->sum('saldo') + (int) (clone $ban)->sum('saldo');
        $vehicleSparepart = (int) $sparepart->where('vehicle_id', $vehicle->id)->sum('saldo');
        $vehicleBan = (int) $ban->where('vehicle_id', $vehicle->id)->sum('saldo');
        $fund = $account instanceof UjDitahanBan ? 'Ban' : 'Sparepart';
        $body = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
            '*Form Pencairan UJ Ditahan '.$fund."*\n".
            "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
            'Nomor Lambung : '.$vehicle->nomor_lambung."\n".
            'Periode : '.sprintf('%02d/%d', $account->bulan, $account->tahun)."\n".
            'Uraian : '.$detail->keterangan."\n\n".
            'Nilai :  *Rp. '.number_format((int) $detail->nominal, 0, ',', '.').",-*\n\n".
            "Ditransfer ke rek:\n\n".
            'Bank     : '.$detail->bank."\n".
            'Nama    : '.$detail->nama_rekening."\n".
            'No. Rek : '.$detail->no_rekening."\n\n".
            "==========================\n".
            'Total Saldo UJ Ditahan Sparepart: '.$vehicle->nomor_lambung."\n".
            'Rp. '.number_format($vehicleSparepart, 0, ',', '.')."\n\n".
            'Total Saldo UJ Ditahan Ban: '.$vehicle->nomor_lambung."\n".
            'Rp. '.number_format($vehicleBan, 0, ',', '.')."\n\n";
        $footer = fn (int $total): string => "Grand Total UJ Ditahan :\nRp. ".number_format($total, 0, ',', '.')."\n\nTerima kasih 🙏🙏🙏\n";
        $this->sendToGroupAndDriver($vehicle, $body.$footer($grandTotal), $body.$footer($vehicleSparepart + $vehicleBan));
    }

    public function sendToGroupAndDriver(Vehicle $vehicle, string $groupText, ?string $driverText = null): void
    {
        $vehicle->loadMissing('driver');
        $messenger = app(GroupWa::class);
        $group = GroupWa::where('untuk', 'kas-uj-ditahan')->first();
        $failure = null;
        $messages = [];
        if ($group && filled($group->nama_group)) {
            $messages[] = ['to' => $group->nama_group, 'text' => $groupText];
        } else {
            $failure = new RuntimeException('Grup WA UJ Ditahan belum dikonfigurasi.');
        }
        $phone = $this->driverPhone($vehicle->driver?->no_hp);
        if ($phone !== null) {
            $messages[] = ['to' => $phone, 'text' => $driverText ?? $groupText];
        }
        foreach ($messages as $message) {
            try {
                $messenger->sendWa($message['to'], $message['text']);
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
