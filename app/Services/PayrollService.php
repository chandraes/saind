<?php

namespace App\Services;

use Carbon\Carbon;

class PayrollService
{
    public function calculateComponent($person)
    {
        $gapokPlus = $person->gaji_pokok + $person->tunjangan_jabatan + $person->tunjangan_keluarga;

        $bpjs_tk = $person->apa_bpjs_tk == 1 ? $gapokPlus * 0.0489 : 0;

        $pot_tk = $person->apa_bpjs_tk == 1 ? $gapokPlus * 0.02 : 0;

        // tambahkan kondisi gapokPlus maksimal 12 juta, jika lebih dari 12 juta, maka potongan hanya dihitung dari 12 juta
        // sesuai aturan BPJS Tahun 2026
        if ($gapokPlus > 12000000) {
            $bpjs_k = $person->apa_bpjs_kesehatan == 1 ? 12000000 * 0.04 : 0;
            $pot_k = $person->apa_bpjs_kesehatan == 1 ? 12000000  * 0.01 : 0;
        } else {
            $bpjs_k = $person->apa_bpjs_kesehatan == 1 ? $gapokPlus * 0.04 : 0;
            $pot_k = $person->apa_bpjs_kesehatan == 1 ? $gapokPlus * 0.01 : 0;
        }

        $pendapatan_kotor = $gapokPlus + $bpjs_tk + $bpjs_k;

        if ($pendapatan_kotor > 10000000) {
             $ptkp = 4500000 * 12; // PTKP (penghasilan tidak kena pajak) untuk individu
            // jika punya keluarga tambah 4.5 juta
            $ptkp += $person->status_menikah == 1 ? 4500000 * 12 : 0;
            // jika punya anak max 3 anak, tiap anak tambah 4.5 juta

            // pendapatan kotor > 10 juta, maka pph 21 aktif = $pendapatan kotor * 5%
            $anak_count = min($person->jumlah_anak, 3);
            $ptkp += $anak_count * 4500000;

            $ptkp_final = $ptkp/12;

            $pph = $pendapatan_kotor > 10000000 ? ($pendapatan_kotor - ($pendapatan_kotor * 0.05)) - $ptkp_final : 0;
        } else {
            $pph = 0;
        }



        $pendapatan_bersih = $gapokPlus - $pot_tk - $pot_k - $pph;
        // $pendapatan_bersih = $gapokPlus - $pot_tk - $pot_k;

        return [
            'bpjs_tk' => $bpjs_tk,
            'bpjs_k' => $bpjs_k,
            'pot_tk' => $pot_tk,
            'pot_k' => $pot_k,
            'pph' => $pph,
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
