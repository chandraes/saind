<?php

namespace App\Services;

use App\Models\Direksi;
use App\Models\Karyawan;
use App\Models\KasBon;
use App\Models\KasBonCicilan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollService
{
    public function __construct(public PayrollAttendanceService $attendance, public BpjsHealthMinimumService $bpjs) {}

    public function calculateComponent(Karyawan|Direksi $person, ?int $year = null): array
    {
        $gapokPlus = $person->gaji_pokok + $person->tunjangan_jabatan + $person->tunjangan_keluarga;

        $bpjs_tk = $person->apa_bpjs_tk == 1 ? $gapokPlus * 0.0489 : 0;

        $pot_tk = $person->apa_bpjs_tk == 1 ? $gapokPlus * 0.02 : 0;

        $healthBase = $this->bpjs->base((int) $gapokPlus, $year ?? now()->year);
        $bpjs_k = $person->apa_bpjs_kesehatan == 1 ? $healthBase * 0.04 : 0;
        $pot_k = $person->apa_bpjs_kesehatan == 1 ? $healthBase * 0.01 : 0;

        $bpjs_tk = (int) round($bpjs_tk);
        $bpjs_k = (int) round($bpjs_k);
        $pot_tk = (int) round($pot_tk);
        $pot_k = (int) round($pot_k);
        $pendapatan_kotor = $gapokPlus + $bpjs_tk + $bpjs_k;
        $pendapatan_bersih = $gapokPlus - $pot_tk - $pot_k;

        return array_map(fn (int|float $amount): int => (int) round($amount), [
            'bpjs_tk' => $bpjs_tk,
            'bpjs_k' => $bpjs_k,
            'pot_tk' => $pot_tk,
            'pot_k' => $pot_k,
            'pendapatan_kotor' => $pendapatan_kotor,
            'pendapatan_bersih' => $pendapatan_bersih,
        ]);
    }

    /** @return Collection<int, Karyawan> */
    public function employeesForPeriod(int $bulan, int $tahun, bool $lock = false): Collection
    {
        return $this->employeesQueryForPeriod($bulan, $tahun, $lock)->get();
    }

    /** @return Builder<Karyawan> */
    public function employeesQueryForPeriod(int $bulan, int $tahun, bool $lock = false): Builder
    {
        $end = Carbon::create($tahun, $bulan, 1)->endOfMonth()->toDateString();
        $query = Karyawan::where('status', 'aktif')->where('mulai_bekerja', '<=', $end)
            ->with(['jabatan', 'kas_bon' => function (HasMany $query) use ($lock): void {
                $query->where('lunas', false)->where('void', false)->orderBy('tanggal')->orderBy('id');
                if ($lock) {
                    $query->lockForUpdate();
                }
            }, 'kas_bon_cicilan' => function (HasMany $query) use ($lock): void {
                $query->where('lunas', false)->orderBy('mulai_tahun')->orderBy('mulai_bulan')->orderBy('id');
                if ($lock) {
                    $query->lockForUpdate();
                }
            }])->orderBy('id');
        if ($lock) {
            $query->lockForUpdate();
        }

        return $query;
    }

    /** @return Collection<int, Direksi> */
    public function directorsForPeriod(int $bulan, int $tahun, bool $lock = false): Collection
    {
        return $this->directorsQueryForPeriod($bulan, $tahun, $lock)->get();
    }

    /** @return Builder<Direksi> */
    public function directorsQueryForPeriod(int $bulan, int $tahun, bool $lock = false): Builder
    {
        $query = Direksi::where('status', 'aktif')->where('mulai_bekerja', '<=', Carbon::create($tahun, $bulan, 1)->endOfMonth()->toDateString())->orderBy('id');
        if ($lock) {
            $query->lockForUpdate();
        }

        return $query;
    }

    public function previewRow(Karyawan|Direksi $person, int $bulan, int $tahun): array
    {
        $calc = $this->calculateComponent($person, $tahun);
        $kasbon = $person instanceof Karyawan ? $this->calculateKasbon($person, $bulan, $tahun) : 0;

        $deduction = $person instanceof Karyawan ? $this->attendance->calculate($person, $bulan, $tahun) : ['amount' => 0, 'raw' => 0, 'cap' => 0, 'daily' => 0, 'days' => [], 'ready' => true];

        return [
            'employee_id' => $person instanceof Karyawan ? $person->id : null, 'potongan_absensi' => $deduction['amount'], 'potongan_absensi_sebelum_batas' => $deduction['raw'], 'attendance_detail' => $deduction,
            'nama' => $person->nama, 'jabatan' => $person instanceof Karyawan ? ($person->jabatan?->nama ?? '—') : $person->jabatan,
            'gp' => (int) $person->gaji_pokok, 'tj' => (int) $person->tunjangan_jabatan, 'tk' => (int) $person->tunjangan_keluarga,
            'btk' => $calc['bpjs_tk'], 'bk' => $calc['bpjs_k'], 'ptk' => $calc['pot_tk'], 'pk' => $calc['pot_k'],
            'pkotor' => $calc['pendapatan_kotor'], 'pbersih' => $calc['pendapatan_bersih'], 'kasbon' => $kasbon,
            'total' => $calc['pendapatan_bersih'] - $kasbon - $deduction['amount'],
        ];
    }

    public function revision(int $month, int $year): string
    {
        $people = $this->directorsForPeriod($month, $year)->concat($this->employeesForPeriod($month, $year));
        $rows = $people->map(fn ($person): array => [get_class($person), $person->id, $person->getAttributes(), $this->previewRow($person, $month, $year), $person instanceof Karyawan ? $person->kas_bon->toArray() : [], $person instanceof Karyawan ? $person->kas_bon_cicilan->toArray() : []])->all();
        $period = $this->attendance->period($month, $year);

        return hash('sha256', json_encode([$this->attendance->enabled(), $period?->snapshot, $period?->overrides, $rows], JSON_THROW_ON_ERROR));
    }

    /** @return list<array{loan: KasBon|KasBonCicilan, amount: int}> */
    public function kasbonPayments(Karyawan $karyawan, int $bulan, int $tahun): array
    {
        $period = Carbon::create($tahun, $bulan, 1)->startOfDay();
        $end = $period->copy()->endOfMonth()->toDateString();
        $payments = [];
        foreach ($karyawan->kas_bon_cicilan as $loan) {
            $start = Carbon::create($loan->mulai_tahun, $loan->mulai_bulan, 1)->startOfDay();
            if (! $loan->lunas && $loan->getRawOriginal('tanggal') <= $end && $period->greaterThanOrEqualTo($start)) {
                $amount = max(0, min((int) $loan->cicilan_nominal, (int) $loan->sisa_kas));
                if ($amount > 0) {
                    $payments[] = ['loan' => $loan, 'amount' => $amount];
                }
            }
        }
        foreach ($karyawan->kas_bon as $loan) {
            if (! $loan->lunas && ! $loan->void && $loan->getRawOriginal('tanggal') <= $end) {
                $amount = max(0, (int) $loan->sisa_kas);
                if ($amount > 0) {
                    $payments[] = ['loan' => $loan, 'amount' => $amount];
                }
            }
        }

        return $payments;
    }

    public function calculateKasbon(Karyawan $karyawan, int $bulan, int $tahun): int
    {
        return array_sum(array_column($this->kasbonPayments($karyawan, $bulan, $tahun), 'amount'));
    }
}
