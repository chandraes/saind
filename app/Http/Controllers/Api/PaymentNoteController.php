<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MobilePaymentNoteResource;
use App\Models\Transaksi;
use App\Models\TransaksiAdditional;
use App\Services\VendorOperationalBilling;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class PaymentNoteController extends Controller
{
    public function __construct(private VendorOperationalBilling $billing) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate(['kind' => ['sometimes', Rule::in(array_keys(VendorOperationalBilling::CATEGORIES))],
            'search' => ['nullable', 'string', 'max:100'], 'page' => ['sometimes', 'integer', 'min:1']]);
        $kind = $filters['kind'] ?? 'utama';
        $query = $this->billing->payableNotes((int) $request->user()->vendor_id, $kind, (int) $request->user()->id);
        $search = trim($filters['search'] ?? '');
        if ($kind === 'utama') {
            $this->billing->searchJourneys($query, $search);
        } elseif ($search !== '') {
            $query->whereHas('transaksi', fn (Builder $query) => $this->billing->searchJourneys($query, $search));
        }
        $summary = $this->billing->paymentWeightSummary($query, $kind);
        if ($kind === 'utama' && $request->user()->canViewVendorFinance()) {
            $summary += $this->billing->paymentFinanceSummary($query);
        }
        $items = $query->orderByDesc('id')->paginate(20)->withQueryString();
        $items->getCollection()->each(fn (Transaksi|TransaksiAdditional $item) => $item->setAttribute('mobile_kind', $kind));

        return MobilePaymentNoteResource::collection($items)->additional(['meta' => ['categories' => VendorOperationalBilling::CATEGORIES] + $summary]);
    }

    public function show(Request $request, string $kind, int $record): MobilePaymentNoteResource
    {
        abort_unless(array_key_exists($kind, VendorOperationalBilling::CATEGORIES), 404);
        $item = $this->billing->payableNotes((int) $request->user()->vendor_id, $kind, (int) $request->user()->id)->findOrFail($record);
        $additional = $item instanceof TransaksiAdditional ? $item : null;
        $item->setAttribute('mobile_kind', $kind);
        $item->setAttribute('mobile_fields', $this->billing->journey($additional ? $item->transaksi : $item, $additional, $request->user()->canViewVendorFinance()));

        return new MobilePaymentNoteResource($item);
    }
}
