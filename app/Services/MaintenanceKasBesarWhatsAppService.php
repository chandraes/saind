<?php

namespace App\Services;

use App\Models\GroupWa;
use App\Models\KasBesar;
use App\Models\Vendor;
use RuntimeException;
use Throwable;

class MaintenanceKasBesarWhatsAppService
{
    public function notify(KasBesar $cash, string $maintenance, ?Vendor $vendor = null): void
    {
        $group = GroupWa::where('untuk', 'kas-besar')->first();
        $body = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
            '*FORM PENGGANTIAN '.$maintenance."*\n".
            "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
            'Uraian :  '.$cash->uraian."\n".
            'Nilai :  *Rp. '.number_format($cash->nominal_transaksi, 0, ',', '.')."*\n\n".
            "Ditransfer ke rek:\n\n".
            'Bank     : '.$cash->bank."\n".
            'Nama    : '.$cash->transfer_ke."\n".
            'No. Rek : '.$cash->no_rekening."\n\n";
        $balances = "==========================\n".
            "Sisa Saldo Kas Besar : \n".
            'Rp. '.number_format($cash->saldo, 0, ',', '.')."\n\n".
            "Total Modal Investor : \n".
            'Rp. '.number_format($cash->modal_investor_terakhir, 0, ',', '.')."\n\n";
        $thanks = "Terima kasih 🙏🙏🙏\n";
        $failure = null;
        $messages = [];
        if ($group && filled($group->nama_group)) {
            $messages[] = ['to' => $group->nama_group, 'text' => $body.$balances.$thanks];
        } else {
            $failure = new RuntimeException('Grup WA Kas Besar belum dikonfigurasi.');
        }
        $phone = WhatsAppPhoneNumber::normalize($vendor?->no_hp);
        if ($phone !== null) {
            $messages[] = ['to' => $phone, 'text' => $body.$thanks];
        }
        foreach ($messages as $message) {
            try {
                if (app(GroupWa::class)->sendWa($message['to'], $message['text']) === false) {
                    throw new RuntimeException('Sebagian notifikasi maintenance Kas Besar belum diterima provider WA.');
                }
            } catch (Throwable $exception) {
                report($exception);
                $failure ??= $exception;
            }
        }
        if ($failure) {
            throw $failure;
        }
    }
}
