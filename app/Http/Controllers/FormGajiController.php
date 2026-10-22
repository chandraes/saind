<?php

namespace App\Http\Controllers;

use App\Exports\GajiPreviewExport;
use App\Models\GroupWa;
use App\Models\Karyawan;
use App\Models\KasBesar;
use App\Models\RekapGaji;
use App\Models\RekapGajiDetail;
use App\Services\PayrollService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class FormGajiController extends Controller
{
    public function __construct(protected PayrollService $payroll) {}

    private function period(Request $request): Carbon
    {
        $data = $request->validate(['bulan' => ['required', 'integer', 'between:1,12'], 'tahun' => ['required', 'integer', 'between:1901,2155']]);
        $period = Carbon::create((int) $data['tahun'], (int) $data['bulan'], 1)->startOfDay();
        if ($period->greaterThan(now()->startOfMonth())) {
            throw ValidationException::withMessages(['bulan' => 'Form gaji untuk periode mendatang belum dapat dibuat.']);
        }

        return $period;
    }

    private function ensurePeriodAvailable(Carbon $period): void
    {
        if (RekapGaji::where('bulan', $period->month)->where('tahun', $period->year)->exists()) {
            throw ValidationException::withMessages(['bulan' => 'Form gaji periode '.$period->copy()->locale('id')->translatedFormat('F Y').' sudah dibuat.']);
        }
        $later = RekapGaji::where(function (Builder $query) use ($period): void {
            $query->where('tahun', '>', $period->year)->orWhere(function (Builder $query) use ($period): void {
                $query->where('tahun', $period->year)->where('bulan', '>', $period->month);
            });
        })->orderByDesc('tahun')->orderByDesc('bulan')->first();
        if ($later) {
            $laterPeriod = Carbon::create($later->tahun, $later->bulan, 1)->locale('id')->translatedFormat('F Y');
            throw ValidationException::withMessages(['bulan' => 'Gaji periode '.$laterPeriod.' sudah dibuat. Periode sebelumnya tidak dapat dibuat karena saldo kasbon telah berubah. Proses gaji sesuai urutan periode.']);
        }
    }

    private function previewData(Carbon $period): array
    {
        return [
            'data' => $this->payroll->employeesForPeriod($period->month, $period->year),
            'direksi' => $this->payroll->directorsForPeriod($period->month, $period->year),
            'monthName' => $period->copy()->locale('id')->monthName,
            'bulan' => $period->month, 'tahun' => $period->year, 'payroll' => $this->payroll,
        ];
    }

    public function index(Request $request): View|JsonResponse
    {
        $this->payroll->attendance->clear();
        $period = $this->period($request);
        $this->ensurePeriodAvailable($period);

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
        $this->payroll->attendance->clear();
        $period = $this->period($request);
        $this->ensurePeriodAvailable($period);
        try {
            $cash = Cache::lock('payroll-generation', 300)->get(function () use ($period, $request): KasBesar {
                $this->ensurePeriodAvailable($period);
                $attendance = $this->payroll->attendance;
                $checkRevision = function () use ($period, $request, $attendance): void {
                    if (($attendance->enabled() || $request->filled('payroll_revision')) && (! is_string($request->input('payroll_revision')) || ! hash_equals($this->payroll->revision($period->month, $period->year), $request->input('payroll_revision')))) {
                        throw ValidationException::withMessages(['attendance' => 'Pratinjau gaji berubah atau kedaluwarsa. Muat ulang dan tinjau sebelum menyimpan.']);
                    }
                };
                $checkRevision();
                $people = $this->payroll->employeesForPeriod($period->month, $period->year);
                $previews = $this->payroll->directorsForPeriod($period->month, $period->year)->concat($people)->map(fn ($person) => $this->payroll->previewRow($person, $period->month, $period->year));
                if ($previews->contains(fn ($row) => $row['total'] < 0)) {
                    throw ValidationException::withMessages(['bulan' => 'Potongan melebihi pendapatan bersih. Periksa sebelum menyimpan.']);
                }
                if ((int) KasBesar::orderByDesc('id')->value('saldo') < $previews->sum('total')) {
                    throw ValidationException::withMessages(['bulan' => 'Saldo Kas Besar Tidak Cukup']);
                }
                $lockedPeriod = $attendance->lock($period->month, $period->year, $people->modelKeys());

                return DB::transaction(function () use ($period, $lockedPeriod, $checkRevision): KasBesar {
                    $this->ensurePeriodAvailable($period);
                    $kasBesar = KasBesar::orderByDesc('id')->lockForUpdate()->first();
                    $data = $this->payroll->employeesForPeriod($period->month, $period->year, true);
                    $direksi = $this->payroll->directorsForPeriod($period->month, $period->year, true);
                    $checkRevision();
                    if ($data->isEmpty() && $direksi->isEmpty()) {
                        throw ValidationException::withMessages(['bulan' => 'Tidak ada pegawai atau direksi untuk periode gaji ini.']);
                    }
                    $rekap = RekapGaji::create([
                        'uraian' => 'Gaji Bulan '.$period->copy()->locale('id')->monthName.' Tahun '.$period->year,
                        'bulan' => $period->month, 'tahun' => $period->year, 'total' => 0,
                    ]);
                    $total = 0;
                    foreach ($direksi->concat($data) as $person) {
                        $calc = $this->payroll->calculateComponent($person);
                        $payments = $person instanceof Karyawan ? $this->payroll->kasbonPayments($person, $period->month, $period->year) : [];
                        $kasbon = array_sum(array_column($payments, 'amount'));
                        $deduction = $person instanceof Karyawan ? $this->payroll->attendance->calculate($person, $period->month, $period->year) : ['amount' => 0, 'raw' => 0, 'days' => []];
                        $sisaGaji = $calc['pendapatan_bersih'] - $kasbon - $deduction['amount'];
                        if ($sisaGaji < 0) {
                            throw ValidationException::withMessages(['bulan' => 'Potongan kasbon '.$person->nama.' melebihi pendapatan bersih. Periksa kasbon sebelum menyimpan gaji.']);
                        }
                        $total += $sisaGaji;
                        RekapGajiDetail::create([
                            'rekap_gaji_id' => $rekap->id,
                            'nik' => $person instanceof Karyawan ? $person->kode.sprintf('%03d', $person->nomor) : 'Direksi',
                            'nama' => $person->nama, 'jabatan' => $person instanceof Karyawan ? ($person->jabatan?->nama ?? '—') : $person->jabatan,
                            'gaji_pokok' => $person->gaji_pokok, 'tunjangan_jabatan' => $person->tunjangan_jabatan, 'tunjangan_keluarga' => $person->tunjangan_keluarga,
                            'bpjs_tk' => $calc['bpjs_tk'], 'bpjs_k' => $calc['bpjs_k'], 'potongan_bpjs_tk' => $calc['pot_tk'], 'potongan_bpjs_kesehatan' => $calc['pot_k'],
                            'pendapatan_kotor' => $calc['pendapatan_kotor'], 'pendapatan_bersih' => $calc['pendapatan_bersih'], 'kasbon' => $kasbon, 'sisa_gaji_dibayar' => $sisaGaji,
                            'potongan_absensi' => $deduction['amount'], 'potongan_absensi_sebelum_batas' => $deduction['raw'], 'rincian_absensi' => json_encode($deduction, JSON_THROW_ON_ERROR),
                            'transfer_ke' => $person->nama_rekening, 'bank' => $person->bank, 'no_rekening' => $person->no_rekening,
                        ]);
                        foreach ($payments as $payment) {
                            $loan = $payment['loan'];
                            $remaining = (int) $loan->sisa_kas - $payment['amount'];
                            $loan->update(['total_bayar' => (int) $loan->total_bayar + $payment['amount'], 'sisa_kas' => $remaining, 'lunas' => $remaining === 0]);
                        }
                    }
                    if (! $kasBesar || $kasBesar->saldo < $total) {
                        throw ValidationException::withMessages(['bulan' => 'Saldo Kas Besar Tidak Cukup']);
                    }
                    $rekap->update(['total' => $total]);
                    $this->payroll->attendance->seal($lockedPeriod, $rekap->id);

                    return KasBesar::create([
                        'uraian' => $rekap->uraian, 'tanggal' => now()->toDateString(), 'nominal_transaksi' => $total, 'jenis_transaksi_id' => 2,
                        'saldo' => $kasBesar->saldo - $total, 'modal_investor_terakhir' => $kasBesar->modal_investor_terakhir,
                        'transfer_ke' => 'Msng2 Karyawan', 'bank' => 'BCA', 'no_rekening' => '-',
                    ]);
                });
            });
            if (! $cash) {
                return back()->with('error', 'Pembuatan gaji sedang diproses. Tunggu sebentar lalu muat ulang halaman.');
            }
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('error', 'Gaji belum tersimpan. Periode absensi mungkin sudah terkunci; muat ulang lalu ulangi transaksi yang sama.');
        }
        $this->sendWaNotification((int) $cash->nominal_transaksi, $cash);

        return to_route('billing.index')->with('success', 'Form Gaji Berhasil Dibuat');
    }

    private function sendWaNotification(int $total, KasBesar $kasBesar): void
    {
        try {
            $dbWa = new GroupWa;
            $group = $dbWa->where('untuk', 'kas-besar')->first();

            if (! $group) {
                return;
            }

            $pesan = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                        "*Form Gaji Karyawan*\n".
                        "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                        'Nilai :  *Rp. '.number_format($total, 0, ',', '.')."*\n\n".
                        "Ditransfer ke rek:\n\n".
                        "Nama     : Masing2 Karyawan\n\n".
                        "==========================\n".
                        "Sisa Saldo Kas Besar : \n".
                        'Rp. '.number_format($kasBesar->saldo, 0, ',', '.')."\n\n".
                        "Total Modal Investor : \n".
                        'Rp. '.number_format($kasBesar->modal_investor_terakhir, 0, ',', '.')."\n\n".
                        "Terima kasih 🙏🙏🙏\n";

            $dbWa->sendWa($group->nama_group, $pesan);
        } catch (\Throwable $e) {
            Log::warning('WA Notifikasi Gagal: '.$e->getMessage());
        }
    }

    public function attendancePdf(Request $request, int $employee): Response
    {
        $this->payroll->attendance->clear();
        $period = $this->period($request);
        $this->ensurePeriodAvailable($period);
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
        $this->ensurePeriodAvailable($period);

        return Pdf::loadView('billing.gaji.pdf', [...$this->previewData($period), 'is_preview' => true])
            ->setPaper('a4', 'landscape')->stream('Preview_Gaji_'.$period->format('m_Y').'.pdf');
    }

    public function previewExcel(Request $request): BinaryFileResponse
    {
        $this->payroll->attendance->clear();
        $period = $this->period($request);
        $this->ensurePeriodAvailable($period);

        return Excel::download(new GajiPreviewExport($period->month, $period->year, $this->payroll), 'Preview_Gaji_'.$period->format('m_Y').'.xlsx');
    }
}
