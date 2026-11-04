<?php

namespace App\Services;

use App\Exceptions\LoadNoteRuleException;
use App\Models\Konfigurasi;
use Carbon\CarbonImmutable;

class LoadNoteDateRule
{
    public function assertMobileAllowed(string $date, string $field = 'loaded_on'): void
    {
        $this->assertAllowed($date, $field, (bool) config('mobile.load_note_rules.enforce_mobile_backdate', false));
    }

    public function assertAllowed(string $date, string $field = 'loaded_on', bool $enforceBackdate = true): void
    {
        $loadedOn = CarbonImmutable::createFromFormat('!Y-m-d', $date);
        if ($loadedOn === false || $loadedOn->format('Y-m-d') !== $date) {
            throw new LoadNoteRuleException($field, 'Tanggal StampOut tidak valid. Foto ulang nota.', 'invalid_date', 'retake_photo');
        }
        if (! $enforceBackdate) {
            return;
        }
        if ((int) Konfigurasi::where('kode', 'nota-muat')->value('status') !== 1) {
            return;
        }
        $days = max(0, (int) config('mobile.load_note_rules.backdate_days', 2));
        if ($loadedOn->lessThan(CarbonImmutable::today()->subDays($days))) {
            throw new LoadNoteRuleException($field, 'Tanggal muat tidak boleh lebih dari H-'.$days.' dari hari ini.', 'backdate_limit', 'contact_admin');
        }
    }
}
