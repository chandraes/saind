<?php

namespace App\Services;

use App\Models\InvoiceBayar;
use App\Models\InvoiceBayarDetail;
use App\Models\KeranjangNotaBayar;
use App\Models\Pajak\PphSimpan;
use App\Models\Pajak\PpnMasukan;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VendorPaymentCart
{
    public function cart(User $user, Vendor $vendor): Builder
    {
        return KeranjangNotaBayar::query()->where('user_id', $user->id)->where('vendor_id', $vendor->id);
    }

    public function eligible(Vendor $vendor): Builder
    {
        return Transaksi::query()
            ->join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
            ->select('transaksis.*')
            ->where('transaksis.status', 3)
            ->where('transaksis.void', 0)
            ->where('transaksis.bayar', 0)
            ->where('kuj.vendor_id', $vendor->id);
    }

    /** @param list<int>|null $ids Null adds all eligible notes. */
    public function add(User $user, Vendor $vendor, ?array $ids = null): int
    {
        return DB::transaction(function () use ($user, $vendor, $ids): int {
            Vendor::query()->lockForUpdate()->findOrFail($vendor->id);
            $query = $this->eligible($vendor);
            if ($ids !== null) {
                $query->whereIn('transaksis.id', $ids);
            }
            $eligible = $query->lockForUpdate()->pluck('transaksis.id');
            if ($ids !== null && $eligible->count() !== count(array_unique($ids))) {
                throw ValidationException::withMessages([
                    'transaksi_ids' => 'Sebagian nota tidak tersedia untuk vendor ini. Muat ulang daftar.',
                ]);
            }

            foreach ($eligible as $id) {
                $this->cart($user, $vendor)->firstOrCreate([
                    'user_id' => $user->id,
                    'vendor_id' => $vendor->id,
                    'transaksi_id' => $id,
                ]);
            }

            return $eligible->count();
        });
    }

    /** @param list<int>|null $ids Null clears the cart. */
    public function remove(User $user, Vendor $vendor, ?array $ids = null): void
    {
        DB::transaction(function () use ($user, $vendor, $ids): void {
            Vendor::query()->lockForUpdate()->findOrFail($vendor->id);
            $cart = $this->cart($user, $vendor);
            if ($ids !== null) {
                $cart->whereIn('transaksi_id', $ids);
            }
            $cart->delete();
        });
    }

    public function notes(User $user, Vendor $vendor): Collection
    {
        return $this->eligible($vendor)
            ->whereIn('transaksis.id', $this->cart($user, $vendor)->select('transaksi_id'))
            ->with('kas_uang_jalan')->get();
    }

    /** @return array{gross: float, advance: float, net: float, ppn: int, pph: int, total: float, fingerprint: string} */
    public function quote(Vendor $vendor, Collection $notes): array
    {
        $gross = (float) $notes->sum('nominal_bayar');
        $advance = (float) $notes->sum('kas_uang_jalan.nominal_transaksi');
        $net = $gross - $advance;
        $ppn = $vendor->ppn == 1 ? (int) floor($gross * 0.11) : 0;
        $pph = $vendor->pph == 1 ? (int) floor($gross * ($vendor->pph_val / 100)) : 0;
        $total = $net - $pph + $ppn;
        $records = $notes->sortBy('id')->map(fn (Transaksi $note): array => [
            $note->id, $note->nominal_bayar, $note->kas_uang_jalan?->nominal_transaksi,
        ])->values()->all();

        return [
            'gross' => $gross,
            'advance' => $advance,
            'net' => $net,
            'ppn' => $ppn,
            'pph' => $pph,
            'total' => $total,
            'fingerprint' => hash('sha256', json_encode([$vendor->id, $records, $ppn, $pph], JSON_THROW_ON_ERROR)),
        ];
    }

    public function checkout(User $user, Vendor $vendor, string $tempo, string $fingerprint): InvoiceBayar
    {
        return DB::transaction(function () use ($user, $vendor, $tempo, $fingerprint): InvoiceBayar {
            $vendor = Vendor::query()->lockForUpdate()->findOrFail($vendor->id);
            $cart = $this->cart($user, $vendor);
            $ids = (clone $cart)->lockForUpdate()->pluck('transaksi_id');
            $notes = $this->eligible($vendor)->whereIn('transaksis.id', $ids)
                ->lockForUpdate()->with('kas_uang_jalan')->get();
            $quote = $this->quote($vendor, $notes);

            if ($notes->isEmpty() || ! hash_equals($quote['fingerprint'], $fingerprint)) {
                throw ValidationException::withMessages([
                    'fingerprint' => 'Keranjang kosong atau berubah. Periksa kembali ringkasan sebelum membuat invoice.',
                ]);
            }

            if (! $user->isAdministrator()) {
                $tempo = now()->addDays($vendor->jatuh_tempo_hari ?? 0)->toDateString();
            }

            $number = InvoiceBayar::query()->where('vendor_id', $vendor->id)->max('no_invoice') + 1;
            $invoice = InvoiceBayar::create([
                'tanggal' => now()->toDateString(),
                'tempo' => $tempo,
                'no_invoice' => $number,
                'vendor_id' => $vendor->id,
                'total_bayar' => $quote['total'],
                'sisa_bayar' => $quote['total'],
                'bayar' => 0,
                'lunas' => 0,
                'periode' => 'Periode '.$number,
            ]);

            if ($quote['ppn'] > 0) {
                PpnMasukan::create([
                    'invoice_bayar_id' => $invoice->id,
                    'uraian' => $vendor->nickname.' PPN '.$invoice->periode,
                    'nominal' => $quote['ppn'],
                ]);
            }

            if ($quote['pph'] > 0) {
                PphSimpan::create([
                    'invoice_bayar_id' => $invoice->id,
                    'uraian' => $vendor->nickname.' PPh '.$invoice->periode,
                    'nominal' => $quote['pph'],
                ]);
            }

            foreach ($notes as $note) {
                $note->update(['bayar' => 1]);

                InvoiceBayarDetail::create([
                    'invoice_bayar_id' => $invoice->id,
                    'transaksi_id' => $note->id,
                ]);
            }

            $cart->whereIn('transaksi_id', $ids)->delete();

            return $invoice;
        });
    }
}
