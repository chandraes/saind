<?php

namespace App\Services;

use App\Exceptions\LoadNoteRuleException;
use App\Models\LoadNoteDraft;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

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

    /**
     * Nomor nota muat harus unik di seluruh sistem, sejalan dengan indeks UNIQUE
     * `transaksis.nota_muat`. Pembandingan mengabaikan huruf besar/kecil dan spasi
     * agar hasil OCR seperti "sjb 001" tetap dikenali sama dengan "SJB001".
     */
    public function assertTicket(int $vendorId, string $ticket, ?int $exceptNote = null): void
    {
        $normalized = self::normalizeTicket($ticket);
        if ($normalized === '') {
            return;
        }

        // Hanya nota yang masih menunggu pemeriksaan. Nota yang sudah diproses
        // tercakup oleh transaksis.nota_muat, yang dikosongkan saat transaksi
        // di-void sehingga nomor nota dapat dipakai ulang.
        $pendingDrafts = fn () => LoadNoteDraft::query()
            ->where('status', 'draft')
            ->whereRaw(self::normalizedSql('ticket_number').' = ?', [$normalized])
            ->when($exceptNote !== null, fn ($query) => $query->whereKeyNot($exceptNote));
        $transactions = fn () => DB::table('transaksis')
            ->whereNotNull('transaksis.nota_muat')
            ->whereRaw(self::normalizedSql('transaksis.nota_muat').' = ?', [$normalized]);

        $ownTransaction = fn () => $transactions()
            ->join('kas_uang_jalans', 'transaksis.kas_uang_jalan_id', '=', 'kas_uang_jalans.id')
            ->where('kas_uang_jalans.vendor_id', $vendorId)->exists();
        if ($pendingDrafts()->where('vendor_id', $vendorId)->exists() || $ownTransaction()) {
            throw new LoadNoteRuleException('ticket_number', 'Nomor nota sudah pernah dikirim. Periksa daftar nota.', 'duplicate_note', 'view_notes');
        }

        // Nota yang diinput lewat web atau dipakai vendor lain: pesan sengaja
        // tidak menyebut vendor/transaksi pemiliknya.
        if ($transactions()->exists() || $pendingDrafts()->exists()) {
            throw new LoadNoteRuleException('ticket_number', 'Nomor nota muat sudah terdaftar di sistem. Periksa kembali nomor nota atau hubungi admin.', 'duplicate_note', 'contact_admin');
        }
    }

    public static function normalizeTicket(string $ticket): string
    {
        return mb_strtoupper((string) preg_replace('/\s+/u', '', $ticket));
    }

    private static function normalizedSql(string $column): string
    {
        $stripped = $column;
        foreach (["' '", 'CHAR(9)', 'CHAR(10)', 'CHAR(13)'] as $whitespace) {
            $stripped = "REPLACE({$stripped}, {$whitespace}, '')";
        }

        return "UPPER({$stripped})";
    }
}
