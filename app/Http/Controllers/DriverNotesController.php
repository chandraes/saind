<?php

namespace App\Http\Controllers;

use App\Models\LoadNoteDraft;
use App\Models\Transaksi;
use App\Services\MobileNotePayment;
use App\Services\VendorOperationalBilling;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverNotesController extends Controller
{
    public function __construct(private VendorOperationalBilling $billing) {}

    public function index(Request $request, string $kind): View|JsonResponse
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:100'], 'page' => ['sometimes', 'integer', 'min:1']]);
        $query = $this->notes($request, $kind);
        if ($kind === 'muat') {
            $query->search($filters['search'] ?? '');
        } elseif (($search = trim($filters['search'] ?? '')) !== '') {
            $query->whereHas('kas_uang_jalan.rute', fn (Builder $route): Builder => $route->where('nama', 'like', '%'.$search.'%'));
        }

        $items = $query->latest('id')->simplePaginate(25)->withQueryString();
        $driverView = true;
        $indexUrl = route('driver.notes', $kind);
        $notes = $items;
        $offset = $items->firstItem() - 1;
        $journeyRows = $kind === 'bongkar' ? $items->map(fn (Transaksi $item): array => [
            'cells' => $this->billing->unloadingCells($item),
            'url' => route('driver.notes.show', ['bongkar', $item->id]),
        ])->all() : [];
        $next = $items->nextPageUrl();

        if ($request->expectsJson()) {
            $rows = $kind === 'muat'
                ? view('billing.nota-muat.rows', compact('notes', 'filters', 'driverView'))->render()
                : view('billing.vendor-readonly.unloading-rows', ['rows' => $journeyRows, 'offset' => $offset])->render();

            return response()->json(compact('rows', 'next'));
        }

        return $kind === 'muat'
            ? view('billing.nota-muat.index', compact('notes', 'filters', 'driverView', 'indexUrl') + [
                'canReview' => false, 'canFilterVendor' => false,
            ])
            : view('billing.vendor-readonly.unloading', compact('next', 'offset', 'filters', 'driverView', 'indexUrl') + [
                'rows' => $journeyRows,
            ]);
    }

    public function show(Request $request, string $kind, int $id, MobileNotePayment $payments): View
    {
        $item = $this->notes($request, $kind)->findOrFail($id);
        $vehicleId = $request->attributes->get('driverVehicle')->getKey();
        $journey = $item instanceof Transaksi ? $item : null;
        if ($item instanceof LoadNoteDraft && $item->status === 'processed') {
            $journey = $item->transaksi_id ? $this->journeys($vehicleId)->find($item->transaksi_id) : null;
        }

        $detail = $journey ? $payments->finalDetail($journey)
            : ($item->status === 'processed' ? [] : $payments->estimateDetail($item));
        $money = $detail['payment'] ?? [];
        $destination = $detail['transfer_destination'] ?? [];

        return view('billing.vendor-readonly.detail', [
            'title' => $kind === 'muat' ? 'Nota Muat Masuk' : 'Nota Bongkar',
            'fields' => [
                'Nolam' => $request->attributes->get('driverVehicle')->nomor_lambung,
                'Nota Muat' => $item->ticket_number ?? $item->nota_muat,
                'Nota Bongkar' => $journey?->nota_bongkar,
                'Tanggal Muat' => $item instanceof LoadNoteDraft ? $item->loaded_on?->format('d-m-Y') : $item->tanggal_muat,
                'Tanggal Bongkar' => $journey?->tanggal_bongkar,
                'Rute' => $item->rute?->nama ?? $journey?->kas_uang_jalan?->rute?->nama,
                'Uang Jalan' => $this->rupiah($money['gross_amount'] ?? null),
                'UJ Ditahan Sparepart' => $this->rupiah($money['sparepart_withheld'] ?? null),
                'UJ Ditahan Ban' => $this->rupiah($money['tyre_withheld'] ?? null),
                'Nominal Transfer' => $this->rupiah($money['net_amount'] ?? null),
                'Penerima' => $destination['recipient_name'] ?? null,
                'Bank' => $destination['bank'] ?? null,
                'Nomor Rekening' => $destination['account_number'] ?? null,
            ],
        ]);
    }

    private function notes(Request $request, string $kind): Builder
    {
        $vehicleId = $request->attributes->get('driverVehicle')->getKey();

        return $kind === 'muat'
            ? LoadNoteDraft::where('vehicle_id', $vehicleId)->with(['rute:id,nama', 'vendor:id,nama', 'creator:id,name'])
            : $this->billing->pendingUnloading(null)
                ->whereHas('kas_uang_jalan', fn (Builder $query): Builder => $query->where('vehicle_id', $vehicleId));
    }

    private function journeys(int $vehicleId): Builder
    {
        return $this->billing->journeys(null)->whereIn('status', [2, 3])
            ->whereHas('kas_uang_jalan', fn (Builder $query): Builder => $query->where('vehicle_id', $vehicleId));
    }

    private function rupiah(?int $amount): ?string
    {
        return $amount === null ? null : 'Rp '.number_format($amount, 0, ',', '.');
    }
}
