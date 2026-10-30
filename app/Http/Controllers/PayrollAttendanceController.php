<?php

namespace App\Http\Controllers;

use App\Models\RekapGaji;
use App\Services\PayrollAttendanceService;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayrollAttendanceController extends Controller
{
    public function __construct(private PayrollAttendanceService $attendance, private PayrollService $payroll) {}

    private function period(Request $request): array
    {
        $data = $request->validate(['bulan' => ['required', 'integer', 'between:1,12'], 'tahun' => ['required', 'integer', 'between:1901,2155']]);
        if (Carbon::create($data['tahun'], $data['bulan'], 1)->startOfMonth()->gt(now()->startOfMonth()->addMonth())) {
            throw ValidationException::withMessages(['bulan' => 'Preview dibatasi sampai satu periode berikutnya.']);
        }

        return $data;
    }

    private function available(int $month, int $year): void
    {
        abort_if(RekapGaji::where('bulan', $month)->where('tahun', $year)->exists(), 409, 'Gaji sudah disahkan.');
    }

    private function run(callable $work): void
    {
        $ran = Cache::lock('payroll-generation', 300)->get(function () use ($work): bool {
            $work();

            return true;
        });
        if (! $ran) {
            throw ValidationException::withMessages(['attendance' => 'Proses gaji lain sedang berjalan.']);
        }
    }

    public function refresh(Request $request): RedirectResponse
    {
        $data = $this->period($request);
        $this->run(function () use ($data): void {
            $this->available($data['bulan'], $data['tahun']);
            $this->attendance->refresh($data['bulan'], $data['tahun'], $this->payroll->employeesForPeriod($data['bulan'], $data['tahun'])->modelKeys());
        });

        return back()->with('success', 'Data absensi diperbarui. Tinjau rincian sebelum menyimpan gaji.');
    }

    public function setting(Request $request): RedirectResponse
    {
        $this->attendance->clear();
        abort_unless($request->user()->role === 'su', 403);
        $data = $request->validate(['enabled' => ['required', 'boolean'], 'reason' => ['required', 'string', 'min:10', 'max:1000']]);
        $this->run(function () use ($data): void {
            if (DB::table('payroll_attendance_periods')->whereNotNull('lock_attempted_at')->whereNull('sealed_at')->exists()) {
                throw ValidationException::withMessages(['attendance' => 'Selesaikan transaksi penguncian yang sedang berjalan sebelum mengubah sakelar.']);
            }
            $before = $this->attendance->enabled();
            DB::transaction(function () use ($data, $before): void {
                DB::table('payroll_attendance_settings')->where('id', 1)->update(['enabled' => $data['enabled'], 'updated_at' => now()]);
                $this->attendance->event('setting.changed', ['before' => $before, 'enabled' => (bool) $data['enabled'], 'reason' => $data['reason']]);
            });
        });

        return back()->with('success', 'Pengaturan potongan absensi diperbarui.');
    }

    public function adjustment(Request $request): RedirectResponse
    {
        $this->attendance->clear();
        abort_unless($request->user()->role === 'su', 403);
        $data = $request->validate(['bulan' => ['required', 'integer', 'between:1,12'], 'tahun' => ['required', 'integer', 'between:1901,2155'], 'employee_id' => ['required', 'integer'], 'date' => ['required', 'date_format:Y-m-d'], 'amount' => ['required', 'integer', 'min:0'], 'reason' => ['required', 'string', 'min:10', 'max:1000'], 'reset' => ['nullable', 'boolean']]);
        $this->run(function () use ($data, $request): void {
            $this->available($data['bulan'], $data['tahun']);
            $period = $this->attendance->period($data['bulan'], $data['tahun']);
            abort_unless($period && ! $period->sealed_at && ! $period->remote_locked_at && ! $period->lock_attempted_at, 409, 'Snapshot tidak tersedia atau sudah dikunci.');
            $employee = $this->payroll->employeesQueryForPeriod($data['bulan'], $data['tahun'])->whereKey($data['employee_id'])->firstOrFail();
            $calculation = $this->attendance->calculate($employee, $data['bulan'], $data['tahun']);
            $day = collect($calculation['days'])->firstWhere('date', $data['date']);
            abort_unless($day && in_array($day['status'], ['present', 'half_day', 'absent'], true), 422, 'Tanggal tidak termasuk hari potongan yang dapat disesuaikan.');
            abort_if($data['amount'] > $calculation['daily'], 422, 'Penyesuaian melebihi nilai harian.');
            $overrides = json_decode($period->overrides ?? '{}', true);
            $key = $employee->id.'|'.$data['date'];
            $before = $overrides[$key] ?? null;
            if ($data['reset'] ?? false) {
                unset($overrides[$key]);
            } else {
                $overrides[$key] = ['amount' => $data['amount'], 'reason' => $data['reason'], 'user_id' => $request->user()->id, 'user_name' => $request->user()->name, 'at' => now()->toIso8601String()];
            }
            DB::transaction(function () use ($period, $overrides, $before, $key, $data): void {
                DB::table('payroll_attendance_periods')->where('id', $period->id)->update(['overrides' => json_encode($overrides, JSON_THROW_ON_ERROR), 'updated_at' => now()]);
                $this->attendance->event('deduction.adjusted', ['key' => $key, 'before' => $before, 'after' => $overrides[$key] ?? null, 'reason' => $data['reason']], $period->id);
            });
        });

        return back()->with('success', 'Penyesuaian potongan dicatat beserta alasan.');
    }
}
