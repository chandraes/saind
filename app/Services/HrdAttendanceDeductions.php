<?php

namespace App\Services;

use App\Models\Direksi;
use App\Models\Karyawan;
use App\Models\RekapGaji;
use App\Models\RekapGajiDetail;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Menu Potongan Absensi untuk HRD (web & mobile, baca saja). Hanya nama, jabatan
 * dan potongan absensi; nominal gaji lain tidak pernah dikirim.
 *
 * - Periode gaji berjalan (belum disahkan): dihitung langsung dari snapshot
 *   absensi, sama seperti pratinjau Form Gaji (status "Sementara").
 * - Periode yang gajinya sudah disahkan: dibaca dari rekap_gaji_details yang
 *   tersimpan, sehingga tidak berubah lagi (status "Final").
 */
class HrdAttendanceDeductions
{
    public const PER_PAGE = 25;

    public function __construct(private PayrollService $payroll) {}

    /**
     * Periode yang dapat dilihat, terbaru lebih dulu.
     *
     * @return list<array{month: int, year: int, final: bool, label: string}>
     */
    public function periods(): array
    {
        $periods = [];
        $open = $this->payroll->nextPeriod();
        // Sama dengan batas pratinjau Form Gaji: paling jauh satu periode ke depan.
        if ($open->lessThanOrEqualTo(now()->startOfMonth()->addMonth())) {
            $periods[$open->year.'-'.$open->month] = $this->describe($open->month, $open->year, false);
        }
        foreach (RekapGaji::orderByDesc('tahun')->orderByDesc('bulan')->get(['bulan', 'tahun']) as $saved) {
            $periods[$saved->tahun.'-'.$saved->bulan] ??= $this->describe((int) $saved->bulan, (int) $saved->tahun, true);
        }

        return array_values($periods);
    }

    /** @return array{month: int, year: int, final: bool, label: string} */
    public function resolve(?int $month, ?int $year): array
    {
        $periods = $this->periods();
        if ($periods === []) {
            throw ValidationException::withMessages(['bulan' => 'Belum ada periode gaji yang dapat dilihat.']);
        }
        if ($month === null || $year === null) {
            return $periods[0];
        }
        foreach ($periods as $period) {
            if ($period['month'] === $month && $period['year'] === $year) {
                return $period;
            }
        }

        throw ValidationException::withMessages(['bulan' => 'Periode gaji tersebut tidak tersedia.']);
    }

    /**
     * Baris tabel: direksi lebih dulu lalu pegawai, seperti Form Gaji. `key`
     * adalah id pegawai (periode berjalan) atau id rincian rekap (periode final).
     *
     * @return Collection<int, array{key: ?int, nama: string, jabatan: string, potongan_absensi: int, has_detail: bool}>
     */
    public function rows(array $period, string $search = '', bool $deductionsOnly = false): Collection
    {
        $rows = $period['final'] ? $this->savedRows($period) : $this->liveRows($period);
        $search = mb_strtolower(trim($search));

        return $rows->filter(fn (array $row): bool => ($search === '' || str_contains(mb_strtolower($row['nama']), $search) || str_contains(mb_strtolower($row['jabatan']), $search))
            && (! $deductionsOnly || $row['potongan_absensi'] > 0))->values();
    }

    /**
     * Rincian potongan per hari satu baris yang berpotongan.
     *
     * @return array{name: string, jabatan: string, detail: array<string, mixed>}
     */
    public function detail(array $period, int $key): array
    {
        if ($period['final']) {
            $record = RekapGajiDetail::where('rekap_gaji_id', $this->rekap($period)->id)->findOrFail($key);
            $detail = json_decode($record->rincian_absensi ?? 'null', true);
            abort_unless((int) $record->potongan_absensi > 0 && is_array($detail) && ! empty($detail['days']), 404, 'Rincian absensi tidak tersedia.');

            return ['name' => (string) $record->nama, 'jabatan' => (string) ($record->jabatan ?? '—'), 'detail' => $detail];
        }

        $this->payroll->attendance->clear();
        $employee = $this->employees($period)->whereKey($key)->firstOrFail();
        $detail = $this->payroll->attendance->calculate($employee, $period['month'], $period['year']);
        abort_unless($detail['amount'] > 0 && ! empty($detail['days']), 404, 'Rincian absensi tidak tersedia.');

        return ['name' => (string) $employee->nama, 'jabatan' => (string) ($employee->jabatan?->nama ?? '—'), 'detail' => $detail];
    }

    /**
     * Keterangan sumber data untuk badge Sementara/Final.
     *
     * @return array{final: bool, enabled: bool, fetched_at: ?string, next_sync_at: ?string, as_of: ?string, from: string, to: string}
     */
    public function status(array $period): array
    {
        $dates = $this->payroll->attendance->dates($period['month'], $period['year']);
        $record = $period['final'] ? null : $this->payroll->attendance->period($period['month'], $period['year']);
        $iso = fn ($value): ?string => $value ? Carbon::parse($value)->toIso8601String() : null;

        return [
            'final' => $period['final'],
            'enabled' => $period['final'] || $this->payroll->attendance->enabled(),
            'fetched_at' => $iso($record?->fetched_at),
            'next_sync_at' => config('attendance.payroll_auto_sync') && ! $record?->sealed_at ? $iso($record?->next_sync_at ?? null) : null,
            'as_of' => $record && $record->snapshot ? ($this->payroll->attendance->payload($record)['as_of'] ?? null) : null,
            'from' => $dates['from'],
            'to' => $dates['to'],
        ];
    }

    /** @return array{month: int, year: int, final: bool, label: string} */
    private function describe(int $month, int $year, bool $final): array
    {
        return ['month' => $month, 'year' => $year, 'final' => $final,
            'label' => Carbon::create($year, $month, 1)->locale('id')->translatedFormat('F Y')];
    }

    private function rekap(array $period): RekapGaji
    {
        return RekapGaji::where('bulan', $period['month'])->where('tahun', $period['year'])->orderByDesc('id')->firstOrFail();
    }

    private function employees(array $period)
    {
        // Tanpa kasbon: menu ini tidak memakai komponen gaji lain.
        return $this->payroll->employeesQueryForPeriod($period['month'], $period['year'])->setEagerLoads([])->with('jabatan:id,nama');
    }

    /** @return Collection<int, array<string, mixed>> */
    private function liveRows(array $period): Collection
    {
        $this->payroll->attendance->clear();
        $directors = $this->payroll->directorsForPeriod($period['month'], $period['year'])
            ->map(fn (Direksi $director): array => ['key' => null, 'nama' => (string) $director->nama, 'jabatan' => (string) ($director->jabatan ?: '—'), 'potongan_absensi' => 0, 'has_detail' => false]);
        $employees = $this->employees($period)->get()->map(function (Karyawan $employee) use ($period): array {
            $detail = $this->payroll->attendance->calculate($employee, $period['month'], $period['year']);

            return ['key' => (int) $employee->id, 'nama' => (string) $employee->nama, 'jabatan' => (string) ($employee->jabatan?->nama ?? '—'),
                'potongan_absensi' => (int) $detail['amount'], 'has_detail' => $detail['amount'] > 0 && ! empty($detail['days'])];
        });

        return collect($directors->all())->concat($employees->all())->values();
    }

    /** @return Collection<int, array<string, mixed>> */
    private function savedRows(array $period): Collection
    {
        return RekapGajiDetail::where('rekap_gaji_id', $this->rekap($period)->id)->orderBy('id')
            ->get(['id', 'nama', 'jabatan', 'potongan_absensi', 'rincian_absensi'])
            ->map(function (RekapGajiDetail $record): array {
                $detail = json_decode($record->rincian_absensi ?? 'null', true);
                $amount = (int) $record->potongan_absensi;

                return ['key' => (int) $record->id, 'nama' => (string) $record->nama, 'jabatan' => (string) ($record->jabatan ?: '—'),
                    'potongan_absensi' => $amount, 'has_detail' => $amount > 0 && is_array($detail) && ! empty($detail['days'])];
            })->values()->toBase();
    }
}
