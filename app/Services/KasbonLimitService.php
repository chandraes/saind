<?php

namespace App\Services;

use App\Models\Karyawan;
use App\Models\KasBon;
use App\Models\KasBonCicilan;
use Carbon\Carbon;

class KasbonLimitService
{
    /** @return array{income: int, ordinary_due: int, installment_due: int, ordinary_limit: int, ordinary_available: int, installment_limit: int, period: string, loans: list<array{jenis: string, tanggal: string, sisa: int, angsuran: int, berlaku: bool}>} */
    public function summary(Karyawan $employee, Carbon $period, bool $lock = false): array
    {
        $period = $period->copy()->startOfMonth()->startOfDay();
        $end = $period->copy()->endOfMonth()->toDateString();
        $ordinaryQuery = KasBon::where('karyawan_id', $employee->id)->where('lunas', false)->where('void', false)->orderBy('id');
        $installmentQuery = KasBonCicilan::where('karyawan_id', $employee->id)->where('lunas', false)->orderBy('id');
        if ($lock) {
            $ordinaryQuery->lockForUpdate();
            $installmentQuery->lockForUpdate();
        }
        $ordinary = $ordinaryQuery->get();
        $installments = $installmentQuery->get();
        $ordinaryDue = 0;
        $installmentDue = 0;
        $loans = [];
        foreach ($ordinary as $loan) {
            $remaining = max(0, (int) $loan->sisa_kas);
            $eligible = $loan->getRawOriginal('tanggal') <= $end;
            $ordinaryDue += $eligible ? $remaining : 0;
            $loans[] = ['jenis' => 'Potong gaji', 'tanggal' => $loan->tanggal, 'sisa' => $remaining, 'angsuran' => 0, 'berlaku' => $eligible];
        }
        foreach ($installments as $loan) {
            $remaining = max(0, (int) $loan->sisa_kas);
            $start = Carbon::create((int) $loan->mulai_tahun, (int) $loan->mulai_bulan, 1)->startOfDay();
            $eligible = $loan->getRawOriginal('tanggal') <= $end && $period->greaterThanOrEqualTo($start);
            $monthly = max(0, min((int) $loan->cicilan_nominal, $remaining));
            $installmentDue += $eligible ? $monthly : 0;
            $loans[] = ['jenis' => 'Cicilan', 'tanggal' => $loan->id_tanggal, 'sisa' => $remaining, 'angsuran' => $monthly, 'berlaku' => $eligible];
        }
        $income = (int) $employee->gaji_pokok + (int) $employee->tunjangan_jabatan + (int) $employee->tunjangan_keluarga;
        $ordinaryLimit = intdiv(max(0, $income - $installmentDue) * 50, 100);

        return [
            'income' => $income, 'ordinary_due' => $ordinaryDue, 'installment_due' => $installmentDue,
            'ordinary_limit' => $ordinaryLimit, 'ordinary_available' => max(0, $ordinaryLimit - $ordinaryDue),
            'installment_limit' => intdiv(max(0, $income - $ordinaryDue) * 30, 100),
            'period' => $period->locale('id')->translatedFormat('F Y'), 'loans' => $loans,
        ];
    }
}
