<?php

namespace App\Services;

use App\Models\AkiGantiInvoiceDetail;
use App\Models\AkiLog;
use App\Models\BanGantiInvoice;
use App\Models\BanLog;
use App\Models\FilterOliGantiInvoice;
use App\Models\FilterOliLog;
use App\Models\UjDitahan;
use App\Models\UjDitahanBan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Antrian Otorisasi Maintenance: pengajuan pending, peringatan sebelum
 * disetujui, dan saldo UJ ditahan. Dipakai halaman web dan API mobile.
 */
class MaintenanceAuthorizationQueue
{
    public const PAYMENT_LABELS = [
        'kas_besar' => 'Kas Besar', 'uj_ditahan' => 'UJ Ditahan Sparepart',
        'uj_ditahan_ban' => 'UJ Ditahan Ban', 'dibayar_sendiri' => 'Dibayar Sendiri',
    ];

    public function __construct(private FilterOliRitaseService $ritase) {}

    public function pending(string $type, string $search = ''): Builder
    {
        $query = MaintenanceApproval::model($type)::query()->where('status', 'pending')
            ->with(['vehicle.vendor', 'details.'.$this->categoryRelation($type)]);
        $search = trim($search);
        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('no_invoice', 'like', '%'.$search.'%')
                    ->orWhere('pembayaran', 'like', '%'.$search.'%')
                    ->orWhereHas('vehicle', fn ($vehicle) => $vehicle->where('nomor_lambung', 'like', '%'.$search.'%'));
            });
        }

        return $query->orderBy('created_at')->orderBy('id');
    }

    /**
     * Peringatan dan saldo UJ ditahan per invoice (detail & kategori sudah dimuat).
     *
     * @return array<int, array{warnings: list<array<string, mixed>>, balance: int}>
     */
    public function describe(string $type, Collection $invoices): array
    {
        if ($invoices->isEmpty()) {
            return [];
        }
        $vehicleIds = $invoices->pluck('vehicle_id')->unique();
        $balances = ($type === 'tire' ? UjDitahanBan::query() : UjDitahan::query())
            ->whereIn('vehicle_id', $vehicleIds)->where('saldo', '>', 0)
            ->selectRaw('vehicle_id, SUM(saldo) as available')->groupBy('vehicle_id')->pluck('available', 'vehicle_id');
        $warnings = $type === 'tire' ? $this->tireWarnings($invoices) : $this->sparepartWarnings($type, $invoices);

        return $invoices->mapWithKeys(fn ($invoice) => [$invoice->id => [
            'warnings' => $warnings[$invoice->id] ?? [],
            'balance' => (int) ($balances[$invoice->vehicle_id] ?? 0),
        ]])->all();
    }

    /** Satu baris daftar pending untuk API mobile. */
    public function row(string $type, Model $invoice, array $info): array
    {
        $total = (int) $invoice->total_nominal;

        return [
            'id' => $invoice->id, 'no_invoice' => $invoice->no_invoice,
            'date' => $this->submittedAt($type, $invoice)->toDateString(),
            'nomor_lambung' => $invoice->vehicle?->nomor_lambung, 'vendor' => $invoice->vehicle?->vendor?->nama,
            'payment' => $invoice->pembayaran, 'payment_label' => self::PAYMENT_LABELS[$invoice->pembayaran] ?? $invoice->pembayaran,
            'total' => $total, 'item_count' => $invoice->details->count(),
            ...$this->balance($invoice, $info['balance']),
            'warnings' => array_map(fn (array $warning): array => ['label' => $warning['label'], 'description' => $warning['description']], $info['warnings']),
        ];
    }

    /** Rincian satu pengajuan: item baru beserta ban/aki/filter yang diganti. */
    public function detail(string $type, int $id): array
    {
        $invoice = MaintenanceApproval::model($type)::with(['vehicle.vendor', 'details.'.$this->categoryRelation($type)])->findOrFail($id);
        $info = $this->describe($type, collect([$invoice]))[$invoice->id];
        $items = $invoice->details->sortBy('id')->values()->map(fn ($detail): array => match ($type) {
            'tire' => $this->tireItem($invoice, $detail),
            'battery' => $this->batteryItem($invoice, $detail),
            'filter-oil' => $this->filterOilItem($invoice, $detail),
        });

        return [
            ...$this->row($type, $invoice, $info),
            'type' => $type, 'status' => $invoice->status,
            'status_label' => ['pending' => 'Menunggu Otorisasi', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'][$invoice->status] ?? $invoice->status,
            'bank' => $invoice->nama_bank, 'account_number' => $invoice->nomor_rekening, 'account_name' => $invoice->nama_rekening,
            'fingerprint' => MaintenanceApproval::fingerprint($invoice),
            'items' => $items->all(),
        ];
    }

    private function balance(Model $invoice, int $balance): array
    {
        $usesHeldCash = in_array($invoice->pembayaran, ['uj_ditahan', 'uj_ditahan_ban'], true);

        return [
            'held_cash_balance' => $balance,
            'balance_short' => $usesHeldCash && $balance < (int) $invoice->total_nominal,
        ];
    }

    private function submittedAt(string $type, Model $invoice): Carbon
    {
        return $type === 'filter-oil' ? $invoice->created_at : Carbon::parse($invoice->tanggal ?? $invoice->created_at);
    }

    private function categoryRelation(string $type): string
    {
        return ['tire' => 'posisiBan', 'battery' => 'posisiAki', 'filter-oil' => 'kategori'][$type];
    }

    /** Ban yang sedang terpasang di posisi itu belum mencapai 80 rit. */
    private function tireWarnings(Collection $invoices): array
    {
        $active = BanLog::whereIn('id', BanLog::selectRaw('MAX(id)')->whereIn('vehicle_id', $invoices->pluck('vehicle_id')->unique())->groupBy('vehicle_id', 'posisi_ban_id'))
            ->get()->keyBy(fn ($log) => $log->vehicle_id.'-'.$log->posisi_ban_id);
        $warnings = [];
        foreach ($invoices as $invoice) {
            foreach ($invoice->details as $detail) {
                $log = $active->get($invoice->vehicle_id.'-'.$detail->posisi_ban_id);
                if ($log && (float) ($log->ritase ?? 0) < 80) {
                    $label = $detail->posisiBan->nama ?? ('Posisi '.$detail->posisi_ban_id);
                    $warnings[$invoice->id][] = [
                        'label' => $label, 'merk' => $log->merk ?? '-', 'no_seri' => $log->no_seri ?? '-', 'ritase' => $log->ritase ?? 0,
                        'description' => ($log->merk ?? '-').' — '.number_format((float) ($log->ritase ?? 0), 1, ',', '.').' dari 80 rit',
                    ];
                }
            }
        }

        return $warnings;
    }

    /** Aki berumur < 365 hari, atau filter/oli diganti sebelum limit ritase kategorinya. */
    private function sparepartWarnings(string $type, Collection $invoices): array
    {
        $isAki = $type === 'battery';
        $detailTable = $isAki ? 'aki_ganti_invoice_details' : 'filter_oli_ganti_invoice_details';
        $invoiceTable = $isAki ? 'aki_ganti_invoices' : 'filter_oli_ganti_invoices';
        $invoiceKey = $isAki ? 'aki_ganti_invoice_id' : 'filter_oli_ganti_invoice_id';
        $logTable = $isAki ? 'aki_logs' : 'filter_oli_logs';
        $categoryKey = $isAki ? 'posisi_aki_id' : 'kategori_filter_oli_mesin_id';
        $previousIds = DB::table($detailTable.' as detail')
            ->join($invoiceTable.' as invoice', 'detail.'.$invoiceKey, '=', 'invoice.id')
            ->whereIn('invoice.id', $invoices->pluck('id'))
            ->selectSub(function ($query) use ($logTable, $categoryKey): void {
                $query->from($logTable.' as previous_log')->select('previous_log.id')
                    ->whereColumn('previous_log.vehicle_id', 'invoice.vehicle_id')
                    ->whereColumn('previous_log.'.$categoryKey, 'detail.'.$categoryKey)
                    ->whereColumn('previous_log.created_at', '<=', 'detail.created_at')
                    ->orderByDesc('previous_log.created_at')->orderByDesc('previous_log.id')->limit(1);
            }, 'previous_log_id')->pluck('previous_log_id')->filter();
        $logs = ($isAki ? AkiLog::query() : FilterOliLog::query())->whereIn('id', $previousIds)
            ->orderByDesc('created_at')->orderByDesc('id')->get()->groupBy('vehicle_id');
        $warnings = [];
        foreach ($invoices as $invoice) {
            foreach ($invoice->details as $detail) {
                $previous = ($logs->get($invoice->vehicle_id) ?? collect())->first(fn ($log) => $log->{$categoryKey} === $detail->{$categoryKey} && $log->created_at <= $detail->created_at);
                if ($isAki) {
                    $days = AkiGantiInvoiceDetail::usageDays($previous?->created_at, $detail->created_at);
                    if ($days !== null && $days < 365) {
                        $warnings[$invoice->id][] = ['label' => $detail->posisiAki->nama, 'description' => $previous->merk.' — '.$days.' hari'];
                    }
                } elseif ($previous && (float) $previous->ritase < $detail->kategori->limit_ritase) {
                    $warnings[$invoice->id][] = ['label' => $detail->kategori->nama, 'description' => number_format((float) $previous->ritase, 1, ',', '.').' dari '.$detail->kategori->limit_ritase.' rit'];
                }
            }
        }

        return $warnings;
    }

    private function tireItem(BanGantiInvoice $invoice, Model $detail): array
    {
        $logs = BanLog::where('vehicle_id', $invoice->vehicle_id)->where('posisi_ban_id', $detail->posisi_ban_id);
        $previous = $detail->ban_log_id
            ? $logs->where('id', '<', $detail->ban_log_id)->orderByDesc('id')->first()
            : $logs->orderByDesc('id')->first();

        return [
            'id' => $detail->id, 'label' => $detail->posisiBan->nama ?? ('Posisi '.$detail->posisi_ban_id),
            'merk' => $detail->merk, 'no_seri' => $detail->no_seri, 'kondisi' => (int) $detail->kondisi,
            'date' => $detail->created_at?->toDateString(), 'ritase' => (float) ($detail->ritase ?? 0),
            'previous' => $previous ? ['merk' => $previous->merk, 'no_seri' => $previous->no_seri, 'ritase' => (float) ($previous->ritase ?? 0)] : null,
        ];
    }

    private function batteryItem(Model $invoice, AkiGantiInvoiceDetail $detail): array
    {
        $previous = $detail->previousAkiLog($invoice->vehicle_id);

        return [
            'id' => $detail->id, 'label' => $detail->posisiAki->nama ?? '-',
            'merk' => $detail->merk, 'no_seri' => $detail->no_seri, 'kondisi' => (int) $detail->kondisi,
            'date' => $detail->created_at?->toDateString(),
            'previous' => $previous ? [
                'merk' => $previous->merk, 'no_seri' => $previous->no_seri, 'kondisi' => $previous->kondisi,
                'date' => $previous->created_at?->toDateString(),
                'age_days' => AkiGantiInvoiceDetail::usageDays($previous->created_at, $detail->created_at),
            ] : null,
        ];
    }

    private function filterOilItem(FilterOliGantiInvoice $invoice, Model $detail): array
    {
        $previous = FilterOliLog::where('vehicle_id', $invoice->vehicle_id)
            ->where('kategori_filter_oli_mesin_id', $detail->kategori_filter_oli_mesin_id)
            ->when($detail->filter_oli_log_id, fn ($query) => $query->where('id', '!=', $detail->filter_oli_log_id))
            ->where('created_at', '<=', $detail->created_at)
            ->orderByDesc('created_at')->orderByDesc('id')->first();
        // Pending: ritase dihitung ulang seperti halaman detail web.
        $ritase = $invoice->status === FilterOliGantiInvoice::STATUS_PENDING
            ? (float) $this->ritase->preview($invoice->vehicle_id, $detail->kategori_filter_oli_mesin_id, $detail->created_at)->sum('nilai_ritase')
            : (float) $detail->ritase;

        return [
            'id' => $detail->id, 'label' => $detail->kategori->nama ?? '-',
            'merk' => $detail->merk, 'kondisi' => (int) $detail->kondisi,
            'date' => $detail->created_at?->toDateString(), 'ritase' => $ritase, 'limit_ritase' => (int) $detail->limit_ritase,
            'previous' => $previous ? ['merk' => $previous->merk, 'date' => $previous->created_at?->toDateString(), 'ritase' => (float) $previous->ritase] : null,
        ];
    }
}
