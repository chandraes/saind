<?php

namespace App\Services;

use App\Models\LoadNoteDraft;
use App\Models\Transaksi;
use App\Models\UjDitahanBanDetail;
use App\Models\UjDitahanDetail;
use Illuminate\Database\Eloquent\Builder;

class MobileNotePayment
{
    public function __construct(private LoadNotePaymentPreview $preview, private TransferDestination $destinations) {}

    /** @return array{kind: string, currency: string, gross_amount: int, sparepart_withheld: int, tyre_withheld: int, net_amount: int}|null */
    public function estimate(LoadNoteDraft $note): ?array
    {
        return $this->estimateDetail($note)['payment'];
    }

    /** @return array{payment: ?array, transfer_destination: array} */
    public function estimateDetail(LoadNoteDraft $note): array
    {
        if ($note->status === 'processed') {
            $transaction = $note->transaksi_id ? Transaksi::whereKey($note->transaksi_id)
                ->whereHas('kas_uang_jalan', fn (Builder $query): Builder => $query->where('vendor_id', $note->vendor_id))->first() : null;

            return ['payment' => null, 'transfer_destination' => $this->destinations->forJourney($transaction?->kas_uang_jalan)];
        }
        $preview = $this->preview->forNote($note);

        return ['payment' => $this->breakdown('estimate', $preview['gross'], $preview['sparepart'], $preview['ban']),
            'transfer_destination' => $preview['transfer_destination']];
    }

    /** @return array{kind: string, currency: string, gross_amount: int, sparepart_withheld: int, tyre_withheld: int, net_amount: int}|null */
    public function final(Transaksi $transaction): ?array
    {
        return $this->finalDetail($transaction)['payment'];
    }

    /** @return array{payment: ?array, transfer_destination: array} */
    public function finalDetail(Transaksi $transaction): array
    {
        $journey = $transaction->kas_uang_jalan()->first();
        $gross = $journey?->nominal_transaksi;
        $sparepart = UjDitahanDetail::where('transaksi_id', $transaction->id)->where('jenis', 'masuk')->sum('nominal');
        $tyre = UjDitahanBanDetail::where('transaksi_id', $transaction->id)->where('jenis', 'masuk')->sum('nominal');

        return ['payment' => $this->breakdown('final', $gross === null ? null : (int) $gross, (int) $sparepart, (int) $tyre),
            'transfer_destination' => $this->destinations->forJourney($journey)];
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
