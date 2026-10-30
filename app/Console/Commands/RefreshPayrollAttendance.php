<?php

namespace App\Console\Commands;

use App\Services\PayrollAttendanceService;
use App\Services\PayrollService;
use Illuminate\Console\Command;
use Throwable;

class RefreshPayrollAttendance extends Command
{
    protected $signature = 'attendance:refresh-payroll';

    protected $description = 'Perbarui snapshot periode gaji terbuka tanpa memproses pembayaran';

    public function handle(PayrollService $payroll, PayrollAttendanceService $attendance): int
    {
        $attendance->clear();
        if (! $attendance->enabled() || ! config('attendance.payroll_auto_sync')) {
            $this->info('Pembaruan otomatis nonaktif.');

            return self::SUCCESS;
        }
        $period = $payroll->nextPeriod();
        if ($period->gt(now()->startOfMonth()->addMonth())) {
            $this->info('Tidak ada periode preview gaji yang terbuka.');

            return self::SUCCESS;
        }
        $sources = $payroll->employeesQueryForPeriod($period->month, $period->year)->withoutEagerLoads()->pluck('id')->all();
        try {
            $updated = $attendance->refresh($period->month, $period->year, $sources, 'automatic');
            $this->info($updated ? 'Snapshot '.$period->format('Y-m').' diperbarui; gaji tidak diproses.' : 'Dilewati: belum waktunya, proses lain berjalan, atau periode terkunci.');

            return self::SUCCESS;
        } catch (Throwable) {
            $this->error('Snapshot belum berhasil diperbarui. Lihat status pada form gaji; retry dijadwalkan.');

            return self::FAILURE;
        }
    }
}
