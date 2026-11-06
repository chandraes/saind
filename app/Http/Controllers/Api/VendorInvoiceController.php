<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VendorInvoiceIndexRequest;
use App\Http\Resources\MobilePaymentNoteResource;
use App\Http\Resources\MobileVendorInvoiceResource;
use App\Models\Transaksi;
use App\Models\TransaksiAdditional;
use App\Services\VendorOperationalBilling;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VendorInvoiceController extends Controller
{
    public function __construct(private VendorOperationalBilling $billing) {}

    public function index(VendorInvoiceIndexRequest $request): AnonymousResourceCollection
    {
        $items = $this->billing->invoices((int) $request->user()->vendor_id, $request->validated())->paginate(20)->withQueryString();
        $items->through(fn (object $invoice): array => $this->billing->invoiceSummary($invoice));

        return MobileVendorInvoiceResource::collection($items);
    }

    public function show(Request $request, string $kind, int $record): AnonymousResourceCollection
    {
        abort_unless(array_key_exists($kind, VendorOperationalBilling::CATEGORIES), 404);
        $request->validate(['page' => ['sometimes', 'integer', 'min:1']]);
        $vendor = (int) $request->user()->vendor_id;
        $query = $this->billing->invoiceQuery($vendor, $kind);
        if ($kind !== 'utama') {
            $query->where('status', 1);
        }
        $invoice = $query->findOrFail($record);
        $items = $this->billing->invoiceJourneys($vendor, $kind, $invoice)->orderByDesc('id')->paginate(20)->withQueryString();
        $items->getCollection()->each(fn (Transaksi|TransaksiAdditional $item) => $item->setAttribute('mobile_kind', $kind));

        return MobilePaymentNoteResource::collection($items)->additional(['invoice' => (new MobileVendorInvoiceResource($this->billing->invoiceSummary($invoice, $kind)))->resolve($request)]);
    }
}
