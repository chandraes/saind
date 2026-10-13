<?php

namespace App\Services;

use App\Models\AttendanceEmployeeSync;
use App\Models\Karyawan;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AttendanceConnector
{
    /** @return array{source_employee_id: string, employee_number: string, name: string, position: ?string, status: string, employment_started_at: ?string} */
    public function payload(Karyawan $employee, bool $deleted = false): array
    {
        return [
            'source_employee_id' => (string) $employee->getKey(),
            'employee_number' => ($employee->kode ?? '').($employee->nomor ?? $employee->getKey()),
            'name' => $employee->nama,
            'position' => $employee->jabatan()->value('nama'),
            'status' => ! $deleted && $employee->status === 'aktif' ? 'active' : 'inactive',
            'employment_started_at' => $employee->mulai_bekerja ? CarbonImmutable::parse($employee->mulai_bekerja)->toDateString() : null,
        ];
    }

    public function capture(Karyawan $employee, bool $deleted = false, bool $force = false): AttendanceEmployeeSync
    {
        return $this->store($this->payload($employee, $deleted), $force);
    }

    /** @param array{source_employee_id: string, employee_number: string, name: string, position: ?string, status: string, employment_started_at: ?string} $payload */
    private function store(array $payload, bool $force = false): AttendanceEmployeeSync
    {
        return DB::transaction(function () use ($payload, $force): AttendanceEmployeeSync {
            DB::table('attendance_employee_syncs')->insertOrIgnore(['source_employee_id' => $payload['source_employee_id'], 'created_at' => now(), 'updated_at' => now()]);
            $sync = AttendanceEmployeeSync::where('source_employee_id', $payload['source_employee_id'])->lockForUpdate()->firstOrFail();
            $hash = hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
            if ($force || $sync->payload_hash !== $hash) {
                $sync->fill(['payload' => $payload, 'payload_hash' => $hash, 'source_version' => $sync->source_version + 1,
                    'attempts' => 0, 'next_retry_at' => null, 'last_error' => null])->save();
            }

            return $sync;
        });
    }

    public function reconcile(bool $force = false): int
    {
        $count = 0;
        Karyawan::query()->select('id')->chunkById(100, function ($employees) use ($force, &$count): void {
            foreach ($employees as $employee) {
                DB::transaction(function () use ($employee, $force, &$count): void {
                    $current = Karyawan::whereKey($employee->id)->lockForUpdate()->first();
                    if ($current) {
                        $this->capture($current, force: $force);
                        $count++;
                    }
                });
            }
        });
        AttendanceEmployeeSync::query()->whereNotIn('source_employee_id', Karyawan::select('id'))->where('payload->status', 'active')
            ->chunkById(100, function ($syncs): void {
                foreach ($syncs as $sync) {
                    DB::transaction(function () use ($sync): void {
                        $current = AttendanceEmployeeSync::whereKey($sync->id)->lockForUpdate()->firstOrFail();
                        if (! Karyawan::whereKey($current->source_employee_id)->exists() && $current->payload['status'] === 'active') {
                            $payload = $current->payload;
                            $payload['status'] = 'inactive';
                            $this->store($payload);
                        }
                    });
                }
            });

        return $count;
    }

    public function configured(): bool
    {
        $url = config('attendance.url');

        return config('attendance.enabled') && is_string($url) && filter_var($url, FILTER_VALIDATE_URL)
            && parse_url($url, PHP_URL_SCHEME) === 'https' && is_string(config('attendance.token')) && strlen(config('attendance.token')) >= 40;
    }

    public function send(int $id, bool $ignoreBackoff = false): bool
    {
        if (! $this->configured()) {
            return false;
        }
        $claimed = DB::transaction(function () use ($id, $ignoreBackoff): ?array {
            $sync = AttendanceEmployeeSync::whereKey($id)->lockForUpdate()->first();
            if (! $sync || $sync->source_version <= $sync->delivered_version || $sync->lease_until?->isFuture()
                || (! $ignoreBackoff && $sync->next_retry_at?->isFuture())) {
                return null;
            }
            $lease = (string) Str::uuid();
            $sync->update(['lease_token' => $lease, 'lease_until' => now()->addSeconds(30)]);

            return ['lease' => $lease, 'version' => $sync->source_version, 'payload' => [...$sync->payload, 'source_version' => $sync->source_version]];
        });
        if (! $claimed) {
            return false;
        }
        $error = null;
        try {
            $client = Http::acceptJson()->withToken(config('attendance.token'))->connectTimeout(3)->timeout(10)->withoutRedirecting();
            if (config('attendance.ca_bundle')) {
                $client = $client->withOptions(['verify' => config('attendance.ca_bundle')]);
            }
            $response = $client->put(rtrim(config('attendance.url'), '/').'/api/v1/integration/employees', $claimed['payload']);
            if (! $response->successful()) {
                $error = 'HTTP '.$response->status().': pengiriman belum diterima; periksa koneksi, token, atau data pegawai.';
            } elseif ($response->json('applied_version') !== $claimed['version'] || $response->json('data.source_employee_id') !== $claimed['payload']['source_employee_id']) {
                $error = 'Konfirmasi ID/versi tidak sesuai; periksa pemetaan sumber dan versi pada aplikasi absensi.';
            }
        } catch (ConnectionException) {
            $error = 'Koneksi ke aplikasi absensi gagal atau melewati batas waktu.';
        }
        DB::transaction(function () use ($id, $claimed, $error): void {
            $sync = AttendanceEmployeeSync::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($sync->lease_token !== $claimed['lease']) {
                return;
            }
            $values = ['lease_token' => null, 'lease_until' => null];
            if ($error) {
                $attempts = $sync->attempts + 1;
                $values += ['attempts' => $attempts, 'last_error' => $error, 'next_retry_at' => now()->addSeconds(min(3600, 15 * 2 ** min(8, $attempts - 1)))];
            } else {
                $values += ['delivered_version' => max($sync->delivered_version, $claimed['version']), 'attempts' => 0, 'last_error' => null, 'next_retry_at' => null, 'last_synced_at' => now()];
            }
            $sync->update($values);
        });

        return $error === null;
    }

    /** @param array{source_employee_id?: string, from?: string, to?: string, page?: int} $filters */
    public function results(string $resource, array $filters = []): array
    {
        if (! $this->configured() || ! in_array($resource, ['attendances', 'leaves', 'leave-accounts'], true)) {
            throw new \RuntimeException('Integrasi belum siap atau jenis data tidak valid.');
        }
        $client = Http::acceptJson()->withToken(config('attendance.token'))->connectTimeout(3)->timeout(10)->withoutRedirecting();
        if (config('attendance.ca_bundle')) {
            $client = $client->withOptions(['verify' => config('attendance.ca_bundle')]);
        }
        try {
            $response = $client->get(rtrim(config('attendance.url'), '/').'/api/v1/integration/'.$resource, array_intersect_key($filters, array_flip(['source_employee_id', 'from', 'to', 'page'])));
        } catch (ConnectionException) {
            throw new \RuntimeException('Koneksi ke aplikasi absensi gagal atau melewati batas waktu.');
        }
        if (! $response->successful() || ! is_array($response->json('data'))) {
            throw new \RuntimeException('Hasil absensi belum dapat dibaca. Periksa koneksi dan token integrasi.');
        }

        return ['data' => $response->json('data'), 'current_page' => (int) $response->json('current_page'), 'last_page' => (int) $response->json('last_page'), 'total' => (int) $response->json('total')];
    }

    /** @return array{sent: int, pending: int} */
    public function flush(int $limit = 50, bool $ignoreBackoff = false): array
    {
        $query = AttendanceEmployeeSync::whereColumn('source_version', '>', 'delivered_version');
        if (! $ignoreBackoff) {
            $query->where(fn ($q) => $q->whereNull('next_retry_at')->orWhere('next_retry_at', '<=', now()));
        }
        $sent = 0;
        $deadline = microtime(true) + 15;
        foreach ($query->orderBy('id')->limit($limit)->pluck('id') as $id) {
            if (microtime(true) >= $deadline) {
                break;
            }
            if ($this->send($id, $ignoreBackoff)) {
                $sent++;
            }
        }

        return ['sent' => $sent, 'pending' => AttendanceEmployeeSync::whereColumn('source_version', '>', 'delivered_version')->count()];
    }
}
