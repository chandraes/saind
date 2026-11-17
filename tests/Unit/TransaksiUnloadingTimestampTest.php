<?php

namespace Tests\Unit;

use App\Models\Transaksi;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

/** tanggal_bongkar = tanggal pada form + jam saat disimpan; edit tanpa ganti tanggal mempertahankan jam. */
class TransaksiUnloadingTimestampTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_new_unloading_uses_form_date_with_save_time(): void
    {
        Carbon::setTestNow('2026-11-17 15:42:09');

        $this->assertSame('2026-11-17 15:42:09', Transaksi::unloadingTimestamp('17-11-2026'));
        // Input terlambat: tanggal dari form, jam saat disimpan.
        $this->assertSame('2026-11-15 15:42:09', Transaksi::unloadingTimestamp('15-11-2026'));
    }

    public function test_edit_keeps_time_when_date_is_unchanged_and_resets_it_otherwise(): void
    {
        Carbon::setTestNow('2026-11-17 08:00:00');

        $this->assertSame('2026-11-10 13:05:00', Transaksi::unloadingTimestamp('10-11-2026', '2026-11-10 13:05:00'));
        $this->assertSame('2026-11-11 08:00:00', Transaksi::unloadingTimestamp('11-11-2026', '2026-11-10 13:05:00'));
        // Data lama (jam 00:00) yang tidak diubah tanggalnya tetap 00:00.
        $this->assertSame('2026-11-10 00:00:00', Transaksi::unloadingTimestamp('10-11-2026', '2026-11-10'));
    }

    public function test_date_only_strips_the_time(): void
    {
        $this->assertSame('2026-11-04', Transaksi::dateOnly('2026-11-04 14:35:12'));
        $this->assertSame('2026-11-04', Transaksi::dateOnly('2026-11-04'));
        $this->assertNull(Transaksi::dateOnly(null));
    }
}
