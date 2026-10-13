<?php

namespace App\Observers;

use App\Models\Karyawan;
use App\Services\AttendanceConnector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class KaryawanAttendanceObserver
{
    public function __construct(private AttendanceConnector $connector) {}

    public function saved(Karyawan $employee): void
    {
        $this->capture($employee);
    }

    public function deleted(Karyawan $employee): void
    {
        $this->capture($employee, true);
    }

    private function capture(Karyawan $employee, bool $deleted = false): void
    {
        if (! config('attendance.enabled')) {
            return;
        }
        $sync = $this->connector->capture($employee, $deleted);
        if (config('attendance.send_immediately')) {
            DB::afterCommit(function () use ($sync): void {
                try {
                    $this->connector->send($sync->id);
                } catch (Throwable) {
                    Log::warning('Pengiriman absensi tertunda; akan diproses ulang.', ['sync_id' => $sync->id]);
                }
            });
        }
    }
}
