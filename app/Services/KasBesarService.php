<?php

namespace App\Services;

use App\Models\KasBesar;
use Exception;

class KasBesarService
{
   public function potongSaldo(array $data)
    {
        // 1. SANITASI INPUT PERTAMA KALI
        // Bersihkan titik/karakter non-angka dan ubah jadi tipe integer/float agar aman dihitung
        $nominalRaw = $data['nominal_transaksi'] ?? 0;
        $nominal = (float) preg_replace('/[^0-9]/', '', $nominalRaw);

        if ($nominal <= 0) {
            throw new Exception("Nominal pemotongan harus lebih besar dari 0.");
        }

        // 2. LOCKING (Mencegah Race Condition)
        // Ambil baris terakhir dan kunci (lock) sementara agar transaksi lain antre membaca
        $kasBesar = KasBesar::orderBy('id', 'desc')->lockForUpdate()->first();

        // 3. SET NILAI DEFAULT JIKA KOSONG & VALIDASI
        $saldoAwal = $kasBesar ? $kasBesar->saldo : 0;
        $modalInvestorTerakhir = $kasBesar ? $kasBesar->modal_investor_terakhir : 0;

        if ($saldoAwal < $nominal) {
            throw new Exception("Saldo Kas Besar tidak mencukupi! Sisa saldo: Rp " . number_format($saldoAwal, 0, ',', '.'));
        }

        // 4. SUSUN DATA BERSIH (Mencegah Mass-Assignment Vulnerability)
        // Jangan timpa array $data bawaan, buat array baru khusus untuk di-insert
        $insertData = [
            'tanggal'                 => date('Y-m-d'),
            'jenis_transaksi_id'      => 2,
            'uraian'                  => $data['uraian'] ?? 'Pemotongan Kas Besar',
            'nominal_transaksi'       => $nominal,             // Simpan angka murni
            'saldo'                   => $saldoAwal - $nominal, // Ingat, dipotong artinya DIKURANGI
            'ban_ganti_invoice_id'    => $data['ban_ganti_invoice_id'] ?? null,
            'modal_investor_terakhir' => $modalInvestorTerakhir,
            'transfer_ke'             => isset($data['transfer_ke']) ? substr($data['transfer_ke'], 0, 15) : null,
            'no_rekening'             => $data['no_rekening'] ?? null,
            'bank'                    => $data['bank'] ?? null,
        ];

        // 5. INSERT DATA (Mencatat mutasi baru)
        return KasBesar::create($insertData);
    }
}
