<?php

namespace App\Services;

use App\Models\Karyawan;
use App\Models\RekapGaji;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class PayrollAttendanceService
{
    private array $periodCache = [];

    private ?bool $enabledCache = null;

    private array $payloadCache = [];

    private array $peopleCache = [];

    public function enabled(): bool
    {
        return $this->enabledCache ??= (bool) config('attendance.enabled', false) && Schema::hasTable('payroll_attendance_settings') && (bool) DB::table('payroll_attendance_settings')->where('id', 1)->value('enabled');
    }

    public function period(int $month, int $year): ?object
    {
        $key = $year.'-'.$month;
        if (! array_key_exists($key, $this->periodCache)) {
            $this->periodCache[$key] = Schema::hasTable('payroll_attendance_periods') ? DB::table('payroll_attendance_periods')->where('year', $year)->where('month', $month)->first() : null;
        }

        return $this->periodCache[$key];
    }

    public function payload(object $period): array
    {
        return $this->payloadCache[$period->id] ??= ($period->snapshot ? json_decode($period->snapshot, true, 512, JSON_THROW_ON_ERROR) : []);
    }

    public function clear(): void
    {
        $this->periodCache = [];
        $this->enabledCache = null;
        $this->payloadCache = [];
        $this->peopleCache = [];
    }

    public function dates(int $month, int $year): array
    {
        $end = Carbon::create($year, $month, 25)->startOfDay();

        return ['from' => $end->copy()->subMonthNoOverflow()->day(26)->toDateString(), 'to' => $end->toDateString()];
    }

    public function event(string $action, array $details, ?int $periodId = null): void
    {
        DB::table('payroll_attendance_events')->insert(['user_id' => auth()->id(), 'user_name' => auth()->user()?->name ?? 'Sistem', 'action' => $action, 'period_id' => $periodId, 'details' => json_encode($details, JSON_THROW_ON_ERROR), 'created_at' => now()]);
    }

    private function request(array $data): array
    {
        $connector = app(AttendanceConnector::class);
        if (! $connector->configured()) {
            throw ValidationException::withMessages(['attendance' => 'Integrasi absensi belum siap. Eksekusi gaji diblokir.']);
        }
        $client = Http::acceptJson()->withToken(config('attendance.token'))->connectTimeout(3)->timeout(30)->withoutRedirecting();
        if (config('attendance.ca_bundle')) {
            $client = $client->withOptions(['verify' => config('attendance.ca_bundle')]);
        }
        try {
            $response = $client->post(rtrim(config('attendance.url'), '/').'/api/v1/integration/payroll-snapshot', $data);
        } catch (ConnectionException) {
            throw ValidationException::withMessages(['attendance' => 'Koneksi absensi gagal. Gaji belum dieksekusi. Coba lagi dengan transaksi yang sama.']);
        }
        if (! $response->successful()) {
            if (! empty($data['request_id']) && $response->status() < 500) {
                DB::table('payroll_attendance_periods')->where('request_id', $data['request_id'])->update(['lock_attempted_at' => null]);
                $this->clear();
            }
            throw ValidationException::withMessages(['attendance' => $response->status() === 409 ? 'Data/periode absensi berubah atau terkunci. Perbarui data dan tinjau ulang.' : 'Snapshot absensi belum tersedia atau belum lengkap. Gaji diblokir; periksa integrasi dan persetujuan HR.']);
        }
        $snapshot = $response->json('data');
        if (! is_array($snapshot) || ($snapshot['schema_version'] ?? null) !== 1 || ! is_array($snapshot['employees'] ?? null) || ! is_array($snapshot['issues'] ?? null) || ! is_bool($snapshot['ready'] ?? null) || ! preg_match('/^[a-f0-9]{64}$/', $snapshot['revision'] ?? '') || ($snapshot['from'] ?? null) !== $data['from'] || ($snapshot['to'] ?? null) !== $data['to']) {
            throw ValidationException::withMessages(['attendance' => 'Format snapshot absensi tidak valid. Gaji diblokir.']);
        }
        if (($snapshot['monitoring_version'] ?? null) !== 1) {
            throw ValidationException::withMessages(['attendance' => 'Backend absensi belum mendukung pantauan berjalan. Perbarui backend sebelum menarik data gaji.']);
        }
        $expected = $data['source_employee_ids'];
        $received = $snapshot['source_employee_ids'] ?? [];
        sort($expected, SORT_STRING);
        sort($received, SORT_STRING);
        $actual = array_map(fn ($row): string => (string) ($row['source_employee_id'] ?? ''), $snapshot['employees']);
        sort($actual, SORT_STRING);
        if ($expected !== $received || $expected !== $actual) {
            throw ValidationException::withMessages(['attendance' => 'Daftar pegawai snapshot tidak lengkap atau tidak sesuai.']);
        }
        if (! empty($data['revision']) && (($snapshot['lock_request_id'] ?? null) !== $data['request_id'] || ! hash_equals($data['revision'], $snapshot['revision']))) {
            throw ValidationException::withMessages(['attendance' => 'Konfirmasi penguncian tidak sesuai. Jangan melanjutkan gaji.']);
        }
        $dates = [];
        for ($date = Carbon::parse($data['from']); $date->toDateString() <= $data['to']; $date = $date->addDay()) {
            $dates[] = $date->toDateString();
        }
        foreach ($snapshot['employees'] as $employee) {
            $dayDates = array_column($employee['days'] ?? [], 'date');
            if ($dayDates !== $dates || ! is_bool($employee['attendance_required'] ?? null)) {
                throw ValidationException::withMessages(['attendance' => 'Rincian tanggal/pegawai snapshot tidak lengkap.']);
            }
            foreach ($employee['days'] as $day) {
                if (! in_array($day['status'] ?? '', ['present', 'half_day', 'absent', 'excused', 'exempt', 'before_tracking', 'off', 'holiday', 'pending', 'invalid', 'upcoming', 'in_progress', 'pending_leave'], true)) {
                    throw ValidationException::withMessages(['attendance' => 'Status absensi tidak dikenal.']);
                }
                foreach (['scheduled_minutes', 'break_minutes', 'late_minutes', 'early_minutes'] as $field) {
                    if (! is_int($day[$field] ?? null) || $day[$field] < 0 || $day[$field] > 10080) {
                        throw ValidationException::withMessages(['attendance' => 'Rincian menit absensi tidak valid.']);
                    }
                }
                if ($snapshot['ready'] && (in_array($day['status'], ['pending', 'invalid', 'upcoming', 'in_progress', 'pending_leave'], true) || (in_array($day['status'], ['present', 'half_day', 'absent'], true) && $day['scheduled_minutes'] <= $day['break_minutes']))) {
                    throw ValidationException::withMessages(['attendance' => 'Snapshot belum siap untuk gaji.']);
                }
            }
        }
        if ($snapshot['ready'] && $snapshot['issues'] !== []) {
            throw ValidationException::withMessages(['attendance' => 'Snapshot memiliki masalah yang belum selesai.']);
        }

        return $snapshot;
    }

    public function refresh(int $month, int $year, array $sources, string $source = 'manual'): bool
    {
        $automatic = $source === 'automatic';
        $this->clear();
        if (! $this->enabled() || ($automatic && ! config('attendance.payroll_auto_sync'))) {
            if ($automatic) {
                return false;
            }
            throw ValidationException::withMessages(['attendance' => 'Integrasi absensi sedang dinonaktifkan.']);
        }
        // Manual refresh, adjustments and payment already use this same application lock.
        $payrollLock = $automatic ? Cache::lock('payroll-generation', 300) : null;
        if ($payrollLock && ! $payrollLock->get()) {
            return false;
        }
        $refreshLock = Cache::lock('payroll-attendance-refresh:'.$year.'-'.$month, 120);
        try {
            if (! $refreshLock->get()) {
                if ($automatic) {
                    return false;
                }
                throw ValidationException::withMessages(['attendance' => 'Data absensi sedang diperbarui. Tunggu sebentar lalu coba lagi.']);
            }
            try {
                $period = $this->period($month, $year);
                if ($period?->sealed_at || $period?->remote_locked_at || $period?->lock_attempted_at || RekapGaji::where('tahun', $year)->where('bulan', $month)->exists()) {
                    if ($automatic) {
                        return false;
                    }
                    throw ValidationException::withMessages(['attendance' => 'Snapshot sudah dikunci atau gaji sudah disahkan. Data tidak dapat diganti.']);
                }
                $due = $period?->next_sync_at ?? ($period?->fetched_at ? Carbon::parse($period->fetched_at)->addMinutes(15) : null);
                if ($automatic && $due && Carbon::parse($due)->isFuture()) {
                    return false;
                }
                $dates = $this->dates($month, $year);
                DB::table('payroll_attendance_periods')->insertOrIgnore(['year' => $year, 'month' => $month, 'from_date' => $dates['from'], 'to_date' => $dates['to'], 'request_id' => (string) Str::uuid(), 'created_at' => now(), 'updated_at' => now()]);
                $query = fn () => DB::table('payroll_attendance_periods')->where('year', $year)->where('month', $month)->whereNull('sealed_at')->whereNull('remote_locked_at')->whereNull('lock_attempted_at');
                $query()->update(['sync_attempted_at' => now(), 'sync_source' => $source]);
                $started = hrtime(true);
                try {
                    $this->refreshSnapshot($month, $year, $sources);
                    $query()->update(['sync_failures' => 0, 'sync_last_error' => null, 'sync_duration_ms' => (int) round((hrtime(true) - $started) / 1000000), 'next_sync_at' => now()->addMinutes(15)]);
                } catch (Throwable $exception) {
                    $failures = min(30, (int) ($query()->value('sync_failures') ?? 0) + 1);
                    $message = $exception instanceof ValidationException ? $exception->validator->errors()->first() : 'Pembaruan gagal karena gangguan internal. Hubungi pengembang.';
                    $query()->update(['sync_failures' => $failures, 'sync_failed_at' => now(), 'sync_last_error' => mb_substr($message, 0, 500), 'sync_duration_ms' => (int) round((hrtime(true) - $started) / 1000000), 'next_sync_at' => now()->addMinutes(min(15, 5 * 2 ** min(2, $failures - 1)))]);
                    Log::warning('Pembaruan snapshot gaji gagal; data terakhir dipertahankan.', ['period' => sprintf('%04d-%02d', $year, $month), 'source' => $source, 'exception_type' => $exception::class, 'attempts' => $failures]);
                    throw $exception;
                } finally {
                    $this->clear();
                }

                return true;
            } finally {
                $refreshLock->release();
            }
        } finally {
            $payrollLock?->release();
        }
    }

    private function refreshSnapshot(int $month, int $year, array $sources): void
    {
        $this->clear();
        $period = $this->period($month, $year);
        if ($period?->sealed_at || $period?->remote_locked_at || $period?->lock_attempted_at) {
            throw ValidationException::withMessages(['attendance' => 'Snapshot sudah dikunci. Lanjutkan transaksi gaji yang sama; data tidak dapat diganti.']);
        }
        $dates = $this->dates($month, $year);
        $sources = array_map('strval', $sources);
        sort($sources, SORT_STRING);
        if ($sources === []) {
            $ended = now()->gt(Carbon::parse($dates['to'])->endOfDay());
            $snapshot = ['schema_version' => 1, 'monitoring_version' => 1, 'as_of' => now()->toIso8601String(), ...$dates, 'source_employee_ids' => [], 'employees' => [], 'ready' => $ended, 'issues' => $ended ? [] : ['Periode absensi masih berjalan.'], 'revision' => hash('sha256', json_encode([$dates, $ended]))];
        } else {
            $snapshot = $this->request($dates + ['source_employee_ids' => $sources]);
        }
        $overrides = $period?->overrides;
        if ($period && ($this->payload($period)['revision'] ?? null) !== $snapshot['revision']) {
            $overrides = null;
        }
        DB::transaction(function () use ($month, $year, $dates, $period, $snapshot, $overrides): void {
            DB::table('payroll_attendance_periods')->updateOrInsert(['year' => $year, 'month' => $month], ['from_date' => $dates['from'], 'to_date' => $dates['to'], 'request_id' => $period?->request_id ?? (string) Str::uuid(), 'snapshot' => json_encode($snapshot, JSON_THROW_ON_ERROR), 'overrides' => $overrides, 'fetched_at' => now(), 'created_at' => $period?->created_at ?? now(), 'updated_at' => now()]);
            $this->clear();
            $this->event('snapshot.refreshed', ['revision' => $snapshot['revision'], 'issues' => $snapshot['issues']], $this->period($month, $year)->id);
        });
    }

    public function calculate(Karyawan $employee, int $month, int $year): array
    {
        $empty = ['amount' => 0, 'raw' => 0, 'cap' => 0, 'daily' => 0, 'days' => [], 'ready' => true];
        if (! $this->enabled()) {
            return $empty;
        }
        $period = $this->period($month, $year);
        if (! $period) {
            return array_replace($empty, ['ready' => false]);
        }
        $snapshot = $this->payload($period);
        if (($snapshot['monitoring_version'] ?? null) !== 1) {
            return array_replace($empty, ['ready' => false]);
        }
        if (! isset($this->peopleCache[$period->id])) {
            $this->peopleCache[$period->id] = array_column($snapshot['employees'], null, 'source_employee_id');
        }
        $person = $this->peopleCache[$period->id][(string) $employee->id] ?? null;
        if (! $person) {
            return array_replace($empty, ['ready' => false]);
        }
        $base = (int) $employee->gaji_pokok + (int) $employee->tunjangan_jabatan + (int) $employee->tunjangan_keluarga;
        $daily = (int) round($base / 25);
        $cap = intdiv($base, 4);
        $days = [];
        $raw = 0;
        $overrides = json_decode($period->overrides ?? '{}', true);
        foreach ($person['days'] as $day) {
            $amount = 0;
            $effective = (int) $day['scheduled_minutes'] - (int) $day['break_minutes'];
            if ($person['attendance_required']) {
                $amount = match ($day['status']) {
                    'absent' => $daily,'half_day' => (int) round($daily / 2),'present' => $effective > 0 ? (int) round(min($effective, max(0, (int) $day['late_minutes']) + max(0, (int) $day['early_minutes'])) / $effective * $daily) : 0,default => 0
                };
            }
            $override = in_array($day['status'], ['present', 'half_day', 'absent'], true) ? ($overrides[$employee->id.'|'.$day['date']] ?? null) : null;
            $applied = $override ? min($daily, max(0, (int) $override['amount'])) : $amount;
            if (! $person['attendance_required']) {
                $applied = 0;
            }
            $days[] = $day + ['automatic' => $amount, 'deduction' => $applied, 'override' => $override, 'effective_minutes' => max(0, $effective)];
            $raw += $applied;
        }

        return ['as_of' => $snapshot['as_of'] ?? null, 'provisional' => ! $snapshot['ready'], 'waiting_hr_days' => count(array_filter($person['days'], fn ($day): bool => $day['status'] === 'pending_leave')), 'unfinished_shifts' => count(array_filter($person['days'], fn ($day): bool => in_array($day['status'], ['pending', 'upcoming', 'in_progress'], true))), 'timezone' => $snapshot['timezone'] ?? 'Asia/Jakarta', 'from' => $snapshot['from'], 'to' => $snapshot['to'], 'amount' => min($cap, $raw), 'raw' => $raw, 'cap' => $cap, 'daily' => $daily, 'days' => $days, 'ready' => $snapshot['ready']];
    }

    public function lock(int $month, int $year, array $sources): ?object
    {
        $this->clear();
        if (! $this->enabled()) {
            return null;
        }
        $period = $this->period($month, $year);
        if (! $period || ! ($this->payload($period)['ready'] ?? false) || ($this->payload($period)['monitoring_version'] ?? null) !== 1) {
            throw ValidationException::withMessages(['attendance' => 'Perbarui data absensi dan selesaikan seluruh masalah sebelum menyimpan gaji.']);
        }
        $snapshot = $this->payload($period);
        $sources = array_map('strval', $sources);
        sort($sources, SORT_STRING);
        if ($sources !== $snapshot['source_employee_ids']) {
            throw ValidationException::withMessages(['attendance' => 'Daftar pegawai berubah. Perbarui snapshot dan tinjau gaji.']);
        }
        DB::table('payroll_attendance_periods')->where('id', $period->id)->update(['lock_attempted_at' => now()]);
        if ($sources !== []) {
            $this->request(['from' => $period->from_date, 'to' => $period->to_date, 'source_employee_ids' => $sources, 'revision' => $snapshot['revision'], 'request_id' => $period->request_id]);
        }
        DB::table('payroll_attendance_periods')->where('id', $period->id)->update(['remote_locked_at' => now(), 'updated_at' => now()]);
        $this->clear();

        return $this->period($month, $year);
    }

    public function seal(?object $period, int $rekap): void
    {
        if ($period) {
            DB::table('payroll_attendance_periods')->where('id', $period->id)->update(['sealed_at' => now(), 'rekap_gaji_id' => $rekap, 'updated_at' => now()]);
            $this->event('payroll.sealed', ['rekap_gaji_id' => $rekap, 'request_id' => $period->request_id], $period->id);
        }
    }
}
