<?php

namespace App\Console\Commands;

use App\Services\AttendanceConnector;
use Illuminate\Console\Command;

class AttendanceSyncEmployees extends Command
{
    protected $signature = 'attendance:sync-employees {--send : Kirim antrean setelah rekonsiliasi} {--force : Buat versi baru walaupun payload sama}';

    protected $description = 'Siapkan sinkronisasi seluruh pegawai SAIND dan penonaktifan pegawai terhapus';

    public function handle(AttendanceConnector $connector): int
    {
        if (! config('attendance.enabled')) {
            $this->error('Integrasi absensi belum diaktifkan.');

            return self::FAILURE;
        }
        $count = $connector->reconcile((bool) $this->option('force'));
        $this->info($count.' pegawai diperiksa.');
        if ($this->option('send')) {
            if (! $connector->configured()) {
                $this->error('URL HTTPS dan token integrasi belum lengkap.');

                return self::FAILURE;
            }
            $result = $connector->flush(100);
            $this->info($result['sent'].' terkirim; '.$result['pending'].' menunggu.');

            return $result['pending'] > 0 ? self::FAILURE : self::SUCCESS;
        }

        return self::SUCCESS;
    }
}
