<?php

namespace App\Services;

use App\Models\InvoiceAddVendor;
use App\Models\InvoiceBayar;
use App\Models\KasUangJalan;
use App\Models\KeranjangNotaBayar;
use App\Models\Pajak\PphSimpan;
use App\Models\Pajak\PpnMasukan;
use App\Models\Rute;
use App\Models\Transaksi;
use App\Models\TransaksiAdditional;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VendorOperationalBilling
{
    public const CATEGORIES = ['utama' => 'HPP'] + TransaksiAdditional::JENIS;

    /** Kolom tabel nota bayar/invoice yang bisa diurutkan: kunci sort => judul kolom. */
    public const SORTS = ['tanggal_uj' => 'Tanggal UJ', 'kode' => 'Kode', 'rute' => 'Rute', 'tanggal_muat' => 'Tanggal Muat', 'tanggal_bongkar' => 'Tanggal Bongkar'];

    /** Kolom daftar invoice vendor yang bisa diurutkan: kunci sort => judul kolom. */
    public const INVOICE_SORTS = ['tanggal' => 'Tanggal', 'vendor' => 'Vendor', 'invoice' => 'Invoice', 'tempo' => 'Jatuh Tempo'];

    /** Perjalanan aktif satu vendor, atau seluruh vendor bila $vendorId null (role operasional). */
    public function journeys(?int $vendorId): Builder
    {
        return Transaksi::query()->where('void', 0)
            ->whereHas('kas_uang_jalan', fn (Builder $query): Builder => $query->when($vendorId !== null, fn (Builder $query): Builder => $query->where('vendor_id', $vendorId)))
            ->with(['kas_uang_jalan.vendor', 'kas_uang_jalan.customer', 'kas_uang_jalan.rute', 'kas_uang_jalan.vehicle.driver']);
    }

    public function pendingUnloading(?int $vendorId): Builder
    {
        return $this->journeys($vendorId)->where('status', 2);
    }

    public function payableNotes(?int $vendorId, string $kind, int $userId): Builder
    {
        return $kind === 'utama'
            ? $this->journeys($vendorId)->where('status', 3)->where('bayar', 0)
                ->whereNotIn('id', $this->cartEntries($vendorId, $userId)->select('transaksi_id'))
            : $this->additionals($vendorId, $kind)->where('status', 3);
    }

    /** @return array{total_weight_kg: ?int, missing_weight_count: int} */
    public function paymentWeightSummary(Builder $filtered, string $kind): array
    {
        $query = DB::query()->fromSub((clone $filtered)->toBase(), 'notes');
        $journey = 'notes';
        if ($kind !== 'utama') {
            $query->leftJoin('transaksis as journey', 'journey.id', '=', 'notes.transaksi_id')
                ->leftJoin('customers as override_customer', 'override_customer.id', '=', 'notes.customer_id');
            $journey = 'journey';
        }
        $query->leftJoin('kas_uang_jalans as cash', 'cash.id', '=', $journey.'.kas_uang_jalan_id')
            ->leftJoin('customers as customer', 'customer.id', '=', 'cash.customer_id');
        $basis = $kind === 'utama' ? 'customer.tagihan_dari' : 'CASE WHEN override_customer.id IS NOT NULL THEN override_customer.tagihan_dari ELSE customer.tagihan_dari END';
        $weight = "CASE ($basis) WHEN 1 THEN $journey.tonase WHEN 2 THEN $journey.timbangan_bongkar ELSE NULL END";
        $summary = $query->selectRaw("SUM(CAST(REPLACE(($weight), ',', '.') AS DECIMAL(14,3))) as total_weight")
            ->selectRaw("SUM(CASE WHEN ($weight) IS NULL OR TRIM(($weight)) = '' THEN 1 ELSE 0 END) as missing_weight_count")->first();
        $missing = (int) $summary->missing_weight_count;

        return ['total_weight_kg' => $missing > 0 ? null : (int) round((float) $summary->total_weight * 1000),
            'missing_weight_count' => $missing];
    }

    /**
     * Total bruto dan netto (bruto − uang jalan) atas seluruh nota HPP hasil
     * filter, sebelum pagination. Hanya untuk akun yang boleh melihat keuangan.
     *
     * @return array{total_gross_amount: int, total_net_amount: int}
     */
    public function paymentFinanceSummary(Builder $filtered): array
    {
        $summary = DB::query()->fromSub((clone $filtered)->toBase(), 'notes')
            ->leftJoin('kas_uang_jalans as cash', 'cash.id', '=', 'notes.kas_uang_jalan_id')
            ->selectRaw('COALESCE(SUM(notes.nominal_bayar), 0) as gross, COALESCE(SUM(cash.nominal_transaksi), 0) as cash')->first();

        return ['total_gross_amount' => (int) $summary->gross, 'total_net_amount' => (int) $summary->gross - (int) $summary->cash];
    }

    /** Harga, bruto dan netto satu perjalanan untuk akun yang boleh melihat keuangan. */
    public function journeyFinance(Transaksi $journey): array
    {
        $gross = $journey->nominal_bayar === null ? null : (int) $journey->nominal_bayar;
        $cash = $journey->kas_uang_jalan?->nominal_transaksi;

        return ['price' => $journey->harga_vendor === null ? null : (float) $journey->harga_vendor, 'gross_amount' => $gross,
            'net_amount' => $gross === null || $cash === null ? null : $gross - (int) $cash];
    }

    /**
     * Rute yang muncul pada nota hasil $base (sebelum filter rute/cari), untuk
     * pilihan filter. Nota tambahan memakai rute miliknya bila ada.
     */
    public function routeOptions(Builder $base, string $kind): Collection
    {
        $journeys = (clone $base)->reorder()->select($kind === 'utama' ? 'transaksis.id' : 'transaksi_additionals.transaksi_id');
        $cashRoutes = KasUangJalan::query()->select('rute_id')
            ->whereIn('id', Transaksi::query()->select('kas_uang_jalan_id')->whereIn('id', $journeys));

        return Rute::query()->whereIn('id', $cashRoutes)
            ->when($kind !== 'utama', fn (Builder $query): Builder => $query->orWhereIn('id', (clone $base)->reorder()->select('transaksi_additionals.rute_id')))
            ->orderBy('nama')->get(['id', 'nama']);
    }

    /** Saring nota per rute: rute nota tambahan bila diisi, selain itu rute uang jalan. */
    public function filterRoute(Builder $query, string $kind, ?int $routeId): void
    {
        if ($routeId === null) {
            return;
        }
        $cashRoute = fn (Builder $query): Builder => $query->where('rute_id', $routeId);
        if ($kind === 'utama') {
            $query->whereHas('kas_uang_jalan', $cashRoute);

            return;
        }
        $query->where(fn (Builder $query): Builder => $query->where('transaksi_additionals.rute_id', $routeId)
            ->orWhere(fn (Builder $query): Builder => $query->whereNull('transaksi_additionals.rute_id')->whereHas('transaksi.kas_uang_jalan', $cashRoute)));
    }

    /**
     * Urutkan nota menurut kolom perjalanan (lihat SORTS). Tanpa $sort urutan
     * tidak diubah; pemanggil tetap menambahkan id sebagai pengurut terakhir.
     */
    public function sortJourneys(Builder $query, string $kind, ?string $sort, string $direction = 'asc'): void
    {
        if ($sort === null || ! array_key_exists($sort, self::SORTS)) {
            return;
        }
        $direction = $direction === 'desc' ? 'desc' : 'asc';
        $journey = $kind === 'utama' ? 'transaksis.id' : 'transaksi_additionals.transaksi_id';
        $journeyColumn = fn (string $column): QueryBuilder => DB::table('transaksis as sort_journey')
            ->leftJoin('kas_uang_jalans as sort_cash', 'sort_cash.id', '=', 'sort_journey.kas_uang_jalan_id')
            ->leftJoin('rutes as sort_route', 'sort_route.id', '=', 'sort_cash.rute_id')
            ->whereColumn('sort_journey.id', $journey)->select($column)->limit(1);
        $value = match ($sort) {
            'tanggal_uj' => $journeyColumn('sort_cash.tanggal'),
            'kode' => $journeyColumn('sort_cash.nomor_uang_jalan'),
            'rute' => $journeyColumn('sort_route.nama'),
            'tanggal_muat' => $journeyColumn('sort_journey.tanggal_muat'),
            'tanggal_bongkar' => $journeyColumn('sort_journey.tanggal_bongkar'),
        };
        if ($sort === 'rute' && $kind !== 'utama') {
            // Nota tambahan menampilkan rutenya sendiri bila diisi, selain itu rute uang jalan.
            $query->orderByRaw('COALESCE((SELECT own_route.nama FROM rutes own_route WHERE own_route.id = transaksi_additionals.rute_id), ('
                .'SELECT cash_route.nama FROM transaksis cash_journey LEFT JOIN kas_uang_jalans cash_kas ON cash_kas.id = cash_journey.kas_uang_jalan_id '
                .'LEFT JOIN rutes cash_route ON cash_route.id = cash_kas.rute_id WHERE cash_journey.id = transaksi_additionals.transaksi_id LIMIT 1)) '.$direction);

            return;
        }
        $query->orderBy($value, $direction);
    }

    /**
     * Total tonase muat, tonase bongkar dan (HPP) uang jalan atas seluruh nota
     * hasil filter, bukan hanya baris yang sudah dimuat.
     *
     * @return array<string, string>
     */
    public function journeyTotals(Builder $filtered, string $kind): array
    {
        $query = DB::query()->fromSub((clone $filtered)->reorder()->toBase(), 'notes');
        $journey = 'notes';
        if ($kind !== 'utama') {
            $query->join('transaksis as journey', 'journey.id', '=', 'notes.transaksi_id');
            $journey = 'journey';
        }
        $totals = $query->leftJoin('kas_uang_jalans as cash', 'cash.id', '=', $journey.'.kas_uang_jalan_id')
            ->selectRaw("COALESCE(SUM($journey.tonase), 0) as loaded")
            ->selectRaw("COALESCE(SUM(CAST(REPLACE($journey.timbangan_bongkar, ',', '.') AS DECIMAL(14,3))), 0) as unloaded")
            ->selectRaw('COALESCE(SUM(cash.nominal_transaksi), 0) as cash')->first();
        $ton = fn ($value): string => number_format((float) $value, 2, ',', '.').' ton';

        return ['Total Tonase Muat' => $ton($totals->loaded), 'Total Tonase Bongkar' => $ton($totals->unloaded)]
            + ($kind === 'utama' ? ['Total Uang Jalan' => 'Rp'.number_format((float) $totals->cash, 0, ',', '.')] : []);
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

    public function invoiceQuery(?int $vendorId, string $kind): Builder
    {
        return ($kind === 'utama' ? InvoiceBayar::query() : InvoiceAddVendor::query()->where('jenis', $kind))
            ->with('vendor')->when($vendorId !== null, fn (Builder $query): Builder => $query->where('vendor_id', $vendorId));
    }

    public function invoices(?int $vendorId, array $filters): QueryBuilder
    {
        $base = $this->invoiceQuery($vendorId, 'utama')->select('id', 'vendor_id', 'periode', 'tanggal', 'tempo', 'updated_at')
            ->selectRaw("'utama' as kind, lunas as is_paid, total_bayar as total")->toBase();
        $extra = InvoiceAddVendor::query()->when($vendorId !== null, fn (Builder $query): Builder => $query->where('vendor_id', $vendorId))->where('status', 1)
            ->whereIn('jenis', array_keys(self::CATEGORIES))
            ->select('id', 'vendor_id', 'periode', 'created_at as tanggal', 'tempo', 'updated_at', 'jenis as kind', 'is_finished as is_paid', 'total')->toBase();
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
        $direction = ($filters['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        match ($filters['sort'] ?? null) {
            'tanggal' => $query->orderBy('tanggal', $direction),
            'vendor' => $query->orderBy(Vendor::query()->select('nama')->whereColumn('vendors.id', 'vendor_invoices.vendor_id')->limit(1), $direction),
            'invoice' => $query->orderBy('periode', $direction),
            'tempo' => $query->orderBy('tempo', $direction),
            default => null,
        };

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

    public function invoiceJourneys(?int $vendorId, string $kind, InvoiceBayar|InvoiceAddVendor $invoice): Builder
    {
        return $kind === 'utama'
            ? $this->journeys($vendorId)->whereIn('id', $invoice->invoice_bayar_details()->select('transaksi_id'))
            : $this->additionals($vendorId, $kind)->whereIn('id', $invoice->details()->select('transaksi_additional_id'));
    }

    public function cartEntries(?int $vendorId, int $userId): Builder
    {
        return KeranjangNotaBayar::query()->when($vendorId !== null, fn (Builder $query): Builder => $query->where('vendor_id', $vendorId))->where('user_id', $userId);
    }

    public function additionals(?int $vendorId, string $kind): Builder
    {
        $own = fn (Builder $query): Builder => $query->when($vendorId !== null, fn (Builder $query): Builder => $query->where('vendor_id', $vendorId));

        return $own(TransaksiAdditional::query())->where('jenis', $kind)
            ->whereHas('transaksi', fn (Builder $query): Builder => $query->where('void', 0)->whereHas('kas_uang_jalan', $own))
            ->with(['customer', 'rute', 'transaksi.kas_uang_jalan.vendor', 'transaksi.kas_uang_jalan.customer', 'transaksi.kas_uang_jalan.rute', 'transaksi.kas_uang_jalan.vehicle.driver']);
    }

    /**
     * Only this allowlist crosses the financial-data boundary into web views.
     * Vendor nominals (harga, bruto, netto) are added only for accounts that
     * may see their own finances ($finance), never for vendor operational.
     */
    public function journey(Transaksi $transaction, ?TransaksiAdditional $additional = null, bool $finance = false): array
    {
        $cash = $transaction->kas_uang_jalan;
        $vehicle = $cash?->vehicle;
        $loaded = $transaction->tonase;
        $unloaded = $transaction->timbangan_bongkar;
        $difference = is_numeric($loaded) && is_numeric($unloaded) ? (float) $loaded - (float) $unloaded : null;

        $rupiah = fn ($value): ?string => $value === null ? null : number_format((float) $value, 0, ',', '.');
        $money = $finance && $additional === null ? [
            'Harga Vendor (Rp)' => $rupiah($transaction->harga_vendor),
            'Bruto (Rp)' => $rupiah($transaction->nominal_bayar),
            'Netto (Rp)' => $transaction->nominal_bayar === null || $cash?->nominal_transaksi === null ? null
                : $rupiah((float) $transaction->nominal_bayar - (float) $cash->nominal_transaksi),
        ] : [];

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
            ...$money,
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

    public function paymentColumns(bool $includeMoneyOut = true, bool $finance = false): array
    {
        $columns = ['Tanggal UJ' => 'Tanggal UJ', 'Kode' => 'Kode UJ', 'Nomor Lambung' => 'Nomor Lambung',
            'Vendor' => 'Vendor', 'Rute' => 'Rute', 'Jarak' => 'Jarak (km)', 'Tanggal Muat' => 'Tanggal Muat',
            'Nota Muat' => 'Nota Muat', 'Tonase Muat' => 'Tonase Muat (ton)', 'Tanggal Bongkar' => 'Tanggal Bongkar',
            'Nota Bongkar' => 'Nota Bongkar', 'Tonase Bongkar' => 'Tonase Bongkar (ton)',
            'Selisih (Ton)' => 'Selisih (ton)', 'Selisih (%)' => 'Selisih (%)'];
        if ($includeMoneyOut) {
            $columns['Uang Jalan'] = 'Uang Jalan (Rp)';
            if ($finance) {
                $columns += ['Harga' => 'Harga Vendor (Rp)', 'Bruto' => 'Bruto (Rp)', 'Netto' => 'Netto (Rp)'];
            }
        }

        return $columns;
    }

    public function paymentCells(Transaksi $transaction, bool $includeMoneyOut = true, bool $finance = false): array
    {
        $fields = $this->journey($transaction, null, $finance);
        $fields['Tanggal Muat'] = $transaction->tanggal_muat ? $transaction->id_tanggal_muat : null;
        $fields['Tanggal Bongkar'] = $transaction->tanggal_bongkar ? $transaction->id_tanggal_bongkar : null;
        if (is_numeric($transaction->tonase) && is_numeric($transaction->timbangan_bongkar)) {
            $difference = (float) $transaction->tonase - (float) $transaction->timbangan_bongkar;
            $fields['Selisih (ton)'] = number_format($difference, 2, ',', '.');
            $fields['Selisih (%)'] = number_format($difference * 0.1, 2, ',', '.');
        }
        $cells = [];
        foreach ($this->paymentColumns($includeMoneyOut, $finance) as $label => $key) {
            $cells[$label] = $fields[$key];
        }

        return $cells;
    }

    /**
     * Invoice totals shown only to accounts allowed to see vendor finances:
     * a headline amount, supporting details and, for HPP invoices, payment progress.
     *
     * @return array{headline: array{0: string, 1: string}, details: array<string, string>, progress: ?array{paid: string, remaining: string, percent: int}, breakdown: list<array{label: string, amount: int, op: ?string}>}
     */
    public function invoiceFinance(InvoiceBayar|InvoiceAddVendor $invoice): array
    {
        $rupiah = fn ($value): string => 'Rp'.number_format((float) $value, 0, ',', '.');
        if ($invoice instanceof InvoiceBayar) {
            $total = (float) $invoice->total_bayar;
            $ppn = (float) PpnMasukan::where('invoice_bayar_id', $invoice->id)->sum('nominal');
            $pph = (float) PphSimpan::where('invoice_bayar_id', $invoice->id)->sum('nominal');
            $journeys = $invoice->transaksi()->join('kas_uang_jalans as kuj', 'kuj.id', '=', 'transaksis.kas_uang_jalan_id')
                ->toBase()->selectRaw('COALESCE(SUM(transaksis.nominal_bayar), 0) as bruto, COALESCE(SUM(kuj.nominal_transaksi), 0) as uang_jalan')->first();
            $bruto = (float) $journeys->bruto;
            $netto = $bruto - (float) $journeys->uang_jalan;

            return ['headline' => ['Total Bayar', $rupiah($total)],
                'details' => ['PPN' => $rupiah($ppn), 'PPh' => $rupiah($pph)],
                'progress' => ['paid' => $rupiah($invoice->bayar), 'remaining' => $rupiah($invoice->sisa_bayar),
                    'percent' => $total > 0 ? (int) min(100, max(0, round((float) $invoice->bayar / $total * 100))) : 0],
                'breakdown' => $this->breakdown([['Bruto', $bruto, null], ['Uang Jalan', (float) $journeys->uang_jalan, '-'],
                    ['Netto', $netto, '='], ['PPN', $ppn, '+'], ['PPh', $pph, '-']], $netto + $ppn - $pph, 'Total Bayar', $total)];
        }

        // dpp adalah tarif per ton per km, bukan nominal rupiah.
        $rate = rtrim(rtrim(number_format((float) $invoice->dpp, 2, ',', '.'), '0'), ',');

        return ['headline' => ['Total', $rupiah($invoice->total)],
            'details' => ['Tarif DPP' => 'Rp'.$rate.' /ton/km', 'Nominal' => $rupiah($invoice->nominal), 'PPN' => $rupiah($invoice->ppn), 'PPh' => $rupiah($invoice->pph)],
            'progress' => null,
            'breakdown' => $this->breakdown([['Nominal', (float) $invoice->nominal, null], ['PPN', (float) $invoice->ppn, '+'], ['PPh', (float) $invoice->pph, '-']],
                (float) $invoice->nominal + (float) $invoice->ppn - (float) $invoice->pph, 'Total', (float) $invoice->total)];
    }

    /**
     * Langkah hitungan menuju total invoice untuk ditampilkan saat total diketuk.
     * Bila total tersimpan berbeda dari hasil hitungan (data lama diubah setelah
     * invoice dibuat), selisihnya ditampilkan sebagai "Penyesuaian" agar hitungan tetap utuh.
     *
     * @param  list<array{0: string, 1: float, 2: ?string}>  $steps  label, nominal, operator (null, '+', '-' atau '=' untuk subtotal)
     * @return list<array{label: string, amount: int, op: ?string}>
     */
    private function breakdown(array $steps, float $computed, string $totalLabel, float $total): array
    {
        $rows = array_map(fn (array $step): array => ['label' => $step[0], 'amount' => (int) round($step[1]), 'op' => $step[2]], $steps);
        $difference = (int) round($total - $computed);
        if ($difference !== 0) {
            $rows[] = ['label' => 'Penyesuaian', 'amount' => abs($difference), 'op' => $difference > 0 ? '+' : '-'];
        }
        $rows[] = ['label' => $totalLabel, 'amount' => (int) round($total), 'op' => '='];

        return $rows;
    }
}
