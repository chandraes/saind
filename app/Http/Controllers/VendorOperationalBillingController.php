<?php

namespace App\Http\Controllers;

use App\Http\Requests\VendorInvoiceIndexRequest;
use App\Models\InvoiceAddVendor;
use App\Models\InvoiceBayar;
use App\Models\Vendor;
use App\Services\VendorOperationalBilling;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VendorOperationalBillingController extends Controller
{
    public function __construct(private VendorOperationalBilling $billing) {}

    public function unloading(Request $request): View|JsonResponse
    {
        $vendor = $this->vendor($request);
        $filters = $this->filters($request, ['waiting' => 'Menunggu bongkar'], 'waiting');
        $query = $this->billing->journeys($vendor)->where('status', 2);
        $this->billing->searchJourneys($query, $filters['search']);

        return $this->listing($request, $query, 'Nota Bongkar', 'nota-bongkar', $filters,
            fn ($item): array => ['cells' => $this->billing->unloadingCells($item), 'url' => route('vendor-operational.nota-bongkar.show', $item->id)]);
    }

    public function unloadingShow(Request $request, int $transaction): View
    {
        $item = $this->billing->journeys($this->vendor($request))->where('status', 2)->findOrFail($transaction);

        return view('billing.vendor-readonly.detail', ['title' => 'Nota Bongkar · '.$item->nota_muat, 'fields' => $this->billing->journey($item)]);
    }

    public function notes(Request $request): View|JsonResponse
    {
        $vendor = $this->vendor($request);
        if (! $request->has('kind') && ! $request->expectsJson()) {
            $counts = [];
            foreach (array_keys(VendorOperationalBilling::CATEGORIES) as $kind) {
                $counts[$kind] = $this->billing->payableNotes($vendor, $kind, (int) $request->user()->id)->count();
            }

            return view('billing.vendor-readonly.note-menu', ['vendorName' => $request->user()->vendor->nama, 'counts' => $counts]);
        }
        $filters = $this->filters($request, ['available' => 'Tersedia'], 'available');
        $kind = $filters['kind'];
        $query = $this->billing->payableNotes($vendor, $kind, (int) $request->user()->id);
        if ($kind === 'utama') {
            $this->billing->searchJourneys($query, $filters['search']);
        } elseif ($filters['search'] !== '') {
            $query->whereHas('transaksi', fn (Builder $query) => $this->billing->searchJourneys($query, $filters['search']));
        }

        return $this->listing($request, $query, $kind === 'utama' ? 'Nota Bayar' : VendorOperationalBilling::CATEGORIES[$kind], 'nota-bayar', $filters,
            fn ($item): array => ['cells' => $this->billing->paymentCells($kind === 'utama' ? $item : $item->transaksi, $kind === 'utama', $this->finance($request)),
                'url' => null]);
    }

    public function noteShow(Request $request, string $kind, int $record): View
    {
        $vendor = $this->vendor($request);
        $this->validKind($kind);
        $item = $this->billing->payableNotes($vendor, $kind, (int) $request->user()->id)->findOrFail($record);

        return view('billing.vendor-readonly.detail', ['title' => 'Nota Bayar · '.VendorOperationalBilling::CATEGORIES[$kind],
            'fields' => $this->billing->journey($kind === 'utama' ? $item : $item->transaksi, $kind === 'utama' ? null : $item, $this->finance($request))]);
    }

    public function invoices(VendorInvoiceIndexRequest $request): View|JsonResponse
    {
        $vendor = $this->vendor($request);
        $filters = $request->validated();
        $filters['status'] ??= 'unpaid';
        $query = $this->billing->invoices($vendor, $filters);
        $finance = $this->finance($request);
        $items = (clone $query)->simplePaginate(20)->withQueryString();
        $vendorName = Vendor::whereKey($vendor)->value('nama');
        $rows = $items->getCollection()->map(function ($invoice) use ($vendorName, $finance): array {
            $summary = $this->billing->invoiceSummary($invoice);

            return ['tanggal' => $summary['issued_on'], 'vendor' => $vendorName, 'invoice' => $summary['invoice_number'],
                'tempo' => $summary['due_on'], 'paid' => $summary['paid'],
                'total' => $finance ? number_format((float) $invoice->total, 0, ',', '.') : null,
                'paid_on' => $summary['paid_on'] ? Carbon::parse($summary['paid_on'])->format('d-m-Y') : null,
                'url' => route('vendor-operational.invoice.show', ['kind' => $summary['kind'], 'record' => $summary['id']])];
        })->all();
        $data = ['title' => 'Invoice Vendor', 'vendorName' => $vendorName, 'filters' => $filters, 'rows' => $rows, 'next' => $items->nextPageUrl(), 'offset' => ($items->currentPage() - 1) * 20,
            'showTotal' => $finance];
        if ($request->expectsJson()) {
            return response()->json(['rows' => view('billing.vendor-readonly.invoice-rows', $data)->render(), 'next' => $data['next']]);
        }

        // Total seluruh invoice yang sesuai filter, bukan hanya baris yang sudah dimuat.
        $data['grandTotal'] = $finance ? number_format((float) (clone $query)->reorder()->sum('total'), 0, ',', '.') : null;

        return view('billing.vendor-readonly.invoice-index', $data);
    }

    public function invoiceShow(Request $request, string $kind, int $record): View|JsonResponse
    {
        $vendor = $this->vendor($request);
        $this->validKind($kind);
        $request->validate(['page' => ['sometimes', 'integer', 'min:1']]);
        $invoice = $this->billing->invoiceQuery($vendor, $kind)->findOrFail($record);
        $query = $this->billing->invoiceJourneys($vendor, $kind, $invoice);
        $items = $query->orderByDesc('id')->simplePaginate(20)->withQueryString();
        $finance = $this->finance($request);
        $rows = $items->getCollection()->map(fn ($item): array => ['cells' => $this->billing->paymentCells($kind === 'utama' ? $item : $item->transaksi, $kind === 'utama', $finance), 'url' => null])->all();
        $data = ['title' => 'Invoice · '.($kind === 'utama' ? $invoice->periode : $invoice->periode_invoice), 'invoice' => $this->invoiceHeader($invoice, $kind),
            'finance' => $finance ? $this->billing->invoiceFinance($invoice) : null, 'columns' => array_keys($this->billing->paymentColumns($kind === 'utama', $finance)), 'rows' => $rows, 'next' => $items->nextPageUrl(), 'offset' => ($items->currentPage() - 1) * 20];
        if ($request->expectsJson()) {
            return response()->json(['rows' => view('billing.vendor-readonly.rows', $data)->render(), 'next' => $data['next']]);
        }

        return view('billing.vendor-readonly.invoice-detail', $data);
    }

    /** Identity, dates and payment status for the invoice detail header. */
    private function invoiceHeader(InvoiceBayar|InvoiceAddVendor $invoice, string $kind): array
    {
        $paid = $kind === 'utama' ? (int) $invoice->lunas === 1 : (int) $invoice->is_finished === 1;
        $status = $kind !== 'utama' && (int) $invoice->status === 0 ? 'cart' : ($paid ? 'paid' : 'unpaid');
        $issued = $kind === 'utama' ? $invoice->tanggal : $invoice->created_at;
        $due = $invoice->tempo ? Carbon::parse($invoice->tempo)->startOfDay() : null;
        $days = $due ? (int) today()->diffInDays($due, false) : null;
        $dueNote = match (true) {
            $due === null || $paid => null,
            $days < 0 => 'Lewat '.abs($days).' hari',
            $days === 0 => 'Jatuh tempo hari ini',
            default => $days.' hari lagi',
        };

        return ['number' => $kind === 'utama' ? $invoice->periode : $invoice->periode_invoice, 'category' => VendorOperationalBilling::CATEGORIES[$kind],
            'vendor' => $invoice->vendor?->nama, 'issued_on' => $issued ? Carbon::parse($issued)->locale('id')->translatedFormat('d F Y') : null,
            'due_on' => $due?->locale('id')->translatedFormat('d F Y'), 'due_note' => $dueNote, 'overdue' => $days !== null && $days < 0 && ! $paid,
            'status' => $status, 'status_label' => ['cart' => 'Dalam keranjang', 'paid' => 'Lunas', 'unpaid' => 'Belum lunas'][$status]];
    }

    private function listing(Request $request, Builder $query, string $title, string $page, array $filters, callable $project): View|JsonResponse
    {
        $items = $query->orderByDesc('id')->simplePaginate(20)->withQueryString();
        $data = ['title' => $title, 'page' => $page, 'filters' => $filters, 'categories' => VendorOperationalBilling::CATEGORIES,
            'vendorName' => $request->user()->vendor->nama, 'columns' => $page === 'nota-bayar' ? array_keys($this->billing->paymentColumns($filters['kind'] === 'utama', $this->finance($request))) : [],
            'rows' => $items->getCollection()->map($project)->all(), 'next' => $items->nextPageUrl(), 'offset' => ($items->currentPage() - 1) * 20];
        $rowsView = $page === 'nota-bongkar' ? 'billing.vendor-readonly.unloading-rows' : 'billing.vendor-readonly.rows';
        if ($request->expectsJson()) {
            return response()->json(['rows' => view($rowsView, $data)->render(), 'next' => $data['next']]);
        }

        return view($page === 'nota-bongkar' ? 'billing.vendor-readonly.unloading' : 'billing.vendor-readonly.notes', $data);
    }

    private function filters(Request $request, array $statuses, string $defaultStatus = 'all'): array
    {
        $filters = $request->validate(['kind' => ['sometimes', Rule::in(array_keys(VendorOperationalBilling::CATEGORIES))],
            'status' => ['sometimes', Rule::in(array_keys($statuses))], 'search' => ['nullable', 'string', 'max:100'], 'page' => ['sometimes', 'integer', 'min:1']]);

        return ['kind' => $filters['kind'] ?? 'utama', 'status' => $filters['status'] ?? $defaultStatus, 'search' => trim($filters['search'] ?? ''), 'statuses' => $statuses];
    }

    private function finance(Request $request): bool
    {
        return $request->user()->canViewVendorFinance();
    }

    private function validKind(string $kind): void
    {
        abort_unless(array_key_exists($kind, VendorOperationalBilling::CATEGORIES), 404);
    }

    private function vendor(Request $request): int
    {
        abort_unless($request->user()?->isVendorAccount() && $request->user()->vendor_id && $request->user()->vendor()->exists(), 403);
        abort_unless($request->isMethod('GET') || $request->isMethod('HEAD'), 403);

        return (int) $request->user()->vendor_id;
    }
}
