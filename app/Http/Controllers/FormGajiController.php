<?php

namespace App\Http\Controllers;

use App\Exports\GajiPreviewExport;
use App\Models\RekapGajiDetail;
use App\Services\PayrollGeneration;
use App\Services\PayrollService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class FormGajiController extends Controller
{
    public function __construct(protected PayrollService $payroll, protected PayrollGeneration $generation) {}

    private function period(Request $request): Carbon
    {
        $data = $request->validate(['bulan' => ['required', 'integer', 'between:1,12'], 'tahun' => ['required', 'integer', 'between:1901,2155']]);
        $period = Carbon::create((int) $data['tahun'], (int) $data['bulan'], 1)->startOfDay();
        if ($period->greaterThan(now()->startOfMonth()->addMonth())) {
            throw ValidationException::withMessages(['bulan' => 'Preview dibatasi sampai satu periode berikutnya.']);
        }

        return $period;
    }

    private function previewData(Carbon $period): array
    {
        return [
            'data' => $this->payroll->employeesForPeriod($period->month, $period->year),
            'direksi' => $this->payroll->directorsForPeriod($period->month, $period->year),
            'monthName' => $period->copy()->locale('id')->monthName,
            'payrollDateReady' => $this->payroll->canProcess($period), 'processingStartsAt' => $this->payroll->processingStartsAt($period),
            'bulan' => $period->month, 'tahun' => $period->year, 'payroll' => $this->payroll, 'bpjsSetting' => $this->payroll->bpjs->setting($period->year),
        ];
    }

    public function index(Request $request): View|JsonResponse
    {
        $this->payroll->attendance->clear();
        $period = $this->period($request);
        $this->generation->ensureAvailable($period);

        if ($request->ajax()) {
            return $this->previewTable($request, $period);
        }
        $preview = $this->previewData($period);
        $rows = $preview['direksi']->concat($preview['data'])->map(fn ($person): array => $this->payroll->previewRow($person, $period->month, $period->year));
        $totals = [];
        foreach (['gp', 'tj', 'tk', 'btk', 'bk', 'ptk', 'pk', 'pkotor', 'pbersih', 'kasbon', 'potongan_absensi', 'total'] as $key) {
            $totals[$key] = $rows->sum($key);
        }

        $record = $this->payroll->attendance->period($period->month, $period->year);
        $snapshot = $record ? $this->payroll->attendance->payload($record) : null;
        $sources = array_map('strval', $preview['data']->modelKeys());
        sort($sources, SORT_STRING);
        if ($snapshot && $snapshot['source_employee_ids'] !== $sources) {
            $snapshot['ready'] = false;
            $snapshot['issues'][] = 'Daftar pegawai berubah. Perbarui data absensi.';
        }

        if ($snapshot && ($snapshot['monitoring_version'] ?? null) !== 1) {
            $snapshot['ready'] = false;
            $snapshot['issues'][] = 'Perbarui snapshot untuk mengaktifkan pantauan berjalan.';
        }

        if ($snapshot && ! $this->payroll->attendance->workPatternsReady($snapshot, $record)) {
            $snapshot['ready'] = false;
            $snapshot['issues'][] = 'Pembagi pengupahan belum ditentukan. Periksa jadwal mingguan pegawai, lalu perbarui snapshot absensi.';
        }

        return view('billing.gaji.index', [...$preview,
            'attendanceEnabled' => $this->payroll->attendance->enabled(), 'attendancePeriod' => $record,
            'attendanceDates' => $this->payroll->attendance->dates($period->month, $period->year),
            'attendanceSnapshot' => $snapshot, 'deductedCount' => $rows->where('potongan_absensi', '>', 0)->count(), 'payrollRevision' => $this->payroll->revision($period->month, $period->year),
            'gt' => $totals, 'peopleCount' => $rows->count(), 'negativeCount' => $rows->where('total', '<', 0)->count(),
        ]);
    }

    private function previewTable(Request $request, Carbon $period): JsonResponse
    {
        $employees = $this->payroll->employeesQueryForPeriod($period->month, $period->year);
        $directors = $this->payroll->directorsQueryForPeriod($period->month, $period->year);
        $total = (clone $employees)->count() + (clone $directors)->count();
        $search = mb_substr($request->string('search.value')->trim()->toString(), 0, 100);
        if ($search !== '') {
            $employees->where(function (Builder $query) use ($search): void {
                $query->where('nama', 'like', '%'.$search.'%')->orWhereHas('jabatan', fn (Builder $query) => $query->where('nama', 'like', '%'.$search.'%'));
            });
            $directors->where(function (Builder $query) use ($search): void {
                $query->where('nama', 'like', '%'.$search.'%')->orWhere('jabatan', 'like', '%'.$search.'%');
            });
        }
        $request->validate(['deductions_only' => ['sometimes', 'boolean']]);
        if ($request->boolean('deductions_only')) {
            $ids = (clone $employees)->get()->filter(fn ($employee): bool => $this->payroll->attendance->calculate($employee, $period->month, $period->year)['amount'] > 0)->modelKeys();
            $employees->whereIn('id', $ids);
            $directors->whereRaw('1 = 0');
        }
        $directorCount = (clone $directors)->count();
        $filtered = $directorCount + (clone $employees)->count();
        $start = max(0, $request->integer('start'));
        $length = min(100, max(1, $request->integer('length', 25)));
        $people = collect();
        if ($start < $directorCount) {
            $people = $people->concat($directors->offset($start)->limit($length)->get());
        }
        $remaining = $length - $people->count();
        if ($remaining > 0) {
            $people = $people->concat($employees->offset(max(0, $start - $directorCount))->limit($remaining)->get());
        }

        return response()->json([
            'draw' => max(0, $request->integer('draw')), 'recordsTotal' => $total, 'recordsFiltered' => $filtered,
            'data' => $people->values()->map(fn ($person, int $index): array => ['number' => $start + $index + 1, ...$this->payroll->previewRow($person, $period->month, $period->year)])->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $period = $this->period($request);
        try {
            $cash = $this->generation->store($period, $request->input('payroll_revision'));
        } catch (ValidationException $exception) {
            if (array_key_exists(PayrollGeneration::BUSY, $exception->errors())) {
                return back()->with('error', $exception->errors()[PayrollGeneration::BUSY][0]);
            }
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('error', 'Gaji belum tersimpan. Periode absensi mungkin sudah terkunci; muat ulang lalu ulangi transaksi yang sama.');
        }
        $this->generation->notify($cash);

        return to_route('billing.index')->with('success', 'Form Gaji Berhasil Dibuat');
    }

    public function attendancePdf(Request $request, int $employee): Response
    {
        $this->payroll->attendance->clear();
        $period = $this->period($request);
        $this->generation->ensureAvailable($period);
        $person = $this->payroll->employeesQueryForPeriod($period->month, $period->year)->whereKey($employee)->firstOrFail();
        $detail = $this->payroll->attendance->calculate($person, $period->month, $period->year);
        abort_unless(! empty($detail['days']), 404, 'Rincian absensi belum tersedia.');

        return $this->downloadAttendancePdf($person->nama, $period, $detail, false, $employee);
    }

    public function savedAttendancePdf(int $detail): Response
    {
        $record = RekapGajiDetail::with('rekap_gaji')->findOrFail($detail);
        $payload = json_decode($record->rincian_absensi ?? 'null', true);
        abort_unless(is_array($payload) && ! empty($payload['days']) && $record->rekap_gaji, 404, 'Rincian absensi tersimpan belum tersedia.');
        $period = Carbon::create($record->rekap_gaji->tahun, $record->rekap_gaji->bulan, 1);

        return $this->downloadAttendancePdf($record->nama, $period, $payload, true, $record->id);
    }

    private function downloadAttendancePdf(string $name, Carbon $period, array $detail, bool $saved, int $identifier): Response
    {
        return Pdf::loadView('billing.gaji.attendance-pdf', compact('name', 'period', 'detail', 'saved'))
            ->setPaper('a4', 'landscape')->download('Rincian_Absensi_'.$period->format('Y_m').'_'.$identifier.'.pdf');
    }

    public function previewPdf(Request $request): Response
    {
        $this->payroll->attendance->clear();
        $period = $this->period($request);
        $this->generation->ensureAvailable($period);

        return Pdf::loadView('billing.gaji.pdf', [...$this->previewData($period), 'is_preview' => true])
            ->setPaper('a4', 'landscape')->stream('Preview_Gaji_'.$period->format('m_Y').'.pdf');
    }

    public function previewExcel(Request $request): BinaryFileResponse
    {
        $this->payroll->attendance->clear();
        $period = $this->period($request);
        $this->generation->ensureAvailable($period);

        return Excel::download(new GajiPreviewExport($period->month, $period->year, $this->payroll), 'Preview_Gaji_'.$period->format('m_Y').'.xlsx');
    }
}
