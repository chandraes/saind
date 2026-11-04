<?php

namespace App\Services;

use App\Models\LoadNoteDraft;
use App\Models\Vehicle;
use App\Models\VendorUangJalan;

class LoadNotePaymentPreview
{
    /**
     * Current settings only; reading a note never reserves or pays funds.
     *
     * @return array{is_custom: bool, system_transfer: ?int, held: bool, transfer: ?int, sparepart: ?int, ban: ?int, gross: ?int, driver: ?string, driver_phone: ?string, account_source: string, recipient: ?string, bank: ?string, account: ?string, warnings: list<string>}
     */
    public function forNote(LoadNoteDraft $note): array
    {
        $vehicle = Vehicle::with('driver')->whereKey($note->vehicle_id)->where('vendor_id', $note->vendor_id)->first();
        $held = (int) $vehicle?->uj_ditahan === 1;
        $driver = $vehicle?->driver;
        $useDriverAccount = $held && $driver !== null;
        $preview = ['is_custom' => $note->transfer_override !== null, 'system_transfer' => null, 'held' => $held, 'transfer' => null, 'sparepart' => $held ? null : 0, 'ban' => $held ? null : 0,
            'gross' => null, 'driver' => $driver?->nama, 'driver_phone' => $driver?->no_hp,
            'account_source' => $useDriverAccount ? 'driver' : 'vehicle',
            'recipient' => $useDriverAccount ? $driver->nama_rek : $vehicle?->transfer_ke,
            'bank' => $useDriverAccount ? $driver->bank : $vehicle?->bank,
            'account' => $useDriverAccount ? $driver->no_rek : $vehicle?->no_rekening, 'warnings' => []];
        if (! $vehicle) {
            $preview['warnings'][] = 'Unit tidak ditemukan pada vendor nota. Periksa kembali data kendaraan.';

            return $preview;
        }
        if (! $note->rute_id) {
            $preview['warnings'][] = 'Pilih rute pada nota terlebih dahulu untuk melihat estimasi uang jalan.';

            return $preview;
        }
        $agreement = VendorUangJalan::where('vendor_id', $note->vendor_id)->where('rute_id', $note->rute_id)->first();
        if (! $agreement) {
            $preview['warnings'][] = 'Kesepakatan uang jalan vendor–rute belum diatur. Hubungi admin.';

            return $preview;
        }
        // The existing exit form treats hk_uang_jalan as the transfer, before adding held funds.
        $preview['system_transfer'] = $agreement->hk_uang_jalan !== null && (int) $agreement->hk_uang_jalan >= 0 ? (int) $agreement->hk_uang_jalan : null;
        $preview['transfer'] = $note->transfer_override ?? $preview['system_transfer'];
        if ($preview['transfer'] === null) {
            $preview['warnings'][] = 'Nominal uang jalan vendor–rute belum diatur. Hubungi admin.';

            return $preview;
        }
        if ($held) {
            foreach (['sparepart' => ['uj_ditahan', 'Sparepart'], 'ban' => ['uj_ditahan_ban', 'Ban']] as $key => [$column, $label]) {
                if ((int) $agreement->{$column} <= 0) {
                    $preview['warnings'][] = 'Nominal UJ Ditahan '.$label.' belum diatur pada vendor–rute ini.';
                } else {
                    $preview[$key] = (int) $agreement->{$column};
                }
            }
        }
        if ($preview['sparepart'] !== null && $preview['ban'] !== null) {
            $preview['gross'] = $preview['transfer'] + $preview['sparepart'] + $preview['ban'];
        }

        return $preview;
    }
}
