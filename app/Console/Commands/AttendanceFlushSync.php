<?php

namespace App\Console\Commands;

use App\Services\AttendanceConnector;
use Illuminate\Console\Command;

class AttendanceFlushSync extends Command
{
    protected $signature = 'attendance:flush {--limit=50} {--retry-now : Abaikan waktu backoff untuk percobaan manual}';

    protected $description = 'Kirim ulang antrean sinkronisasi pegawai ke aplikasi absensi';

    public function handle(AttendanceConnector $connector): int
    {
        if (! $connector->configured()) {
            $this->error('Integrasi absensi memerlukan konfigurasi URL HTTPS dan token.');

            return self::FAILURE;
        }
        $limit = filter_var($this->option('limit'), FILTER_VALIDATE_INT);
        if ($limit === false || $limit < 1 || $limit > 100) {
            $this->error('Limit harus 1–100.');

            return self::FAILURE;
        }
        $result = $connector->flush($limit, (bool) $this->option('retry-now'));
        $this->info($result['sent'].' terkirim; '.$result['pending'].' menunggu.');

        return $result['pending'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
