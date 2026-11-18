<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\InvoiceTagihan;
use App\Models\Transaksi;
use App\Services\CustomerInvoicePayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Rekap Tagihan & Invoice untuk General Manager (baca saja), dari data yang
 * sama dengan halaman /rekap/tagihan-invoice: tab Tagihan (nota belum ditagih
 * per tambang) dan tab Invoice (invoice belum lunas).
 */
class MobileBillingInvoiceController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $rows = collect((new Customer)->tagihanInvoice());
        // Admin & SU: sisa tagihan untuk tombol Lunas/Cicil.
        $admin = $request->user()->isMobileAdministrator();
        $bills = $rows->filter(fn (array $row): bool => $row['tanggal_awal'] !== null)->values()
            ->map(fn (array $row, int $index): array => ['number' => $index + 1, 'customer_id' => $row['customer_id'], 'customer' => (string) $row['customer'],
                'from' => $row['tanggal_awal'], 'to' => $row['tanggal_akhir'], 'amount' => (int) round($row['total_transaksi'])]);
        $invoices = $rows->flatMap(fn (array $row): array => array_map(fn (array $invoice): array => ['id' => $invoice['id'], 'customer_id' => $row['customer_id'], 'customer' => (string) $row['customer'],
            'invoice' => (string) $invoice['periode'], 'payment' => (int) $invoice['total_tagihan'],
            'submitted_on' => $invoice['tanggal_submit_softcopy'], 'hardcopy_on' => $invoice['tanggal_hardcopy'],
            'tracking_number' => $invoice['no_resi'], 'validation_number' => $invoice['no_validasi'],
            'initial_amount' => (int) $invoice['tagihan_awal'], 'adjustment' => (int) $invoice['penyesuaian'],
            'penalty' => (int) $invoice['penalty'], 'ppn' => (int) $invoice['ppn'], 'pph' => (int) $invoice['pph'],
            'estimated_payment' => $invoice['estimasi_pembayaran'] === '-' ? null : $invoice['estimasi_pembayaran']]
            + ($admin ? ['remaining' => (int) round((float) $invoice['sisa_tagihan'])] : []), $row['invoices']))
            ->values()->map(fn (array $invoice, int $index): array => ['number' => $index + 1] + $invoice);

        return response()->json([
            'bills' => $bills,
            'invoices' => $invoices,
            'meta' => ['date' => now()->format('d-m-Y'), 'total_bill' => (int) $bills->sum('amount'), 'total_invoice' => (int) $invoices->sum('payment'),
                'grand_total' => (int) $bills->sum('amount') + (int) $invoices->sum('payment')],
        ]);
    }

    /** Admin & SU: lunasi ([installment] kosong) atau cicil Invoice Tagihan, sama dengan web. */
    public function pay(Request $request, InvoiceTagihan $invoice, CustomerInvoicePayment $payments): JsonResponse
    {
        $data = $request->validate(['remaining' => ['required', 'integer', 'min:0'], 'installment' => ['nullable', 'integer', 'min:1']]);
        $installment = $data['installment'] ?? null;
        [$invoice, $cash] = $payments->pay($invoice->id, $installment, $data['remaining']);

        return response()->json(['message' => $installment === null ? 'Invoice berhasil dilunasi.' : 'Cicilan berhasil dicatat.']
            + ($payments->notify($invoice, $cash, $installment !== null) ? [] : [
                'warning' => 'Notifikasi WA Kas Besar belum berhasil dikirim. Pembayaran sudah tersimpan; jangan ulangi.',
            ]));
    }

    public function transactions(Request $request, InvoiceTagihan $invoice): JsonResponse
    {
        abort_unless((int) $invoice->lunas === 0 && $invoice->customer()->where('status', 1)->exists(), 404);
        $request->validate(['page' => ['sometimes', 'integer', 'min:1']]);
        $weight = (int) $invoice->customer->tagihan_dari === 2 ? 'timbangan_bongkar' : 'tonase';
        $page = $invoice->transaksi()->with(['kas_uang_jalan.vehicle', 'kas_uang_jalan.rute'])
            ->orderBy('transaksis.id')->paginate(25);

        return response()->json([
            'data' => $page->getCollection()->map(fn (Transaksi $row, int $index): array => [
                'number' => $page->firstItem() + $index,
                'id' => (int) $row->id,
                'reference' => $row->nota_bongkar ?: $row->nota_muat,
                'date' => Transaksi::dateOnly($row->tanggal_bongkar),
                'vehicle' => $row->kas_uang_jalan?->vehicle?->nomor_lambung,
                'route' => $row->kas_uang_jalan?->rute?->nama,
                'weight' => $row->{$weight} === null ? null : (float) $row->{$weight},
                'amount' => (int) $row->nominal_tagihan,
            ]),
            'meta' => ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage()],
        ]);
    }
}
