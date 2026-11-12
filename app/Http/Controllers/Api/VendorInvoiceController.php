<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VendorInvoiceIndexRequest;
use App\Http\Resources\MobilePaymentNoteResource;
use App\Http\Resources\MobileVendorInvoiceResource;
use App\Models\Transaksi;
use App\Models\TransaksiAdditional;
use App\Models\Vendor;
use App\Services\VendorOperationalBilling;
use App\Support\MobileVendorScope;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VendorInvoiceController extends Controller
{
    public function __construct(private VendorOperationalBilling $billing) {}

    public function index(VendorInvoiceIndexRequest $request): AnonymousResourceCollection
    {
        $query = $this->billing->invoices(MobileVendorScope::id($request), $request->validated());
        $finance = $request->user()->canViewVendorFinance();
        $items = (clone $query)->paginate(20)->withQueryString();
        // Nama vendor per invoice hanya untuk operasional (seluruh vendor).
        $operasional = $request->user()->isOperasional();
        $names = $operasional ? Vendor::whereIn('id', $items->getCollection()->pluck('vendor_id')->unique())->get(['id', 'nama', 'nickname'])->keyBy('id') : collect();
        $items->through(fn (object $invoice): array => $this->billing->invoiceSummary($invoice)
            + ($operasional ? ['vendor_name' => $names->get($invoice->vendor_id)?->nickname ?: $names->get($invoice->vendor_id)?->nama] : [])
            + ($finance ? ['total_amount' => (int) $invoice->total] : []));
        $collection = MobileVendorInvoiceResource::collection($items);

        // Grand total seluruh invoice sesuai filter, bukan hanya halaman ini.
        return $finance ? $collection->additional(['meta' => ['grand_total_amount' => (int) (clone $query)->reorder()->sum('total')]]) : $collection;
    }

    public function show(Request $request, string $kind, int $record): AnonymousResourceCollection
    {
        abort_unless(array_key_exists($kind, VendorOperationalBilling::CATEGORIES), 404);
        $request->validate(['page' => ['sometimes', 'integer', 'min:1']]);
        $vendor = MobileVendorScope::id($request);
        $query = $this->billing->invoiceQuery($vendor, $kind);
        if ($kind !== 'utama') {
            $query->where('status', 1);
        }
        $invoice = $query->findOrFail($record);
        $items = $this->billing->invoiceJourneys($vendor, $kind, $invoice)->orderByDesc('id')->paginate(20)->withQueryString();
        $items->getCollection()->each(fn (Transaksi|TransaksiAdditional $item) => $item->setAttribute('mobile_kind', $kind));

        $summary = (new MobileVendorInvoiceResource($this->billing->invoiceSummary($invoice, $kind)
            + ($request->user()->isOperasional() ? ['vendor_name' => $invoice->vendor?->nickname ?: $invoice->vendor?->nama] : [])))->resolve($request);
        if ($request->user()->canViewVendorFinance()) {
            $summary['finance'] = $this->billing->invoiceFinance($invoice);
        }

        return MobilePaymentNoteResource::collection($items)->additional(['invoice' => $summary]);
    }
}
