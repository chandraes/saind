<?php

namespace App\Exports;

use App\Models\Karyawan;
use App\Models\Direksi;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class GajiPreviewExport implements FromView, WithColumnFormatting
{
    protected $bulan, $tahun, $payroll;

    public function __construct($bulan, $tahun, $payroll) {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->payroll = $payroll;
    }

    public function columnFormats(): array
    {
        return [
            // Kolom D sampai N (Gaji Pokok sampai Sisa Gaji) diformat ribuan
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
            'N' => '#,##0',
        ];
    }

    public function view(): View
    {
        return view('billing.gaji.export-excel', [
            'data' => Karyawan::where('status', 'aktif')->with(['jabatan', 'kas_bon', 'kas_bon_cicilan'])->get(),
            'direksi' => Direksi::where('status', 'aktif')->get(),
            'bulan' => $this->bulan, // Ini harus angka (misal: 1)
            'tahun' => $this->tahun,
            'payroll' => $this->payroll
        ]);
    }


}
