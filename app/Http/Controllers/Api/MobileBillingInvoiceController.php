<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;

/**
 * Rekap Tagihan & Invoice untuk General Manager (baca saja), dari data yang
 * sama dengan halaman /rekap/tagihan-invoice: tab Tagihan (nota belum ditagih
 * per tambang) dan tab Invoice (invoice belum lunas).
 */
class MobileBillingInvoiceController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $rows = collect((new Customer)->tagihanInvoice());
        $bills = $rows->filter(fn (array $row): bool => $row['tanggal_awal'] !== null)->values()
            ->map(fn (array $row, int $index): array => ['number' => $index + 1, 'customer_id' => $row['customer_id'], 'customer' => (string) $row['customer'],
                'from' => $row['tanggal_awal'], 'to' => $row['tanggal_akhir'], 'amount' => (int) round($row['total_transaksi'])]);
        $invoices = $rows->flatMap(fn (array $row): array => array_map(fn (array $invoice): array => ['customer_id' => $row['customer_id'], 'customer' => (string) $row['customer'],
            'invoice' => (string) $invoice['periode'], 'payment' => (int) $invoice['total_tagihan'],
            'submitted_on' => $invoice['tanggal_submit_softcopy'], 'hardcopy_on' => $invoice['tanggal_hardcopy'],
            'tracking_number' => $invoice['no_resi'], 'validation_number' => $invoice['no_validasi'],
            'initial_amount' => (int) $invoice['tagihan_awal'], 'adjustment' => (int) $invoice['penyesuaian'],
            'penalty' => (int) $invoice['penalty'], 'ppn' => (int) $invoice['ppn'], 'pph' => (int) $invoice['pph'],
            'estimated_payment' => $invoice['estimasi_pembayaran'] === '-' ? null : $invoice['estimasi_pembayaran']], $row['invoices']))
            ->values()->map(fn (array $invoice, int $index): array => ['number' => $index + 1] + $invoice);

        return response()->json([
            'bills' => $bills,
            'invoices' => $invoices,
            'meta' => ['date' => now()->format('d-m-Y'), 'total_bill' => (int) $bills->sum('amount'), 'total_invoice' => (int) $invoices->sum('payment'),
                'grand_total' => (int) $bills->sum('amount') + (int) $invoices->sum('payment')],
        ]);
    }
}
