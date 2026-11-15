<?php

namespace App\Http\Controllers;

/**
 * Gaji Karyawan untuk eksekutif — General Manager & Direksi (baca saja): tabel Form Gaji lengkap per
 * periode (berjalan = pratinjau Form Gaji, final = rekap tersimpan), memakai
 * halaman & periode yang sama dengan menu Potongan Absensi HRD.
 */
class ExecutivePayrollController extends HrdAttendanceDeductionController
{
    protected bool $salary = true;

    protected string $indexRoute = 'executive.payroll';

    protected string $title = 'GAJI KARYAWAN';
}
