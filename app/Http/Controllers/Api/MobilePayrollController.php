<?php

namespace App\Http\Controllers\Api;

/** Gaji Karyawan General Manager di aplikasi (baca saja), seluruh kolom Form Gaji. */
class MobilePayrollController extends MobileAttendanceDeductionController
{
    protected bool $salary = true;
}
