<?php

namespace App\Services;

use App\Models\AkiGantiInvoice;
use App\Models\AkiGantiInvoiceDetail;
use App\Models\AkiLog;
use App\Models\FilterOliGantiInvoice;
use App\Models\FilterOliLog;
use App\Models\UjDitahan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MaintenanceAuthorizationService
{
    public function table(Request $request, string $type): JsonResponse
    {
        $data = $request->validate([
            'draw' => ['nullable', 'integer', 'min:0'], 'start' => ['nullable', 'integer', 'min:0'],
            'length' => ['nullable', 'integer'], 'search.value' => ['nullable', 'string', 'max:200'],
        ]);
        $isAki = $type === 'aki';
        $query = $isAki ? AkiGantiInvoice::query() : FilterOliGantiInvoice::query();
        $query->where('status', 'pending');
        $total = (clone $query)->count();
        $search = trim($data['search']['value'] ?? '');
        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('no_invoice', 'like', '%'.$search.'%')
                    ->orWhere('pembayaran', 'like', '%'.$search.'%')
                    ->orWhereHas('vehicle', fn ($vehicle) => $vehicle->where('nomor_lambung', 'like', '%'.$search.'%'));
            });
        }
        $filtered = (clone $query)->count();
        $start = (int) ($data['start'] ?? 0);
        $invoices = $query->with(['vehicle', $isAki ? 'details.posisiAki' : 'details.kategori'])
            ->orderBy('created_at')->orderBy('id')->offset($start)->limit(max(1, min(100, (int) ($data['length'] ?? 25))))->get();
        $vehicleIds = $invoices->pluck('vehicle_id')->unique();
        $balances = UjDitahan::whereIn('vehicle_id', $vehicleIds)->where('saldo', '>', 0)
            ->selectRaw('vehicle_id, SUM(saldo) as available')->groupBy('vehicle_id')->pluck('available', 'vehicle_id');
        $logsQuery = $isAki ? AkiLog::query() : FilterOliLog::query();
        $detailTable = $isAki ? 'aki_ganti_invoice_details' : 'filter_oli_ganti_invoice_details';
        $invoiceTable = $isAki ? 'aki_ganti_invoices' : 'filter_oli_ganti_invoices';
        $invoiceKey = $isAki ? 'aki_ganti_invoice_id' : 'filter_oli_ganti_invoice_id';
        $logTable = $isAki ? 'aki_logs' : 'filter_oli_logs';
        $categoryKey = $isAki ? 'posisi_aki_id' : 'kategori_filter_oli_mesin_id';
        $previousIds = DB::table($detailTable.' as detail')
            ->join($invoiceTable.' as invoice', 'detail.'.$invoiceKey, '=', 'invoice.id')
            ->whereIn('invoice.id', $invoices->modelKeys())
            ->selectSub(function ($query) use ($logTable, $categoryKey): void {
                $query->from($logTable.' as previous_log')->select('previous_log.id')
                    ->whereColumn('previous_log.vehicle_id', 'invoice.vehicle_id')
                    ->whereColumn('previous_log.'.$categoryKey, 'detail.'.$categoryKey)
                    ->whereColumn('previous_log.created_at', '<=', 'detail.created_at')
                    ->orderByDesc('previous_log.created_at')->orderByDesc('previous_log.id')->limit(1);
            }, 'previous_log_id')->pluck('previous_log_id')->filter();
        $logs = $logsQuery->whereIn('id', $previousIds)->orderByDesc('created_at')->orderByDesc('id')->get()->groupBy('vehicle_id');
        $rows = $invoices->values()->map(function ($invoice, int $index) use ($isAki, $type, $logs, $balances, $start): array {
            $warnings = [];
            foreach ($invoice->details as $detail) {
                $previous = ($logs->get($invoice->vehicle_id) ?? collect())->first(fn ($log) => ($isAki ? $log->posisi_aki_id === $detail->posisi_aki_id : $log->kategori_filter_oli_mesin_id === $detail->kategori_filter_oli_mesin_id) && $log->created_at <= $detail->created_at);
                if ($isAki) {
                    $days = AkiGantiInvoiceDetail::usageDays($previous?->created_at, $detail->created_at);
                    if ($days !== null && $days < 365) {
                        $warnings[] = ['label' => $detail->posisiAki->nama, 'description' => $previous->merk.' — '.$days.' hari'];
                    }
                } elseif ($previous && (float) $previous->ritase < $detail->kategori->limit_ritase) {
                    $warnings[] = ['label' => $detail->kategori->nama, 'description' => number_format((float) $previous->ritase, 1, ',', '.').' dari '.$detail->kategori->limit_ritase.' rit'];
                }
            }
            $balance = (int) ($balances[$invoice->vehicle_id] ?? 0);

            return [
                'no' => $start + $index + 1, 'no_invoice' => $invoice->no_invoice,
                'tanggal' => ($isAki ? Carbon::parse($invoice->tanggal) : $invoice->created_at)->format('d-m-Y'),
                'vehicle' => $invoice->vehicle->nomor_lambung ?? '—',
                'pembayaran' => ['kas_besar' => 'Kas Besar', 'uj_ditahan' => 'UJ Ditahan Sparepart', 'dibayar_sendiri' => 'Dibayar Sendiri'][$invoice->pembayaran] ?? $invoice->pembayaran,
                'nominal' => (int) $invoice->total_nominal, 'saldo_uj' => $balance,
                'saldo_kurang' => $invoice->pembayaran === 'uj_ditahan' && $balance < (int) $invoice->total_nominal,
                'jumlah' => $invoice->details->count(),
                'actions' => view('billing.otorisasi-maintenance.actions', compact('invoice', 'type', 'warnings'))->render(),
            ];
        });

        return response()->json(['draw' => (int) ($data['draw'] ?? 0), 'recordsTotal' => $total, 'recordsFiltered' => $filtered, 'data' => $rows]);
    }
}
