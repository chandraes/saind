<?php

namespace App\Services;

use Carbon\Carbon;

class PayrollService
{
    /**
     * Menghitung Komponen Gaji, BPJS, dan PPh 21 (TER/Progresif)
     * * @param object $person Data karyawan
     * @param int $bulan Bulan penggajian (1-12). Default: bulan ini.
     * @param float $akumulasi_pph_jan_nov Total PPh yang SUDAH dipotong dari Jan-Nov (Wajib diisi jika bulan 12)
     * @return array
     */
    public function calculateComponent($person, $bulan = null, $akumulasi_pph_jan_nov = 0)
    {
        $bulan = $bulan ?? (int) date('n');

        // 1. Definisikan Penghasilan Tetap/Teratur
        $gajiPokok = $person->gaji_pokok;
        $tunjangan = $person->tunjangan_jabatan + $person->tunjangan_keluarga;
        $penghasilanTeratur = $gajiPokok + $tunjangan;

        // 2. Perhitungan BPJS Kesehatan (Cap Rp 12.000.000)
        // Sesuai Perpres 64/2020 & aturan 2025/2026 (Asumsi cap naik ke 12jt)
        $basis_bpjs_kes = min($penghasilanTeratur, 12000000);

        $bpjs_kes_perusahaan = ($person->apa_bpjs_kesehatan == 1) ? $basis_bpjs_kes * 0.04 : 0; // Masuk Bruto Pajak
        $bpjs_kes_karyawan   = ($person->apa_bpjs_kesehatan == 1) ? $basis_bpjs_kes * 0.01 : 0; // Pengurang THP

        // 3. Perhitungan BPJS Ketenagakerjaan
        // PENTING: Untuk pajak, pisahkan JKK/JKM (Kena Pajak) dengan JHT/JP (Tidak Kena Pajak)
        // Rate Asumsi: JKK(0.24%) + JKM(0.30%) = 0.54% (Penambah Bruto Pajak)
        // Rate Asumsi: JHT(3.7%) + JP(2%) = 5.7% (Benefit Kantor, Bukan Objek Pajak)

        $basis_bpjs_tk = $penghasilanTeratur;
        // Cap khusus JP (Jaminan Pensiun) biasanya ada batas (misal 10jt), disini kita samakan 12jt utk simplifikasi
        $basis_jp = min($penghasilanTeratur, 12000000);

        if ($person->apa_bpjs_tk == 1) {
            // Dibayar Perusahaan (Benefit)
            $jkk_jkm_perusahaan = $basis_bpjs_tk * 0.0054; // Kena Pajak (Bruto)
            $jht_perusahaan     = $basis_bpjs_tk * 0.037;  // Tidak Kena Pajak
            $jp_perusahaan      = $basis_jp * 0.02;        // Tidak Kena Pajak

            // Total Benefit TK (Hanya utk display, tidak semua masuk pajak)
            $bpjs_tk_benefit_total = $jkk_jkm_perusahaan + $jht_perusahaan + $jp_perusahaan;

            // Dibayar Karyawan (Potongan) -> JHT(2%) + JP(1%)
            $pot_jht_karyawan = $basis_bpjs_tk * 0.02;
            $pot_jp_karyawan  = $basis_jp * 0.01;
            $pot_tk_karyawan  = $pot_jht_karyawan + $pot_jp_karyawan;
        } else {
            $jkk_jkm_perusahaan = 0;
            $bpjs_tk_benefit_total = 0;
            $pot_tk_karyawan = 0;
        }

        // 4. Hitung Penghasilan Bruto (Dasar Pengenaan Pajak)
        // Rumus: Gaji + Tunjangan + BPJS Kes(Ktr) + JKK/JKM(Ktr)
        $bruto_sebulan = $penghasilanTeratur + $bpjs_kes_perusahaan + $jkk_jkm_perusahaan;

        // 5. Hitung PPh 21
        $pph21 = 0;
        $metode_pajak = '';

        if ($bulan < 12) {
            // === METODE TER (Januari - November) ===
            $metode_pajak = 'TER Bulanan';

            // Tentukan Kategori TER (A, B, C)
            $kode_ptkp = $this->getPtkpCode($person->status_menikah, $person->jumlah_anak);
            $kategori_ter = $this->getTerCategory($kode_ptkp);

            // Ambil Tarif
            $tarif_ter = $this->getTerRate($kategori_ter, $bruto_sebulan);

            // Hitung Pajak
            $pph21 = floor($bruto_sebulan * $tarif_ter);

        } else {
            // === METODE PROGRESIF (Desember) ===
            $metode_pajak = 'Pasal 17 (Tahunan)';

            // Disetahunkan (Idealnya ambil data real Jan-Nov dari database + Bruto Des)
            // Di sini kita simulasi: Bruto Bulan Ini * 12
            $bruto_setahun = $bruto_sebulan * 12;

            // Pengurang 1: Biaya Jabatan (5% Bruto, Max 6jt/thn)
            $biaya_jabatan = min($bruto_setahun * 0.05, 6000000);

            // Pengurang 2: Iuran Pensiun/JHT dibayar karyawan (Setahun)
            $iuran_karyawan_setahun = ($pot_tk_karyawan + $bpjs_kes_karyawan) * 12; // Opsional: BPJS Kes karyawan kadang dianggap pengurang, kadang tidak tergantung kebijakan, UU HPP memperbolehkan iuran pensiun/JHT. Kita masukkan JHT/JP saja biasanya.
            $iuran_pensiun_setahun = $pot_tk_karyawan * 12;

            // Hitung Netto
            $netto_setahun = $bruto_setahun - $biaya_jabatan - $iuran_pensiun_setahun;

            // Hitung PTKP
            $ptkp_setahun = $this->getPtkpAmount($person->status_menikah, $person->jumlah_anak);

            // Hitung PKP (Penghasilan Kena Pajak)
            $pkp = $netto_setahun - $ptkp_setahun;
            $pkp = floor($pkp / 1000) * 1000; // Pembulatan ribuan ke bawah

            if ($pkp <= 0) {
                $pph_terutang_setahun = 0;
            } else {
                $pph_terutang_setahun = $this->calculateProgressiveRate($pkp);
            }

            // PPh Desember = Pajak Setahun - Pajak yg sudah dibayar Jan-Nov
            $pph21 = $pph_terutang_setahun - $akumulasi_pph_jan_nov;

            // Handle jika lebih bayar (dinolkan untuk payroll, lebih bayar diurus via SPT)
            if ($pph21 < 0) $pph21 = 0;
        }

        // 6. Hitung Take Home Pay
        $pendapatan_bersih = $penghasilanTeratur - $pot_tk_karyawan - $bpjs_kes_karyawan - $pph21;

        return [
            'gaji_pokok' => $gajiPokok,
            'tunjangan' => $tunjangan,
            'bruto_pajak' => $bruto_sebulan, // Dasar pengenaan TER

            // Komponen BPJS
            'bpjs_tk_benefit' => $bpjs_tk_benefit_total, // Dibayar kantor
            'bpjs_k_benefit' => $bpjs_kes_perusahaan,   // Dibayar kantor

            // Potongan Karyawan
            'pot_tk' => $pot_tk_karyawan,
            'pot_k' => $bpjs_kes_karyawan,

            // Pajak
            'metode_pajak' => $metode_pajak,
            'pph' => $pph21,

            // Hasil Akhir
            'pendapatan_kotor' => $bruto_sebulan, // Note: Pendapatan kotor di slip gaji biasanya Gaji+Tunjangan, tapi utk pajak pakai Bruto Pajak
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

    // ================= HELPER FUNCTIONS (PRIVATE) =================

    private function getPtkpCode($status_menikah, $jumlah_anak)
    {
        $anak = min($jumlah_anak, 3);
        $prefix = ($status_menikah == 1) ? 'K' : 'TK';
        return $prefix . '/' . $anak;
    }

    private function getPtkpAmount($status_menikah, $jumlah_anak)
    {
        $ptkp = 54000000; // Diri Sendiri
        if ($status_menikah == 1) $ptkp += 4500000; // Kawin
        $anak = min($jumlah_anak, 3);
        $ptkp += ($anak * 4500000); // Tanggungan
        return $ptkp;
    }

    private function getTerCategory($ptkp_code)
    {
        // Kategori A: TK/0, TK/1, K/0
        if (in_array($ptkp_code, ['TK/0', 'TK/1', 'K/0'])) return 'A';
        // Kategori B: TK/2, TK/3, K/1, K/2
        if (in_array($ptkp_code, ['TK/2', 'TK/3', 'K/1', 'K/2'])) return 'B';
        // Kategori C: K/3
        if ($ptkp_code == 'K/3') return 'C';

        return 'A'; // Default
    }

    private function getTerRate($category, $bruto)
    {
        // Simulasi Tabel TER (Sebagian data untuk representasi)
        // Anda disarankan menyimpan tabel lengkap TER PP 58/2023 di Database

        if ($category == 'A') {
            if ($bruto <= 5400000) return 0;
            if ($bruto <= 5650000) return 0.0025;
            if ($bruto <= 5950000) return 0.005;
            if ($bruto <= 6300000) return 0.0075;
            if ($bruto <= 6750000) return 0.01;
            if ($bruto <= 7500000) return 0.0125;
            if ($bruto <= 8550000) return 0.015;
            if ($bruto <= 9650000) return 0.0175;
            if ($bruto <= 10050000) return 0.02;
            if ($bruto <= 10350000) return 0.0225;
            if ($bruto <= 10700000) return 0.025;
            if ($bruto <= 11050000) return 0.03;
            if ($bruto <= 11600000) return 0.035;
            if ($bruto <= 12500000) return 0.04;
            if ($bruto <= 13750000) return 0.05;
            if ($bruto <= 15100000) return 0.06;
            if ($bruto <= 16950000) return 0.07;
            if ($bruto <= 19750000) return 0.08;
            if ($bruto <= 24150000) return 0.09;
            return 0.10; // Fallback sederhana utk range atas
        }

        if ($category == 'B') {
            if ($bruto <= 6200000) return 0;
            if ($bruto <= 6500000) return 0.0025;
            if ($bruto <= 6850000) return 0.005;
            if ($bruto <= 7300000) return 0.0075;
            if ($bruto <= 9200000) return 0.01;
            if ($bruto <= 10750000) return 0.015;
            if ($bruto <= 11250000) return 0.02;
            if ($bruto <= 11600000) return 0.025;
            if ($bruto <= 12600000) return 0.03;
            if ($bruto <= 13600000) return 0.04;
            if ($bruto <= 14950000) return 0.05;
            if ($bruto <= 16400000) return 0.06;
            if ($bruto <= 18450000) return 0.07;
            return 0.08; // Fallback
        }

        if ($category == 'C') {
            if ($bruto <= 6600000) return 0;
            if ($bruto <= 6950000) return 0.0025;
            if ($bruto <= 7350000) return 0.005;
            if ($bruto <= 7800000) return 0.0075;
            if ($bruto <= 8850000) return 0.01;
            if ($bruto <= 9800000) return 0.0125;
            if ($bruto <= 10950000) return 0.015;
            if ($bruto <= 11200000) return 0.0175;
            if ($bruto <= 12050000) return 0.02;
            return 0.03; // Fallback
        }

        return 0;
    }

    private function calculateProgressiveRate($pkp)
    {
        $tax = 0;

        // Layer 1: 5% (0 - 60jt)
        if ($pkp > 60000000) {
            $tax += 60000000 * 0.05;
            $pkp -= 60000000;
        } else {
            $tax += $pkp * 0.05;
            return $tax;
        }

        // Layer 2: 15% (>60jt - 250jt)
        if ($pkp > 190000000) {
            $tax += 190000000 * 0.15;
            $pkp -= 190000000;
        } else {
            $tax += $pkp * 0.15;
            return $tax;
        }

        // Layer 3: 25% (>250jt - 500jt)
        if ($pkp > 250000000) {
            $tax += 250000000 * 0.25;
            $pkp -= 250000000;
        } else {
            $tax += $pkp * 0.25;
            return $tax;
        }

        // Layer 4: 30% (>500jt - 5M)
        if ($pkp > 4500000000) {
            $tax += 4500000000 * 0.30;
            $pkp -= 4500000000;
        } else {
            $tax += $pkp * 0.30;
            return $tax;
        }

        // Layer 5: 35% (>5M)
        $tax += $pkp * 0.35;

        return $tax;
    }
}
