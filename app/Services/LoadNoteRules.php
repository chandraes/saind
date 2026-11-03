<?php

namespace App\Services;

use App\Exceptions\LoadNoteRuleException;
use App\Models\LoadNoteDraft;
use App\Models\Vehicle;

class LoadNoteRules
{
    public function assertVehicle(Vehicle $vehicle, ?int $exceptNote = null): void
    {
        if (! in_array($vehicle->status, config('mobile.load_note_rules.allowed_vehicle_statuses', ['aktif']), true)) {
            throw new LoadNoteRuleException('vehicle_id', 'Kendaraan tidak aktif atau masih dalam proses. Tunggu proses sebelumnya selesai.', 'vehicle_unavailable', 'wait');
        }
        $limit = max(1, (int) config('mobile.load_note_rules.max_do_count', 2));
        if ((int) $vehicle->do_count >= $limit) {
            throw new LoadNoteRuleException('vehicle_id', 'Kendaraan telah mencapai batas DO ('.$limit.'). Selesaikan DO sebelumnya.', 'do_limit_reached', 'wait');
        }
        if (LoadNoteDraft::where('vehicle_id', $vehicle->id)
            ->whereIn('status', config('mobile.load_note_rules.blocking_note_statuses', ['draft']))
            ->when($exceptNote !== null, fn ($query) => $query->whereKeyNot($exceptNote))->exists()) {
            throw new LoadNoteRuleException('vehicle_id', 'Kendaraan masih memiliki nota yang menunggu diproses. Tunggu pemeriksaan nota sebelumnya.', 'pending_note', 'wait');
        }
    }

    public function assertTicket(int $vendorId, string $ticket): void
    {
        if (LoadNoteDraft::where('vendor_id', $vendorId)->where('ticket_number', trim($ticket))
            ->whereNotIn('status', ['rejected', 'cancelled'])->exists()) {
            throw new LoadNoteRuleException('ticket_number', 'Nomor nota sudah pernah dikirim. Periksa daftar nota.', 'duplicate_note', 'view_notes');
        }
    }
}
