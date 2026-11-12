<?php

namespace App\Support;

use App\Models\InvoiceAddVendor;
use App\Models\TransaksiAdditional;

/**
 * Data tampilan detail invoice tambahan (tabel perjalanan dan ringkasan per
 * rute dengan PPN/PPh): dipakai halaman admin dan halaman invoice vendor.
 */
class AdditionalInvoiceRecap
{
    /** @return array<string, mixed> */
    public static function data(InvoiceAddVendor $invoice): array
    {
        $vendor = $invoice->vendor;

        $jenis = $invoice->jenis;
        $taId = $invoice->details->pluck('transaksi_additional_id');

        $data = TransaksiAdditional::with(['transaksi.kas_uang_jalan.vendor', 'transaksi.kas_uang_jalan.rute', 'transaksi.kas_uang_jalan.vehicle', 'rute', 'customer'])
            ->whereIn('id', $taId)->get();

        $dpp = $invoice->dpp;

        $groupedData = [];
        $totalKeseluruhan = 0;

        foreach ($data as $item) {
            // ... (Kode grouping Anda tetap tidak berubah sampai bagian bawah foreach)
            $customerName = $item->customer->nama ?? 'Tidak Diketahui';
            $ruteName = $item->rute->nama ?? 'Rute Lain';
            $tagihan_dari = $item->customer?->tagihan_dari == 1 ? 'tonase' : 'timbangan_bongkar';
            $jarak = (float) ($item->jarak ?? 0);
            $muatan = (float) ($item->transaksi->{$tagihan_dari} ?? 0);

            if (! isset($groupedData[$customerName])) {
                $groupedData[$customerName] = [
                    'rutes' => [],
                    'subtotal_customer' => 0,
                ];
            }

            if (! isset($groupedData[$customerName]['rutes'][$ruteName])) {
                $groupedData[$customerName]['rutes'][$ruteName] = [
                    'jarak' => $jarak,
                    'total_muatan' => 0,
                    'jumlah_trx' => 0,
                ];
            }

            $groupedData[$customerName]['rutes'][$ruteName]['total_muatan'] += $muatan;
            $groupedData[$customerName]['rutes'][$ruteName]['jumlah_trx']++;
        }

        foreach ($groupedData as $customerName => &$customerData) {
            foreach ($customerData['rutes'] as $ruteName => &$ruteData) {
                $ruteData['subtotal'] = (int) round($ruteData['jarak'] * $ruteData['total_muatan'] * $dpp);
                $customerData['subtotal_customer'] += $ruteData['subtotal'];
            }
            $totalKeseluruhan += $customerData['subtotal_customer'];
        }
        unset($customerData, $ruteData);

        // === TAMBAHAN PERHITUNGAN PAJAK UNTUK TAMPILAN ===
        $ppn = $vendor->ppn == 1 ? (int) round($totalKeseluruhan * 0.11) : 0;
        $pph = $vendor->pph == 1 ? (int) round($totalKeseluruhan * ($vendor->pph_val / 100)) : 0;
        $totalAkhir = $totalKeseluruhan + $ppn - $pph;
        // ==================================================

        $stringJenis = TransaksiAdditional::JENIS[$jenis] ?? $jenis;

        return [
            'data' => $data,
            'vendor' => $vendor,
            'jenis' => $jenis,
            'stringJenis' => $stringJenis,
            'invoice' => $invoice,
            'totalKeseluruhan' => $totalKeseluruhan,
            'groupedData' => $groupedData,
            'ppn' => $ppn,
            'pph' => $pph,
            'totalAkhir' => $totalAkhir,
        ];
    }
}
