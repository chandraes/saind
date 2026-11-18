<?php

namespace App\Http\Controllers\Api;

use App\Services\PayrollGeneration;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Gaji Karyawan di aplikasi: eksekutif baca saja, seluruh kolom Form Gaji.
 * Admin & SU juga menyimpan gaji periode dan memperbarui absensinya, sama
 * dengan web (PayrollGeneration).
 */
class MobilePayrollController extends MobileAttendanceDeductionController
{
    protected bool $salary = true;

    public function index(Request $request): JsonResponse
    {
        $response = parent::index($request);
        $meta = $response->getData(true)['meta'];
        if (! $request->user()->isMobileAdministrator() || $meta['period']['final']) {
            return $response;
        }
        // Admin & SU: sidik pratinjau untuk Simpan Gaji dan kapan gaji boleh diproses.
        $payroll = app(PayrollService::class);
        $period = Carbon::create($meta['period']['year'], $meta['period']['month'], 1)->startOfDay();
        $data = $response->getData(true);
        $data['meta']['actions'] = [
            'revision' => $payroll->revision($period->month, $period->year),
            'can_store' => $payroll->canProcess($period),
            'processing_starts_at' => $payroll->processingStartsAt($period)->toIso8601String(),
        ];

        return response()->json($data);
    }

    public function refresh(Request $request, PayrollGeneration $generation): JsonResponse
    {
        $period = $this->payrollPeriod($request);
        $generation->refreshAttendance($period->month, $period->year);

        return response()->json(['message' => 'Data absensi diperbarui. Tinjau rincian sebelum menyimpan gaji.']);
    }

    public function store(Request $request, PayrollGeneration $generation): JsonResponse
    {
        $period = $this->payrollPeriod($request);
        $revision = $request->validate(['payroll_revision' => ['required', 'string', 'size:64']])['payroll_revision'];
        $cash = $generation->store($period, $revision);

        return response()->json(['message' => 'Form Gaji berhasil dibuat. Kas Besar keluar '.number_format((int) $cash->nominal_transaksi, 0, ',', '.').'.']
            + ($generation->notify($cash) ? [] : ['warning' => 'Notifikasi WA Kas Besar belum berhasil dikirim. Gaji sudah tersimpan; jangan ulangi.']));
    }

    /** Periode gaji, dibatasi sampai satu periode berikutnya seperti web. */
    private function payrollPeriod(Request $request): Carbon
    {
        $data = $request->validate(['bulan' => ['required', 'integer', 'between:1,12'], 'tahun' => ['required', 'integer', 'between:2000,2100']]);
        $period = Carbon::create((int) $data['tahun'], (int) $data['bulan'], 1)->startOfDay();
        if ($period->greaterThan(now()->startOfMonth()->addMonth())) {
            throw ValidationException::withMessages(['bulan' => 'Preview dibatasi sampai satu periode berikutnya.']);
        }

        return $period;
    }
}
