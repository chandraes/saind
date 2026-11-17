<?php

namespace App\Console\Commands;

use App\Services\ReminderRefresher;
use App\Support\ReminderRegistry;
use Illuminate\Console\Command;

class RefreshReminders extends Command
{
    protected $signature = 'reminders:refresh {types?* : standby, unloading, filter_oil, invoice_due, attendance (kosong = semua)}';

    protected $description = 'Rekap ulang Dashboard Pengingat (dijadwalkan sesuai REMINDERS_REFRESH)';

    public function handle(ReminderRefresher $refresher): int
    {
        $types = $this->argument('types') ?: null;
        if ($types !== null && array_diff($types, ReminderRegistry::keys()) !== []) {
            $this->error('Jenis pengingat tidak dikenal. Pilihan: '.implode(', ', ReminderRegistry::keys()));

            return self::INVALID;
        }
        foreach ($refresher->refresh($types) as $type => $count) {
            $this->line("{$type}: {$count}");
        }

        return self::SUCCESS;
    }
}
