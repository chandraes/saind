<?php

namespace App\Services;

use App\Models\LoadNoteDraft;
use App\Models\Transaksi;
use App\Models\UjDitahanBanDetail;
use App\Models\UjDitahanDetail;

class MobileNotePayment
{
    public function __construct(private LoadNotePaymentPreview $preview) {}

    /** @return array{kind: string, currency: string, gross_amount: int, sparepart_withheld: int, tyre_withheld: int, net_amount: int}|null */
    public function estimate(LoadNoteDraft $note): ?array
    {
        if ($note->status === 'processed') {
            return null;
        }
        $preview = $this->preview->forNote($note);

        return $this->breakdown('estimate', $preview['gross'], $preview['sparepart'], $preview['ban']);
    }

    /** @return array{kind: string, currency: string, gross_amount: int, sparepart_withheld: int, tyre_withheld: int, net_amount: int}|null */
    public function final(Transaksi $transaction): ?array
    {
        $gross = $transaction->kas_uang_jalan()->value('nominal_transaksi');
        $sparepart = UjDitahanDetail::where('transaksi_id', $transaction->id)->where('jenis', 'masuk')->sum('nominal');
        $tyre = UjDitahanBanDetail::where('transaksi_id', $transaction->id)->where('jenis', 'masuk')->sum('nominal');

        return $this->breakdown('final', $gross === null ? null : (int) $gross, (int) $sparepart, (int) $tyre);
    }

    /** @return array{kind: string, currency: string, gross_amount: int, sparepart_withheld: int, tyre_withheld: int, net_amount: int}|null */
    private function breakdown(string $kind, ?int $gross, ?int $sparepart, ?int $tyre): ?array
    {
        if ($gross === null || $sparepart === null || $tyre === null || min($gross, $sparepart, $tyre) < 0 || $gross < $sparepart + $tyre) {
            return null;
        }

        return ['kind' => $kind, 'currency' => 'IDR', 'gross_amount' => $gross,
            'sparepart_withheld' => $sparepart, 'tyre_withheld' => $tyre, 'net_amount' => $gross - $sparepart - $tyre];
    }
}
