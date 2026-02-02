<?php

namespace App\Helpers;

class PayrollHelper
{
    public static function terbilang($angka)
    {
        $angka = abs($angka);
        $baca = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
        $terbilang = "";

        if ($angka < 12) {
            $terbilang = " " . $baca[$angka];
        } else if ($angka < 20) {
            $terbilang = self::terbilang($angka - 10) . " belas ";
        } else if ($angka < 100) {
            $terbilang = self::terbilang($angka / 10) . " puluh " . self::terbilang($angka % 10);
        } else if ($angka < 200) {
            $terbilang = " seratus" . self::terbilang($angka - 100);
        } else if ($angka < 1000) {
            $terbilang = self::terbilang($angka / 100) . " ratus " . self::terbilang($angka % 100);
        } else if ($angka < 2000) {
            $terbilang = " seribu" . self::terbilang($angka - 1000);
        } else if ($angka < 1000000) {
            $terbilang = self::terbilang($angka / 1000) . " ribu" . self::terbilang($angka % 1000);
        } else if ($angka < 1000000000) {
            $terbilang = self::terbilang($angka / 1000000) . " juta " . self::terbilang($angka % 1000000);
        }

        return trim($terbilang);
    }
}
