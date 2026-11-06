<?php

namespace App\Services;

use App\Models\InvoiceAddVendor;
use App\Models\InvoiceBayar;
use App\Models\KeranjangNotaBayar;
use App\Models\Transaksi;
use App\Models\TransaksiAdditional;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class VendorOperationalBilling
{
    public const CATEGORIES = ['utama' => 'HPP'] + TransaksiAdditional::JENIS;

    public function journeys(int $vendorId): Builder
    {
        return Transaksi::query()->where('void', 0)
            ->whereHas('kas_uang_jalan', fn (Builder $query): Builder => $query->where('vendor_id', $vendorId))
            ->with(['kas_uang_jalan.vendor', 'kas_uang_jalan.customer', 'kas_uang_jalan.rute', 'kas_uang_jalan.vehicle.driver']);
    }

    public function payableNotes(int $vendorId, string $kind, int $userId): Builder
    {
        return $kind === 'utama'
            ? $this->journeys($vendorId)->where('status', 3)->where('bayar', 0)
                ->whereNotIn('id', $this->cartEntries($vendorId, $userId)->select('transaksi_id'))
            : $this->additionals($vendorId, $kind)->where('status', 3);
    }

    public function searchJourneys(Builder $query, string $search): void
    {
        if ($search === '') {
            return;
        }
        $term = '%'.$search.'%';
        $query->where(fn (Builder $query): Builder => $query->where('nota_muat', 'like', $term)->orWhere('nota_bongkar', 'like', $term)
            ->orWhereHas('kas_uang_jalan.vehicle', fn (Builder $query): Builder => $query->where('nomor_lambung', 'like', $term)->orWhere('nopol', 'like', $term))
            ->orWhereHas('kas_uang_jalan.rute', fn (Builder $query): Builder => $query->where('nama', 'like', $term)));
    }

    public function invoiceQuery(int $vendorId, string $kind): Builder
    {
        return $kind === 'utama' ? InvoiceBayar::query()->with('vendor')->where('vendor_id', $vendorId)
            : InvoiceAddVendor::query()->with('vendor')->where('vendor_id', $vendorId)->where('jenis', $kind);
    }

    public function invoices(int $vendorId, array $filters): QueryBuilder
    {
        $base = $this->invoiceQuery($vendorId, 'utama')->select('id', 'periode', 'tanggal', 'tempo', 'updated_at')
            ->selectRaw("'utama' as kind, lunas as is_paid")->toBase();
        $extra = InvoiceAddVendor::query()->where('vendor_id', $vendorId)->where('status', 1)
            ->whereIn('jenis', array_keys(self::CATEGORIES))
            ->select('id', 'periode', 'created_at as tanggal', 'tempo', 'updated_at', 'jenis as kind', 'is_finished as is_paid')->toBase();
        $query = DB::query()->fromSub($base->unionAll($extra), 'vendor_invoices');
        if (($filters['status'] ?? 'unpaid') !== 'all') {
            $query->where('is_paid', ($filters['status'] ?? 'unpaid') === 'paid' ? 1 : 0);
        }
        if (trim($filters['search'] ?? '') !== '') {
            $query->where('periode', 'like', '%'.trim($filters['search']).'%');
        }
        if (! empty($filters['start_date'])) {
            $query->whereDate('tempo', '>=', $filters['start_date']);
        }
        if (! empty($filters['end_date'])) {
            $query->whereDate('tempo', '<=', $filters['end_date']);
        }

        return $query->orderByDesc('tanggal')->orderByDesc('id')->orderBy('kind');
    }

    /** @return array<string, mixed> */
    public function invoiceSummary(object $invoice, ?string $kind = null): array
    {
        $kind ??= $invoice->kind;
        $paid = (int) ($invoice instanceof InvoiceBayar ? $invoice->lunas : ($invoice instanceof InvoiceAddVendor ? $invoice->is_finished : $invoice->is_paid)) === 1;
        $date = $invoice instanceof InvoiceAddVendor ? $invoice->created_at : $invoice->tanggal;

        return ['id' => (int) $invoice->id, 'kind' => $kind,
            'invoice_number' => $kind === 'utama' ? $invoice->periode : (new InvoiceAddVendor)->forceFill(['jenis' => $kind, 'periode' => $invoice->periode])->periode_invoice,
            'category_name' => self::CATEGORIES[$kind], 'issued_on' => $date === null ? null : substr((string) $date, 0, 10),
            'due_on' => $invoice->tempo === null ? null : substr((string) $invoice->tempo, 0, 10),
            'paid' => $paid, 'paid_on' => $paid && $invoice->updated_at ? Carbon::parse($invoice->updated_at)->format('Y-m-d') : null];
    }

    public function invoiceJourneys(int $vendorId, string $kind, InvoiceBayar|InvoiceAddVendor $invoice): Builder
    {
        return $kind === 'utama'
            ? $this->journeys($vendorId)->whereIn('id', $invoice->invoice_bayar_details()->select('transaksi_id'))
            : $this->additionals($vendorId, $kind)->whereIn('id', $invoice->details()->select('transaksi_additional_id'));
    }

    public function cartEntries(int $vendorId, int $userId): Builder
    {
        return KeranjangNotaBayar::query()->where('vendor_id', $vendorId)->where('user_id', $userId);
    }

    public function additionals(int $vendorId, string $kind): Builder
    {
        return TransaksiAdditional::query()->where('vendor_id', $vendorId)->where('jenis', $kind)
            ->whereHas('transaksi', fn (Builder $query): Builder => $query->where('void', 0)
                ->whereHas('kas_uang_jalan', fn (Builder $query): Builder => $query->where('vendor_id', $vendorId)))
            ->with(['customer', 'rute', 'transaksi.kas_uang_jalan.vendor', 'transaksi.kas_uang_jalan.customer', 'transaksi.kas_uang_jalan.rute', 'transaksi.kas_uang_jalan.vehicle.driver']);
    }

    /** Only this allowlist crosses the financial-data boundary into web views. */
    public function journey(Transaksi $transaction, ?TransaksiAdditional $additional = null): array
    {
        $cash = $transaction->kas_uang_jalan;
        $vehicle = $cash?->vehicle;
        $loaded = $transaction->tonase;
        $unloaded = $transaction->timbangan_bongkar;
        $difference = is_numeric($loaded) && is_numeric($unloaded) ? (float) $loaded - (float) $unloaded : null;

        return [
            'Tanggal UJ' => $cash?->tanggal,
            'Kode UJ' => $cash ? 'UJ'.sprintf('%02d', $cash->nomor_uang_jalan) : null,
            'Vendor' => $cash?->vendor?->nickname ?: $cash?->vendor?->nama,
            'Nomor Lambung' => $vehicle?->nomor_lambung,
            'Nomor Polisi' => $vehicle?->nopol,
            'Driver' => $vehicle?->driver?->nama,
            'Tambang' => $additional?->customer?->nama ?? $cash?->customer?->nama,
            'Rute' => $additional?->rute?->nama ?? $cash?->rute?->nama,
            'Jarak (km)' => $additional?->rute?->jarak ?? $cash?->rute?->jarak,
            'Tanggal Muat' => $transaction->tanggal_muat,
            'Nota Muat' => $transaction->nota_muat,
            'Gross Muat (ton)' => $transaction->gross_muat,
            'Tare Muat (ton)' => $transaction->tarra_muat,
            'Tonase Muat (ton)' => $loaded,
            'Tanggal Bongkar' => $transaction->tanggal_bongkar,
            'Nota Bongkar' => $transaction->nota_bongkar,
            'Gross Bongkar (ton)' => $transaction->gross_bongkar,
            'Tare Bongkar (ton)' => $transaction->tarra_bongkar,
            'Tonase Bongkar (ton)' => $unloaded,
            'Selisih (ton)' => $difference === null ? null : number_format($difference, 3, ',', '.'),
            'Selisih (%)' => $difference === null || (float) $loaded === 0.0 ? null : number_format($difference / (float) $loaded * 100, 2, ',', '.'),
            'Uang Jalan (Rp)' => $cash?->nominal_transaksi === null ? null : number_format($cash->nominal_transaksi, 0, ',', '.'),
            'Status Bongkar' => (int) $transaction->status === 2 ? 'Menunggu bongkar' : 'Selesai bongkar',
            'Status Nota Bayar' => $additional
                ? match ((int) $additional->status) {
                    3 => 'Tersedia', 4 => 'Dalam keranjang', 5 => 'Sudah invoice', default => 'Belum tersedia'
                }
                : ((int) $transaction->bayar === 1 ? 'Sudah invoice' : ((int) $transaction->readonly_cart_count > 0 ? 'Dalam keranjang' : 'Tersedia')),
        ];
    }

    public function unloadingCells(Transaksi $transaction): array
    {
        $fields = $this->journey($transaction);
        $customer = $transaction->kas_uang_jalan?->customer;

        return [
            'Tanggal' => $fields['Tanggal UJ'],
            'Kode' => $fields['Kode UJ'],
            'Nomor Lambung' => $fields['Nomor Lambung'],
            'Vendor' => $fields['Vendor'],
            'Tambang' => $customer?->singkatan ?: $customer?->nama,
            'Rute' => $fields['Rute'],
            'Tanggal Muat' => $transaction->tanggal_muat ? $transaction->id_tanggal_muat : null,
            'Nota Muat' => $fields['Nota Muat'],
            'Timbangan Muat' => $fields['Tonase Muat (ton)'],
        ];
    }

    public function paymentColumns(bool $includeMoneyOut = true): array
    {
        $columns = ['Tanggal UJ' => 'Tanggal UJ', 'Kode' => 'Kode UJ', 'Nomor Lambung' => 'Nomor Lambung',
            'Vendor' => 'Vendor', 'Rute' => 'Rute', 'Jarak' => 'Jarak (km)', 'Tanggal Muat' => 'Tanggal Muat',
            'Nota Muat' => 'Nota Muat', 'Tonase Muat' => 'Tonase Muat (ton)', 'Tanggal Bongkar' => 'Tanggal Bongkar',
            'Nota Bongkar' => 'Nota Bongkar', 'Tonase Bongkar' => 'Tonase Bongkar (ton)',
            'Selisih (Ton)' => 'Selisih (ton)', 'Selisih (%)' => 'Selisih (%)'];
        if ($includeMoneyOut) {
            $columns['Uang Jalan'] = 'Uang Jalan (Rp)';
        }

        return $columns;
    }

    public function paymentCells(Transaksi $transaction, bool $includeMoneyOut = true): array
    {
        $fields = $this->journey($transaction);
        $fields['Tanggal Muat'] = $transaction->tanggal_muat ? $transaction->id_tanggal_muat : null;
        $fields['Tanggal Bongkar'] = $transaction->tanggal_bongkar ? $transaction->id_tanggal_bongkar : null;
        if (is_numeric($transaction->tonase) && is_numeric($transaction->timbangan_bongkar)) {
            $difference = (float) $transaction->tonase - (float) $transaction->timbangan_bongkar;
            $fields['Selisih (ton)'] = number_format($difference, 2, ',', '.');
            $fields['Selisih (%)'] = number_format($difference * 0.1, 2, ',', '.');
        }
        $cells = [];
        foreach ($this->paymentColumns($includeMoneyOut) as $label => $key) {
            $cells[$label] = $fields[$key];
        }

        return $cells;
    }
}
