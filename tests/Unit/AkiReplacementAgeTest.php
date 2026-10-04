<?php

namespace Tests\Unit;

use App\Models\AkiGantiInvoiceDetail;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class AkiReplacementAgeTest extends TestCase
{
    public function test_age_is_measured_at_replacement_in_calendar_days(): void
    {
        $installedAt = Carbon::parse('2025-10-04 23:59:59', 'Asia/Jakarta');
        $replacedAt = Carbon::parse('2026-10-03 01:00:00', 'Asia/Jakarta');

        $this->assertSame(364, AkiGantiInvoiceDetail::usageDays($installedAt, $replacedAt));
        $this->assertSame('23:59:59', $installedAt->format('H:i:s'));
        $this->assertSame(365, AkiGantiInvoiceDetail::usageDays($installedAt, $replacedAt->copy()->addDay()));
        $this->assertSame(366, AkiGantiInvoiceDetail::usageDays($installedAt, $replacedAt->copy()->addDays(2)));
    }

    public function test_same_day_replacement_has_zero_days(): void
    {
        $this->assertSame(0, AkiGantiInvoiceDetail::usageDays(
            Carbon::parse('2026-10-04 08:00:00'),
            Carbon::parse('2026-10-04 16:00:00'),
        ));
    }

    public function test_missing_or_later_history_has_no_known_age(): void
    {
        $replacedAt = Carbon::parse('2026-10-04');

        $this->assertNull(AkiGantiInvoiceDetail::usageDays(null, $replacedAt));
        $this->assertNull(AkiGantiInvoiceDetail::usageDays($replacedAt->copy()->addDay(), $replacedAt));
    }
}
