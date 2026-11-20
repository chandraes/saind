<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoiceAddVendor;
use App\Models\Transaksi;
use App\Models\TransaksiAdditional;
use App\Models\Vendor;
use App\Services\AdditionalVendorPaymentCart;
use App\Services\VendorPaymentCart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileVendorPaymentCartController extends Controller
{
    public function __construct(private VendorPaymentCart $cart, private AdditionalVendorPaymentCart $additional) {}

    public function show(Request $request, Vendor $vendor): JsonResponse
    {
        $page = $this->page($request);
        $notes = $this->cart->notes($request->user(), $vendor)->sortBy('id')->values();

        return response()->json([
            'data' => $notes->forPage($page, 25)->map(fn (Transaksi $note): array => [
                'id' => $note->id,
                'nota_bongkar' => $note->nota_bongkar,
                'nominal' => (float) $note->nominal_bayar,
            ])->values()->all(),
            'last_page' => max(1, (int) ceil($notes->count() / 25)),
            'quote' => $this->cart->quote($vendor, $notes),
            'tempo' => now()->addDays($vendor->jatuh_tempo_hari ?? 0)->toDateString(),
        ]);
    }

    public function store(Request $request, Vendor $vendor): JsonResponse
    {
        $ids = $this->selection($request);
        $count = $this->cart->add($request->user(), $vendor, $ids);

        return response()->json(['message' => $count.' transaksi berhasil dimasukkan ke keranjang.']);
    }

    public function destroy(Request $request, Vendor $vendor): JsonResponse
    {
        $this->cart->remove($request->user(), $vendor, $this->selection($request));

        return response()->json(['message' => 'Keranjang berhasil diperbarui.']);
    }

    public function checkout(Request $request, Vendor $vendor): JsonResponse
    {
        $data = $request->validate([
            'tempo' => ['required', 'date_format:Y-m-d'],
            'fingerprint' => ['required', 'string', 'size:64'],
        ]);
        $invoice = $this->cart->checkout($request->user(), $vendor, $data['tempo'], $data['fingerprint']);

        return response()->json(['message' => 'Invoice Vendor berhasil dibuat.', 'invoice_id' => $invoice->id], 201);
    }

    public function showAdditional(Request $request, Vendor $vendor, string $kind): JsonResponse
    {
        abort_unless(isset(TransaksiAdditional::JENIS[$kind]), 404);
        $invoice = $this->additional->pending($vendor, $kind);
        if ($invoice === null) {
            return response()->json(['data' => null]);
        }

        $count = $invoice->details()->count();
        $page = $this->page($request);

        return response()->json(['data' => [
            'id' => $invoice->id,
            'dpp' => (float) $invoice->dpp,
            'fingerprint' => $this->additional->fingerprint($invoice),
            'nominal' => (float) $invoice->nominal,
            'ppn' => (float) $invoice->ppn,
            'pph' => (float) $invoice->pph,
            'total' => (float) $invoice->total,
            'count' => $count,
            'last_page' => max(1, (int) ceil($count / 25)),
            'notes' => Transaksi::query()->join('invoice_add_vendor_details as detail', 'detail.transaksi_id', 'transaksis.id')
                ->where('detail.invoice_add_vendor_id', $invoice->id)
                ->orderBy('detail.id')->forPage($page, 25)->get(['detail.id', 'transaksis.nota_bongkar']),

            'tempo' => $invoice->tempo ?? now()->addDays((int) ($vendor->{'jatuh_tempo_'.$kind} ?? 0))->toDateString(),
        ]]);
    }

    public function storeAdditional(Request $request, Vendor $vendor, string $kind): JsonResponse
    {
        abort_unless(isset(TransaksiAdditional::JENIS[$kind]), 404);
        $data = $request->validate([
            'dpp' => ['required', 'numeric', 'min:0', 'decimal:0,4'],
            'transaksi_ids' => ['required', 'array', 'min:1', 'max:100'],
            'transaksi_ids.*' => ['required', 'integer', 'distinct', 'min:1'],
        ]);
        $this->additional->add($vendor, $kind, $data['transaksi_ids'], (float) $data['dpp']);

        return response()->json(['message' => 'Nota tambahan berhasil dimasukkan ke keranjang.']);
    }

    public function returnAdditional(Request $request, Vendor $vendor, string $kind, InvoiceAddVendor $invoice): JsonResponse
    {
        $data = $request->validate(['fingerprint' => ['required', 'string', 'size:64']]);
        $this->additional->finish($vendor, $kind, $invoice, null, $data['fingerprint']);

        return response()->json(['message' => 'Nota tambahan berhasil dikembalikan.']);
    }

    public function checkoutAdditional(Request $request, Vendor $vendor, string $kind, InvoiceAddVendor $invoice): JsonResponse
    {
        $data = $request->validate([
            'tempo' => ['required', 'date_format:Y-m-d'],
            'fingerprint' => ['required', 'string', 'size:64'],
        ]);
        $this->additional->finish($vendor, $kind, $invoice, $data['tempo'], $data['fingerprint']);

        return response()->json(['message' => 'Invoice Vendor tambahan berhasil dibuat.', 'invoice_id' => $invoice->id], 201);
    }

    private function page(Request $request): int
    {
        $input = $request->validate(['page' => ['sometimes', 'integer', 'min:1']]);

        return (int) ($input['page'] ?? 1);
    }

    /** @return list<int>|null */
    private function selection(Request $request): ?array
    {
        $data = $request->validate([
            'all' => ['sometimes', 'accepted', 'prohibits:transaksi_ids'],
            'transaksi_ids' => ['required_without:all', 'array', 'min:1', 'max:100'],
            'transaksi_ids.*' => ['required', 'integer', 'distinct', 'min:1'],
        ]);

        return $data['transaksi_ids'] ?? null;
    }
}
