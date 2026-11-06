<?php

namespace App\Http\Controllers;

use App\Models\InvoiceAddVendor;
use App\Models\InvoiceBayar;
use App\Models\Vendor;
use App\Services\VendorOperationalBilling;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            fn ($item): array => ['cells' => $this->billing->paymentCells($kind === 'utama' ? $item : $item->transaksi, $kind === 'utama'),
                'url' => null]);
    }

    public function noteShow(Request $request, string $kind, int $record): View
    {
        $vendor = $this->vendor($request);
        $this->validKind($kind);
        $item = $this->billing->payableNotes($vendor, $kind, (int) $request->user()->id)->findOrFail($record);

        return view('billing.vendor-readonly.detail', ['title' => 'Nota Bayar · '.VendorOperationalBilling::CATEGORIES[$kind],
            'fields' => $this->billing->journey($kind === 'utama' ? $item : $item->transaksi, $kind === 'utama' ? null : $item)]);
    }

    public function invoices(Request $request): View|JsonResponse
    {
        $vendor = $this->vendor($request);
        $rules = ['status' => ['sometimes', Rule::in(['unpaid', 'paid', 'all'])], 'search' => ['nullable', 'string', 'max:100'], 'start_date' => ['nullable', 'date_format:Y-m-d'], 'end_date' => ['nullable', 'date_format:Y-m-d'], 'page' => ['sometimes', 'integer', 'min:1']];
        if ($request->filled('start_date')) {
            $rules['end_date'][] = 'after_or_equal:start_date';
        }
        $filters = $request->validate($rules);
        $filters['status'] ??= 'unpaid';
        $base = $this->invoiceQuery($vendor, 'utama')
            ->select('id', 'periode', 'tanggal', 'tempo', 'updated_at')->selectRaw("'utama' as kind, lunas as is_paid")->toBase();
        $extra = InvoiceAddVendor::query()->where('vendor_id', $vendor)->where('status', 1)
            ->whereIn('jenis', array_keys(VendorOperationalBilling::CATEGORIES))
            ->select('id', 'periode', 'created_at as tanggal', 'tempo', 'updated_at', 'jenis as kind', 'is_finished as is_paid')->toBase();
        $query = DB::query()->fromSub($base->unionAll($extra), 'vendor_invoices');
        if ($filters['status'] !== 'all') {
            $query->where('is_paid', $filters['status'] === 'paid' ? 1 : 0);
        }
        if ($request->filled('search')) {
            $query->where('periode', 'like', '%'.$filters['search'].'%');
        }
        if ($request->filled('start_date')) {
            $query->whereDate('tempo', '>=', $filters['start_date']);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tempo', '<=', $filters['end_date']);
        }
        $items = $query->orderByDesc('tanggal')->orderByDesc('id')->orderBy('kind')->simplePaginate(20)->withQueryString();
        $vendorName = Vendor::whereKey($vendor)->value('nama');
        $rows = $items->getCollection()->map(fn ($invoice): array => [
            'tanggal' => $invoice->tanggal === null ? null : substr($invoice->tanggal, 0, 10),
            'vendor' => $vendorName,
            'invoice' => $invoice->kind === 'utama' ? $invoice->periode
                : (new InvoiceAddVendor)->forceFill(['jenis' => $invoice->kind, 'periode' => $invoice->periode])->periode_invoice,
            'tempo' => $invoice->tempo,
            'paid' => (int) $invoice->is_paid === 1,
            'paid_on' => (int) $invoice->is_paid === 1 && $invoice->updated_at ? Carbon::parse($invoice->updated_at)->format('d-m-Y') : null,
            'url' => route('vendor-operational.invoice.show', ['kind' => $invoice->kind, 'record' => $invoice->id]),
        ])->all();
        $data = ['title' => 'Invoice Vendor', 'vendorName' => $vendorName, 'filters' => $filters, 'rows' => $rows, 'next' => $items->nextPageUrl(), 'offset' => ($items->currentPage() - 1) * 20];
        if ($request->expectsJson()) {
            return response()->json(['rows' => view('billing.vendor-readonly.invoice-rows', $data)->render(), 'next' => $data['next']]);
        }

        return view('billing.vendor-readonly.invoice-index', $data);
    }

    public function invoiceShow(Request $request, string $kind, int $record): View|JsonResponse
    {
        $vendor = $this->vendor($request);
        $this->validKind($kind);
        $request->validate(['page' => ['sometimes', 'integer', 'min:1']]);
        $invoice = $this->invoiceQuery($vendor, $kind)->findOrFail($record);
        if ($kind === 'utama') {
            $query = $this->billing->journeys($vendor)->whereIn('id', $invoice->invoice_bayar_details()->select('transaksi_id'));
        } else {
            $query = $this->billing->additionals($vendor, $kind)->whereIn('id', $invoice->details()->select('transaksi_additional_id'));
        }
        $items = $query->orderByDesc('id')->simplePaginate(20)->withQueryString();
        $rows = $items->getCollection()->map(fn ($item): array => ['cells' => $this->billing->paymentCells($kind === 'utama' ? $item : $item->transaksi, $kind === 'utama'), 'url' => null])->all();
        $data = ['title' => 'Invoice · '.($kind === 'utama' ? $invoice->periode : $invoice->periode_invoice), 'invoiceFields' => array_merge(...array_values($this->invoiceCells($invoice, $kind))), 'columns' => array_keys($this->billing->paymentColumns($kind === 'utama')), 'rows' => $rows, 'next' => $items->nextPageUrl(), 'offset' => ($items->currentPage() - 1) * 20];
        if ($request->expectsJson()) {
            return response()->json(['rows' => view('billing.vendor-readonly.rows', $data)->render(), 'next' => $data['next']]);
        }

        return view('billing.vendor-readonly.invoice-detail', $data);
    }

    private function invoiceQuery(int $vendor, string $kind): Builder
    {
        return $kind === 'utama' ? InvoiceBayar::query()->with('vendor')->where('vendor_id', $vendor)
            : InvoiceAddVendor::query()->with('vendor')->where('vendor_id', $vendor)->where('jenis', $kind);
    }

    private function invoiceCells(InvoiceBayar|InvoiceAddVendor $invoice, string $kind): array
    {
        $paid = $kind === 'utama' ? (int) $invoice->lunas === 1 : (int) $invoice->is_finished === 1;
        $status = $kind !== 'utama' && (int) $invoice->status === 0 ? 'Dalam keranjang' : ($paid ? 'Lunas' : 'Belum lunas');

        return ['Invoice' => ['Vendor' => $invoice->vendor?->nama, 'Periode' => $kind === 'utama' ? $invoice->periode : $invoice->periode_invoice,
            'Jenis' => VendorOperationalBilling::CATEGORIES[$kind]],
            'Tanggal' => ['Tanggal' => $kind === 'utama' ? $invoice->tanggal : $invoice->created_at?->format('Y-m-d'), 'Jatuh Tempo' => $invoice->tempo],
            'Status' => ['Pembayaran' => $status]];
    }

    private function listing(Request $request, Builder $query, string $title, string $page, array $filters, callable $project): View|JsonResponse
    {
        $items = $query->orderByDesc('id')->simplePaginate(20)->withQueryString();
        $data = ['title' => $title, 'page' => $page, 'filters' => $filters, 'categories' => VendorOperationalBilling::CATEGORIES,
            'vendorName' => $request->user()->vendor->nama, 'columns' => $page === 'nota-bayar' ? array_keys($this->billing->paymentColumns($filters['kind'] === 'utama')) : [],
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

    private function validKind(string $kind): void
    {
        abort_unless(array_key_exists($kind, VendorOperationalBilling::CATEGORIES), 404);
    }

    private function vendor(Request $request): int
    {
        abort_unless($request->user()?->role === 'vendor-operational' && $request->user()->vendor_id && $request->user()->vendor()->exists(), 403);
        abort_unless($request->isMethod('GET') || $request->isMethod('HEAD'), 403);

        return (int) $request->user()->vendor_id;
    }
}
