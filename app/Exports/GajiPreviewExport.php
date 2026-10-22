<?php

namespace App\Exports;

use App\Services\PayrollService;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class GajiPreviewExport implements FromView, WithColumnFormatting
{
    public function __construct(protected int $bulan, protected int $tahun, protected PayrollService $payroll) {}

    public function columnFormats(): array
    {
        return [
            // Kolom D sampai O (Gaji Pokok sampai Sisa Gaji) diformat ribuan
            'D' => '#,##0',
            'E' => '#,##0',
            'F' => '#,##0',
            'G' => '#,##0',
            'H' => '#,##0',
            'I' => '#,##0',
            'J' => '#,##0',
            'K' => '#,##0',
            'L' => '#,##0',
            'M' => '#,##0',
            'N' => '#,##0', 'O' => '#,##0',
        ];
    }

    public function view(): View
    {
        return view('billing.gaji.export-excel', [
            'data' => $this->payroll->employeesForPeriod($this->bulan, $this->tahun),
            'direksi' => $this->payroll->directorsForPeriod($this->bulan, $this->tahun),
            'bulan' => $this->bulan, // Ini harus angka (misal: 1)
            'tahun' => $this->tahun,
            'payroll' => $this->payroll,
        ]);
    }
}
