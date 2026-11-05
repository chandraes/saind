<?php

namespace App\Services;

use App\Models\KasUangJalan;
use App\Models\Vehicle;

class TransferDestination
{
    /** @return array{source: string, recipient_name: ?string, bank: ?string, account_number: ?string, complete: bool} */
    public function forVehicle(?Vehicle $vehicle): array
    {
        $driver = $vehicle?->driver;

        return $this->destination($driver ? 'driver' : 'vehicle', $driver ? $driver->nama_rek : $vehicle?->transfer_ke,
            $driver ? $driver->bank : $vehicle?->bank, $driver ? $driver->no_rek : $vehicle?->no_rekening);
    }

    /** @return array{source: string, recipient_name: ?string, bank: ?string, account_number: ?string, complete: bool} */
    public function forJourney(?KasUangJalan $journey): array
    {
        return $this->destination('transaction', $journey?->transfer_ke, $journey?->bank, $journey?->no_rekening);
    }

    /** @return array{source: string, recipient_name: ?string, bank: ?string, account_number: ?string, complete: bool} */
    private function destination(string $source, mixed $name, mixed $bank, mixed $account): array
    {
        $name = $this->text($name);
        $bank = $this->text($bank);
        $account = $this->text($account);

        return ['source' => $source, 'recipient_name' => $name, 'bank' => $bank, 'account_number' => $account,
            'complete' => $name !== null && $bank !== null && $account !== null];
    }

    private function text(mixed $value): ?string
    {
        $text = trim((string) $value);

        return in_array($text, ['', '-', '0'], true) ? null : $text;
    }
}
