<?php

namespace App\Services;

use Carbon\Carbon;

class PayrollService
{
    public function calculateComponent($person)
    {
        $gapokPlus = $person->gaji_pokok + $person->tunjangan_jabatan + $person->tunjangan_keluarga;

        $bpjs_tk = $person->apa_bpjs_tk == 1 ? $gapokPlus * 0.0489 : 0;
        $bpjs_k = $person->apa_bpjs_kesehatan == 1 ? $gapokPlus * 0.04 : 0;
        $pot_tk = $person->apa_bpjs_tk == 1 ? $gapokPlus * 0.02 : 0;
        $pot_k = $person->apa_bpjs_kesehatan == 1 ? $gapokPlus * 0.01 : 0;

        $pendapatan_kotor = $gapokPlus + $bpjs_tk + $bpjs_k;
        $pendapatan_bersih = $gapokPlus - $pot_tk - $pot_k;

        return [
            'bpjs_tk' => $bpjs_tk,
            'bpjs_k' => $bpjs_k,
            'pot_tk' => $pot_tk,
            'pot_k' => $pot_k,
            'pendapatan_kotor' => $pendapatan_kotor,
            'pendapatan_bersih' => $pendapatan_bersih,
        ];
    }

    public function calculateKasbon($karyawan, $bulan, $tahun)
    {
        $kasbon_cicil = 0;
        $now = Carbon::createFromDate($tahun, $bulan, 1);

        // Cicilan Aktif
        $cicilan = $karyawan->kas_bon_cicilan->where('lunas', 0)->first();
        if ($cicilan) {
            $mulai = Carbon::createFromDate($cicilan->mulai_tahun, $cicilan->mulai_bulan, 1);
            if ($now->greaterThanOrEqualTo($mulai)) {
                $kasbon_cicil = $cicilan->cicilan_nominal;
            }
        }

        // Kasbon Biasa (Lunas Sekaligus)
        $kasbon_biasa = $karyawan->kas_bon->where('lunas', 0)->sum('nominal');

        return $kasbon_cicil + $kasbon_biasa;
    }
}
