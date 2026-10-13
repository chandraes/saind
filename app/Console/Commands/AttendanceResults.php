<?php

namespace App\Console\Commands;

use App\Services\AttendanceConnector;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class AttendanceResults extends Command
{
    protected $signature = 'attendance:results {resource=attendances} {--employee=} {--from=} {--to=} {--page=1}';

    protected $description = 'Baca hasil absensi, cuti, atau hak cuti melalui API khusus perusahaan SAIND';

    public function handle(AttendanceConnector $connector): int
    {
        $data = ['resource' => $this->argument('resource'), 'source_employee_id' => $this->option('employee'), 'from' => $this->option('from'), 'to' => $this->option('to'), 'page' => $this->option('page')];
        $validator = Validator::make($data, ['resource' => ['required', 'in:attendances,leaves,leave-accounts'], 'source_employee_id' => ['nullable', 'string', 'max:100'], 'from' => ['nullable', 'date_format:Y-m-d'], 'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'], 'page' => ['required', 'integer', 'min:1', 'max:100000']]);
        if ($validator->fails()) {
            $this->error('Filter tidak valid: '.$validator->errors()->first());

            return self::FAILURE;
        }
        try {
            $results = $connector->results($data['resource'], array_filter($data, fn ($value) => $value !== null && $value !== ''));
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
        $this->info($results['total'].' catatan; halaman '.$results['current_page'].'/'.$results['last_page']);
        $this->table(['ID pegawai SAIND', 'Nama', 'Tanggal / tahun', 'Status'], array_map(fn (array $row): array => [
            $row['employee']['source_employee_id'] ?? '—', $row['employee']['name'] ?? '—', $row['work_date'] ?? $row['start_date'] ?? $row['year'] ?? '—', $row['status'] ?? '—',
        ], $results['data']));

        return self::SUCCESS;
    }
}
