<?php

namespace App\Observers;

use App\Models\Transaksi;
use App\Services\FilterOliRitaseService;

class FilterOliTransaksiObserver
{
    public function __construct(private FilterOliRitaseService $ritase) {}

    public function created(Transaksi $transaksi): void
    {
        $this->ritase->refreshForTransaction($transaksi);
    }

    public function updated(Transaksi $transaksi): void
    {
        if ($transaksi->wasChanged(['void', 'created_at'])) {
            $this->ritase->refreshForTransaction($transaksi);
        }
    }
}
