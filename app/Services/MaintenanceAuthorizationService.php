<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MaintenanceAuthorizationService
{
    public function __construct(private MaintenanceAuthorizationQueue $queue) {}

    public function table(Request $request, string $type): JsonResponse
    {
        $data = $request->validate([
            'draw' => ['nullable', 'integer', 'min:0'], 'start' => ['nullable', 'integer', 'min:0'],
            'length' => ['nullable', 'integer'], 'search.value' => ['nullable', 'string', 'max:200'],
        ]);
        $isAki = $type === 'aki';
        $queueType = $isAki ? 'battery' : 'filter-oil';
        $total = $this->queue->pending($queueType)->count();
        $query = $this->queue->pending($queueType, $data['search']['value'] ?? '');
        $filtered = (clone $query)->count();
        $start = (int) ($data['start'] ?? 0);
        $invoices = $query->offset($start)->limit(max(1, min(100, (int) ($data['length'] ?? 25))))->get();
        $info = $this->queue->describe($queueType, $invoices);
        $rows = $invoices->values()->map(function ($invoice, int $index) use ($isAki, $type, $info, $start): array {
            ['warnings' => $warnings, 'balance' => $balance] = $info[$invoice->id];

            return [
                'no' => $start + $index + 1, 'no_invoice' => $invoice->no_invoice,
                'tanggal' => ($isAki ? Carbon::parse($invoice->tanggal) : $invoice->created_at)->format('d-m-Y'),
                'vehicle' => $invoice->vehicle->nomor_lambung ?? '—',
                'pembayaran' => MaintenanceAuthorizationQueue::PAYMENT_LABELS[$invoice->pembayaran] ?? $invoice->pembayaran,
                'nominal' => (int) $invoice->total_nominal, 'saldo_uj' => $balance,
                'saldo_kurang' => $invoice->pembayaran === 'uj_ditahan' && $balance < (int) $invoice->total_nominal,
                'jumlah' => $invoice->details->count(),
                'actions' => view('billing.otorisasi-maintenance.actions', compact('invoice', 'type', 'warnings'))->render(),
            ];
        });

        return response()->json(['draw' => (int) ($data['draw'] ?? 0), 'recordsTotal' => $total, 'recordsFiltered' => $filtered, 'data' => $rows]);
    }
}
