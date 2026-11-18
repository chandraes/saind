<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UnloadingNoteCompletion;
use App\Services\VendorInvoicePayment;
use App\Services\VendorOperationalBilling;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin & SU (mobile): isi Nota Bongkar, lunasi dan batalkan Invoice Vendor.
 * Logika sama dengan web lewat UnloadingNoteCompletion dan VendorInvoicePayment.
 */
class MobileVendorBillingActionController extends Controller
{
    public function completeUnloading(Request $request, int $transaction, UnloadingNoteCompletion $completion): JsonResponse
    {
        $data = $request->validate([
            'nota_bongkar' => ['required', 'string', 'max:100'],
            'timbangan_bongkar' => ['required', 'numeric', 'min:0'],
            'tanggal_bongkar' => ['required', 'date_format:Y-m-d'],
            'gross_bongkar' => ['nullable', 'numeric', 'min:0'],
            'tarra_bongkar' => ['nullable', 'numeric', 'min:0'],
        ]);
        $completion->complete($transaction, $data);

        return response()->json(['message' => 'Nota bongkar berhasil disimpan.']);
    }

    public function settleInvoice(Request $request, string $kind, int $record, VendorInvoicePayment $payments): JsonResponse
    {
        abort_unless(array_key_exists($kind, VendorOperationalBilling::CATEGORIES), 404);
        $payments->settle($kind, $record, $this->amount($request));

        return response()->json(['message' => 'Invoice berhasil dilunasi.']);
    }

    public function cancelInvoice(Request $request, int $record, VendorInvoicePayment $payments): JsonResponse
    {
        $payments->cancel($record, $this->amount($request));

        return response()->json(['message' => 'Invoice berhasil dibatalkan. Transaksinya kembali ke Nota Bayar.']);
    }

    /** Nominal yang ditampilkan di dialog; wajib agar yang diproses sama dengan yang dilihat. */
    private function amount(Request $request): int
    {
        return (int) $request->validate(['amount' => ['required', 'integer', 'min:0']])['amount'];
    }
}
