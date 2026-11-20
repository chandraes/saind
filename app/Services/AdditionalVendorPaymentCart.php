<?php

namespace App\Services;

use App\Models\InvoiceAdditional;
use App\Models\InvoiceAddVendor;
use App\Models\TransaksiAdditional;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdditionalVendorPaymentCart
{
    public function pending(Vendor $vendor, string $kind): ?InvoiceAddVendor
    {
        return InvoiceAddVendor::query()->where('vendor_id', $vendor->id)
            ->where('jenis', $kind)->where('status', 0)->first();
    }

    /** @param list<int> $selectedIds */
    public function add(Vendor $vendor, string $jenis, array $selectedIds, float $dpp): InvoiceAddVendor
    {
        return DB::transaction(function () use ($vendor, $jenis, $selectedIds, $dpp): InvoiceAddVendor {
            $vendor = Vendor::query()->lockForUpdate()->findOrFail($vendor->id);
            $rekapJenis = TransaksiAdditional::with(['transaksi', 'customer'])
                ->where('jenis', $jenis)
                ->where('vendor_id', $vendor->id)
                ->where('status', 3)
                ->whereIn('id', $selectedIds)
                ->lockForUpdate()
                ->get();

            if ($rekapJenis->count() !== count(array_unique($selectedIds))) {
                throw ValidationException::withMessages(['transaksi_ids' => 'Sebagian nota tambahan tidak tersedia. Muat ulang daftar.']);
            }

            $rekapIds = $rekapJenis->pluck('id');
            $maxDpp = InvoiceAdditional::whereHas('details', fn ($query) => $query->whereIn('transaksi_additional_id', $rekapIds))->max('dpp');
            if ($maxDpp !== null && $dpp > (float) $maxDpp) {
                throw ValidationException::withMessages(['dpp' => 'DPP vendor tidak boleh lebih besar dari DPP tagihan.']);
            }

            $existingInvoice = InvoiceAddVendor::where('vendor_id', $vendor->id)
                ->where('jenis', $jenis)
                ->where('status', 0)
                ->lockForUpdate()
                ->first();

            // Validasi duplikasi
            $existingDetailIds = DB::table('invoice_add_vendor_details')
                ->whereIn('transaksi_additional_id', $rekapIds)
                ->pluck('transaksi_additional_id')
                ->toArray();

            if (! empty($existingDetailIds)) {
                throw ValidationException::withMessages(['transaksi_ids' => 'Beberapa transaksi yang dipilih sudah ada di keranjang/invoice lain.']);
            }

            // Kalkulasi Total DPP khusus item terpilih
            $totalKeseluruhan = $rekapJenis->sum(function ($item) use ($dpp) {
                if (! $item->customer || ! $item->transaksi) {
                    return 0;
                }

                $tagihan_dari = $item->customer->tagihan_dari == 1 ? 'tonase' : 'timbangan_bongkar';
                if (empty($tagihan_dari)) {
                    return 0;
                }

                $jarak = (float) ($item->jarak ?? 0);
                $muatan = (float) ($item->transaksi->$tagihan_dari ?? 0);

                return $jarak * $muatan * $dpp;
            });
            $totalKeseluruhan = (int) round($totalKeseluruhan);

            // Perhitungan Pajak
            $ppn = $vendor->ppn == 1 ? (int) round($totalKeseluruhan * 0.11) : 0;
            $pph = $vendor->pph == 1 ? (int) round($totalKeseluruhan * ($vendor->pph_val / 100)) : 0;
            $totalAkhir = $totalKeseluruhan + $ppn - $pph;

            // Proses Invoice
            if ($existingInvoice) {
                if (bccomp((string) $existingInvoice->dpp, (string) $dpp, 4) !== 0) {
                    throw ValidationException::withMessages(['dpp' => 'Gunakan DPP yang sama dengan keranjang.']);
                }

                $existingInvoice->increment('nominal', $totalKeseluruhan);
                $existingInvoice->increment('ppn', $ppn);
                $existingInvoice->increment('pph', $pph);

                $existingInvoice->update([
                    'total' => $existingInvoice->nominal + $existingInvoice->ppn - $existingInvoice->pph,
                ]);

                $invoice = $existingInvoice;
            } else {
                $invoice = InvoiceAddVendor::create([
                    'vendor_id' => $vendor->id,
                    'jenis' => $jenis,
                    'nominal' => $totalKeseluruhan,
                    'dpp' => $dpp,
                    'ppn' => $ppn,
                    'pph' => $pph,
                    'total' => $totalAkhir,
                    'status' => 0,
                    'is_finished' => false,
                ]);
            }

            // Insert Detail
            $detailData = $rekapJenis->map(fn ($item) => [
                'invoice_add_vendor_id' => $invoice->id,
                'transaksi_additional_id' => $item->id,
                'transaksi_id' => $item->transaksi_id,
                'created_at' => now(),
                'updated_at' => now(),
            ])->toArray();

            DB::table('invoice_add_vendor_details')->insert($detailData);

            // Update Status HANYA transaksi yang dicentang
            TransaksiAdditional::whereIn('id', $rekapIds)->update(['status' => 4]);

            return $invoice;
        });
    }

    public function fingerprint(InvoiceAddVendor $invoice): string
    {
        return hash('sha256', json_encode([
            $invoice->id,
            $invoice->dpp,
            $invoice->nominal,
            $invoice->ppn,
            $invoice->pph,
            $invoice->details()->orderBy('transaksi_additional_id')->pluck('transaksi_additional_id')->all(),
        ], JSON_THROW_ON_ERROR));
    }

    public function finish(Vendor $vendor, string $kind, InvoiceAddVendor $invoice, ?string $due, ?string $fingerprint = null): void
    {
        DB::transaction(function () use ($vendor, $kind, $invoice, $due, $fingerprint): void {
            Vendor::query()->lockForUpdate()->findOrFail($vendor->id);
            $invoice = InvoiceAddVendor::query()->whereKey($invoice->id)->where('vendor_id', $vendor->id)
                ->where('jenis', $kind)->where('status', 0)->lockForUpdate()->first();
            if ($invoice === null) {
                throw ValidationException::withMessages(['invoice' => 'Invoice tidak valid untuk keranjang ini.']);
            }

            if ($fingerprint !== null && ! hash_equals($this->fingerprint($invoice), $fingerprint)) {
                throw ValidationException::withMessages(['fingerprint' => 'Keranjang berubah. Periksa kembali ringkasan.']);
            }

            $ids = $invoice->details()->pluck('transaksi_additional_id');
            TransaksiAdditional::whereIn('id', $ids)->update(['status' => $due === null ? 3 : 5]);
            if ($due === null) {
                $invoice->details()->delete();
                $invoice->delete();
            } else {
                $invoice->update(['tempo' => $due, 'status' => 1]);
            }
        });
    }
}
