<?php

namespace App\Services;

use App\Models\KasVendor;
use Exception;

class KasVendorService
{
    /**
     * Menambahkan hutang ke dalam Ledger Kas Vendor (1 Tabel)
     */
    public function tambahHutang(array $data)
    {
        // 1. SANITASI INPUT
        // Membersihkan format pemisah ribuan agar kalkulasi matematika akurat
        $nominalRaw = $data['nominal_transaksi'] ?? 0;
        $nominal = (float) preg_replace('/[^0-9]/', '', $nominalRaw);

        if ($nominal <= 0) {
            throw new Exception("Nominal penambahan hutang harus lebih besar dari 0.");
        }

        // 2. LOCKING (Mencegah Race Condition)
        $kas = KasVendor::where('vendor_id', $data['vendor_id'])
                        ->orderBy('id', 'desc')
                        ->lockForUpdate()
                        ->first();

        // 3. KALKULASI SALDO SEBELUMNYA
        $pinjamanAwal = $data['nominal_transaksi'] ?? 0;
        $sisaAwal = $kas ? $kas->sisa : 0;

        // 4. SUSUN DATA BERSIH
        $insertData = [
            'vendor_id'         => $data['vendor_id'],
            'vehicle_id'        => $data['vehicle_id'] ?? null,
            'tanggal'           => date('Y-m-d H:i:s'),
            'ban_ganti_invoice_id' => $data['ban_ganti_invoice_id'] ?? null,
            'uraian'            => $data['uraian'] ?? 'Penambahan hutang vendor',
            'pinjaman'          => $nominal, // Bertambah
            'sisa'              => $sisaAwal + $nominal,     // Sisa hutang bertambah
        ];

        // 5. INSERT DATA
        return KasVendor::create($insertData);
    }
}
