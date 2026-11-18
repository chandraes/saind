<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\InvoiceAdditional;
use App\Models\InvoiceAdditionalDetail;
use App\Models\InvoiceTagihan;
use App\Models\InvoiceTagihanDetail;
use App\Models\Pajak\PphPerusahaan;
use App\Models\Pajak\PpnKeluaran;
use App\Models\Transaksi;
use App\Models\TransaksiAdditional;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Nota Tagihan → Invoice Tagihan customer: centang nota fisik, ubah nota,
 * masuk/keluar keranjang, dan buat invoice. Dipakai bersama halaman web
 * Nota Tagihan dan API mobile (Admin & SU) agar hasilnya sama persis.
 * Keranjang tagihan disimpan di transaksi (kolom `keranjang`), dipakai
 * bersama semua user seperti di web.
 */
class CustomerBilling
{
    public const ADDITIONALS = ['kompensasi_jr', 'penyesuaian_bbm', 'achievement'];

    /**
     * Setel nota fisik ke [$checked]; bila sudah begitu, tidak ada yang berubah
     * (ketukan ulang aman). Mencentang mengurangi DO nolam, melepas menambah.
     */
    public function setPhysicalNote(int $id, bool $checked, ?Customer $customer = null): Transaksi
    {
        return DB::transaction(function () use ($id, $checked, $customer): Transaksi {
            $transaksi = $this->billable($customer)->lockForUpdate()->findOrFail($id);
            if ((bool) $transaksi->nota_fisik !== $checked) {
                (new Transaksi)->changeStateNotaFisik($transaksi->id);
            }

            return $transaksi->refresh();
        });
    }

    /** Ubah data nota (sama dengan form Edit Nota Tagihan web); nominal dihitung ulang. */
    public function updateNote(int $id, array $input, ?Customer $customer = null): Transaksi
    {
        return DB::transaction(function () use ($id, $input, $customer): Transaksi {
            $transaksi = $this->billable($customer)->with(['kas_uang_jalan.customer', 'kas_uang_jalan.rute'])->lockForUpdate()->findOrFail($id);
            if (Transaksi::whereKeyNot($transaksi->id)->where(fn ($query) => $query
                ->where('nota_bongkar', $input['nota_bongkar'])->orWhere('nota_muat', $input['nota_muat']))->exists()) {
                throw ValidationException::withMessages(['nota' => 'Terdapat nota yang sama!!']);
            }
            $data = collect($input)->only(['tonase', 'gross_muat', 'tarra_muat', 'timbangan_bongkar', 'gross_bongkar', 'tarra_bongkar', 'nota_muat', 'nota_bongkar'])->all();
            $data['tanggal_muat'] = date('Y-m-d', strtotime($input['tanggal_muat']));
            $data['tanggal_bongkar'] = Transaksi::unloadingTimestamp($input['tanggal_bongkar'], $transaksi->tanggal_bongkar);

            $cash = $transaksi->kas_uang_jalan;
            $jarak = $cash->rute->jarak;
            $weight = match ((int) $cash->customer->tagihan_dari) {
                1 => $data['tonase'],
                2 => $data['timbangan_bongkar'],
                default => throw ValidationException::withMessages(['nota' => 'Dasar tagihan customer belum diatur.']),
            };
            $data['nominal_tagihan'] = $weight * $jarak * $transaksi->harga_customer;
            $data['nominal_bayar'] = $weight * $jarak * $transaksi->harga_vendor;
            $penalty = $weight * $jarak * $cash->customer->nominal_penalty;
            $data['nominal_csr'] = $cash->customer->csr == 1 ? $data['timbangan_bongkar'] * $transaksi->harga_csr : 0;
            $data['nominal_bonus'] = $data['timbangan_bongkar'] * ($jarak > 50 ? 1000 : 500);
            $data['profit'] = ($data['nominal_tagihan'] * 0.98) - $data['nominal_bayar'] - $data['nominal_bonus'] - $data['nominal_csr'] - $penalty;
            $transaksi->update($data);

            return $transaksi;
        });
    }

    /**
     * Masukkan nota ke keranjang tagihan customer, beserta baris kategori
     * tambahan (Kompensasi JR, Penyesuaian BBM, Achievement) sesuai setting customer.
     * Hanya nota customer ini yang belum ditagih dan belum di keranjang.
     *
     * @param  list<int>  $ids
     */
    public function addToCart(Customer $customer, array $ids): int
    {
        return DB::transaction(function () use ($customer, $ids): int {
            $selected = $this->billable($customer)->where('keranjang', 0)->whereIn('transaksis.id', $ids)
                ->with('kas_uang_jalan.rute')->lockForUpdate()->get();
            if ($selected->isEmpty()) {
                throw ValidationException::withMessages(['ids' => 'Tidak ada data yang dipilih.']);
            }
            Transaksi::whereIn('id', $selected->modelKeys())->update(['keranjang' => 1]);
            $kinds = array_values(array_filter(self::ADDITIONALS, fn (string $kind): bool => (bool) $customer->{'is_'.$kind}));
            $rows = [];
            foreach ($selected as $transaksi) {
                $cash = $transaksi->kas_uang_jalan;
                if (! $cash || ! $cash->rute) {
                    continue;
                }
                foreach ($kinds as $kind) {
                    $rows[] = ['customer_id' => $customer->id, 'transaksi_id' => $transaksi->id, 'jenis' => $kind,
                        'vendor_id' => $cash->vendor_id, 'rute_id' => $cash->rute_id, 'jarak' => $cash->rute->jarak];
                }
            }
            if ($rows !== []) {
                TransaksiAdditional::upsert($rows, ['transaksi_id', 'jenis'], ['vendor_id', 'rute_id', 'jarak']);
            }

            return $selected->count();
        });
    }

    /** Keluarkan nota dari keranjang; invoice kategori tambahan yang memuatnya ikut dibatalkan. */
    public function removeFromCart(Customer $customer, int $id): void
    {
        DB::transaction(function () use ($customer, $id): void {
            $transaksi = $this->billable($customer)->where('keranjang', 1)->lockForUpdate()->findOrFail($id);
            $transaksi->update(['keranjang' => 0]);
            TransaksiAdditional::where('transaksi_id', $transaksi->id)->delete();
            foreach (InvoiceAdditionalDetail::where('transaksi_id', $transaksi->id)->get() as $detail) {
                $ids = InvoiceAdditionalDetail::where('invoice_additional_id', $detail->invoice_additional_id)->pluck('transaksi_id')->all();
                if ($ids !== []) {
                    TransaksiAdditional::whereIn('transaksi_id', $ids)->update(['status' => 0]);
                }
                InvoiceAdditionalDetail::where('invoice_additional_id', $detail->invoice_additional_id)->delete();
                InvoiceAdditional::whereKey($detail->invoice_additional_id)->delete();
            }
        });
    }

    /** Nota di keranjang customer (urut seperti halaman keranjang web). */
    public function cart(Customer $customer): Collection
    {
        return Transaksi::getKeranjangTagihanData($customer->id);
    }

    /** Invoice kategori tambahan yang sudah disetujui dan ikut ditagih bersama keranjang. */
    public function additionals(Customer $customer): \Illuminate\Support\Collection
    {
        return InvoiceAdditional::with('details')->where('customer_id', $customer->id)->where('status', 1)
            ->where('is_finished', 0)->whereIn('jenis', self::ADDITIONALS)->get()->keyBy('jenis');
    }

    /**
     * Rincian invoice dari keranjang dan isian form (sama dengan perhitungan web).
     *
     * @param  array{penyesuaian: int|float, penalty: int|float, penalty_akhir: int|float, ppn_dipungut: int|bool}  $input
     * @return array{total_awal: float, additionals: array<string, float>, total: float, ppn: float, pph: float, total_tagihan: float}
     */
    public function quote(Customer $customer, array $input, ?Collection $cart = null, ?\Illuminate\Support\Collection $additionals = null): array
    {
        $cart ??= $this->cart($customer);
        $additionals ??= $this->additionals($customer);
        $totalAwal = (float) $cart->sum('nominal_tagihan');
        $extra = [];
        foreach (self::ADDITIONALS as $kind) {
            $extra[$kind] = (float) ($additionals->get($kind)?->nominal ?? 0);
        }
        $total = $totalAwal + array_sum($extra) + (float) $input['penyesuaian'] - (float) $input['penalty'];
        $ppn = $customer->ppn == 1 ? $total * 0.11 : 0;
        $pph = $customer->pph == 1 ? $total * 0.02 : 0;
        $tagihan = (int) $input['ppn_dipungut'] === 1
            ? ($total + $ppn - $pph) - (float) $input['penalty_akhir']
            : ($total - $pph) - (float) $input['penalty_akhir'];

        return ['total_awal' => $totalAwal, 'additionals' => $extra, 'total' => $total, 'ppn' => $ppn, 'pph' => $pph, 'total_tagihan' => $tagihan];
    }

    /**
     * Buat Invoice Tagihan dari seluruh isi keranjang. [$expectedIds] (mobile):
     * nota yang dilihat pengguna; bila keranjang berubah (mis. dari web), ditolak.
     *
     * @param  array{penyesuaian: int|float, penalty: int|float, penalty_akhir: int|float, tanggal_hardcopy: string, estimasi_pembayaran: string, no_resi: string, no_validasi: string, ppn_dipungut: int|bool}  $input  tanggal Y-m-d
     * @param  list<int>|null  $expectedIds
     */
    public function checkout(Customer $customer, array $input, ?array $expectedIds = null): InvoiceTagihan
    {
        return DB::transaction(function () use ($customer, $input, $expectedIds): InvoiceTagihan {
            $cart = $this->cart($customer);
            Transaksi::whereIn('id', $cart->modelKeys())->lockForUpdate()->get();
            if ($expectedIds !== null) {
                $actual = $cart->modelKeys();
                sort($actual);
                sort($expectedIds);
                if ($actual !== array_map('intval', $expectedIds)) {
                    throw ValidationException::withMessages(['transaction_ids' => 'Isi keranjang berubah sejak dibuka. Muat ulang dan periksa kembali.']);
                }
            }
            if ($cart->isEmpty()) {
                throw ValidationException::withMessages(['transaction_ids' => 'Keranjang tagihan masih kosong.']);
            }
            $additionals = $this->additionals($customer);
            $quote = $this->quote($customer, $input, $cart, $additionals);
            $number = InvoiceTagihan::where('customer_id', $customer->id)->lockForUpdate()->max('no_invoice') + 1;
            $invoice = InvoiceTagihan::create([
                'penyesuaian' => $input['penyesuaian'], 'penalty' => $input['penalty'], 'penalty_akhir' => $input['penalty_akhir'],
                'tanggal_hardcopy' => $input['tanggal_hardcopy'], 'estimasi_pembayaran' => $input['estimasi_pembayaran'],
                'no_resi' => $input['no_resi'], 'no_validasi' => $input['no_validasi'], 'ppn_dipungut' => $input['ppn_dipungut'],
                'lunas' => 0, 'tanggal' => now()->toDateString(), 'total_awal' => $quote['total_awal'],
                'kompensasi_jr' => $quote['additionals']['kompensasi_jr'], 'invoice_kompensasi_jr_id' => $additionals->get('kompensasi_jr')?->id,
                'penyesuaian_bbm' => $quote['additionals']['penyesuaian_bbm'], 'invoice_penyesuaian_bbm_id' => $additionals->get('penyesuaian_bbm')?->id,
                'achievement' => $quote['additionals']['achievement'], 'invoice_achievement_id' => $additionals->get('achievement')?->id,
                'ppn' => $quote['ppn'], 'pph' => $quote['pph'], 'customer_id' => $customer->id, 'total_bayar' => 0,
                'sisa_tagihan' => $quote['total_tagihan'], 'total_tagihan' => $quote['total_tagihan'],
                'no_invoice' => $number, 'periode' => 'Periode '.$number,
            ]);
            $additionalIds = [];
            foreach ($additionals as $additional) {
                $additionalIds = array_merge($additionalIds, $additional->details->pluck('transaksi_additional_id')->all());
                $additional->update(['is_finished' => true]);
            }
            if ($additionalIds !== []) {
                TransaksiAdditional::whereIn('id', $additionalIds)->update(['status' => 3]);
            }
            if ($quote['ppn'] > 0) {
                PpnKeluaran::create(['invoice_tagihan_id' => $invoice->id, 'uraian' => 'PPN '.$invoice->periode, 'nominal' => $quote['ppn'], 'dipungut' => (int) $input['ppn_dipungut']]);
            }
            if ($quote['pph'] > 0) {
                PphPerusahaan::create(['invoice_tagihan_id' => $invoice->id, 'uraian' => 'PPh '.$invoice->periode, 'nominal' => $quote['pph']]);
            }
            Transaksi::whereIn('id', $cart->modelKeys())->update(['tagihan' => 1, 'keranjang' => 0]);
            $now = now();
            InvoiceTagihanDetail::insert(array_map(fn (int $id): array => ['invoice_tagihan_id' => $invoice->id, 'transaksi_id' => $id,
                'created_at' => $now, 'updated_at' => $now], $cart->modelKeys()));

            return $invoice;
        });
    }

    /** Nota yang bisa ditagih: bongkar selesai, tidak void, belum ditagih (opsional milik [$customer]). */
    private function billable(?Customer $customer = null): Builder
    {
        return Transaksi::query()->select('transaksis.*')->where('transaksis.status', 3)->where('transaksis.void', 0)->where('transaksis.tagihan', 0)
            ->when($customer, fn ($query) => $query->whereHas('kas_uang_jalan', fn ($query) => $query->where('customer_id', $customer->id)));
    }
}
