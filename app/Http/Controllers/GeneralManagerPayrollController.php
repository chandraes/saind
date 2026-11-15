<?php

namespace App\Http\Controllers;

/**
 * Gaji Karyawan untuk General Manager (baca saja): tabel Form Gaji lengkap per
 * periode (berjalan = pratinjau Form Gaji, final = rekap tersimpan), memakai
 * halaman & periode yang sama dengan menu Potongan Absensi HRD.
 */
class GeneralManagerPayrollController extends HrdAttendanceDeductionController
{
    protected bool $salary = true;

    protected string $indexRoute = 'gm.payroll';

    protected string $title = 'GAJI KARYAWAN';
}
