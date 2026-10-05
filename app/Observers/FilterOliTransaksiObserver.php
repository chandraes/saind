<?php

namespace App\Observers;

use App\Models\Transaksi;
use App\Services\FilterOliRitaseService;

class FilterOliTransaksiObserver
{
    public function __construct(private FilterOliRitaseService $ritase) {}

    public function created(Transaksi $transaksi): void
    {
        $this->ritase->recordTransaction($transaksi);
    }

    public function updated(Transaksi $transaksi): void
    {
        if ($transaksi->wasChanged('created_at')) {
            $this->ritase->refreshForTransaction($transaksi);
        } elseif ($transaksi->wasChanged('void')) {
            if ($transaksi->void) {
                $this->ritase->rollbackTransaction($transaksi);
            } else {
                $this->ritase->recordTransaction($transaksi);
            }
        }
    }
}
