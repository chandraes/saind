<?php

namespace App\Services;

use App\Models\InvoiceAddVendor;
use App\Models\InvoiceBayar;
use App\Models\KasVendor;
use App\Models\Pajak\PphSimpan;
use App\Models\Pajak\PpnMasukan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Pelunasan dan pembatalan Invoice Vendor, dipakai bersama halaman Invoice
 * Bayar web dan API mobile (Admin & SU). Invoice dikunci dan statusnya
 * diperiksa di dalam transaksi, sehingga ketukan ulang tidak mencatat Kas
 * Vendor dua kali.
 */
class VendorInvoicePayment
{
    /**
     * Lunasi invoice utama (Invoice Bayar) atau jenis tambahan (InvoiceAddVendor).
     * [$expected] (mobile): nominal yang dilihat pengguna; bila berbeda, ditolak.
     */
    public function settle(string $kind, int $id, ?int $expected = null): InvoiceBayar|InvoiceAddVendor
    {
        return DB::transaction(fn (): InvoiceBayar|InvoiceAddVendor => $kind === 'utama'
            ? $this->settleMain($id, $expected)
            : $this->settleAdditional($kind, $id, $expected));
    }

    /** Batalkan invoice utama yang belum dibayar: transaksinya kembali ke Nota Bayar. */
    public function cancel(int $id, ?int $expected = null): InvoiceBayar
    {
        return DB::transaction(function () use ($id, $expected): InvoiceBayar {
            $invoice = InvoiceBayar::lockForUpdate()->findOrFail($id);
            if ($invoice->lunas != 0) {
                throw ValidationException::withMessages(['invoice' => 'Invoice sudah ada pembayaran']);
            }
            $this->ensureAmount((int) $invoice->total_bayar, $expected);
            foreach ($invoice->transaksi as $transaksi) {
                $transaksi->update(['bayar' => 0]);
            }
            PpnMasukan::where('invoice_bayar_id', $invoice->id)->delete();
            $invoice->delete();

            return $invoice;
        });
    }

    private function settleMain(int $id, ?int $expected): InvoiceBayar
    {
        $invoice = InvoiceBayar::lockForUpdate()->findOrFail($id);
        if ($invoice->lunas != 0) {
            throw ValidationException::withMessages(['invoice' => 'Invoice ini sudah lunas.']);
        }
        $total = $invoice->sisa_bayar;
        $this->ensureAmount((int) $total, $expected);
        $invoice->update(['bayar' => $total, 'sisa_bayar' => 0, 'lunas' => 1]);
        PpnMasukan::where('invoice_bayar_id', $invoice->id)->update(['onhold' => 0]);
        PphSimpan::where('invoice_bayar_id', $invoice->id)->update(['onhold' => 0]);
        KasVendor::create(['tanggal' => now(), 'uraian' => 'Pembayaran '.' - '.$invoice->periode, 'bayar' => $total,
            'vendor_id' => $invoice->vendor_id, 'invoice_bayar_id' => $invoice->id,
            'sisa' => $this->lastBalance($invoice->vendor_id) - $total]);

        return $invoice;
    }

    private function settleAdditional(string $kind, int $id, ?int $expected): InvoiceAddVendor
    {
        $invoice = InvoiceAddVendor::with('vendor')->where('jenis', $kind)->where('status', 1)->lockForUpdate()->findOrFail($id);
        if ($invoice->is_finished) {
            throw ValidationException::withMessages(['invoice' => 'Invoice ini sudah lunas.']);
        }
        $this->ensureAmount((int) $invoice->total, $expected);
        $uraian = "{$invoice->vendor->nama} {$invoice->periode_invoice}";
        if ($invoice->ppn > 0) {
            PpnMasukan::create(['invoice_add_vendor_id' => $invoice->id, 'uraian' => $uraian, 'nominal' => $invoice->ppn, 'onhold' => 0]);
        }
        if ($invoice->pph > 0) {
            PphSimpan::create(['invoice_add_vendor_id' => $invoice->id, 'uraian' => $uraian, 'nominal' => $invoice->pph, 'onhold' => 0]);
        }
        KasVendor::create(['tanggal' => now(), 'uraian' => "Pembayaran - {$invoice->periode_invoice}", 'bayar' => $invoice->total,
            'vendor_id' => $invoice->vendor_id, 'invoice_add_vendor_id' => $invoice->id,
            'sisa' => $this->lastBalance($invoice->vendor_id) - $invoice->total]);
        $invoice->update(['is_finished' => true]);

        return $invoice;
    }

    private function lastBalance(int $vendorId): float
    {
        return (float) (KasVendor::where('vendor_id', $vendorId)->latest()->orderBy('id', 'desc')->lockForUpdate()->first()?->sisa ?? 0);
    }

    private function ensureAmount(int $actual, ?int $expected): void
    {
        if ($expected !== null && $expected !== $actual) {
            throw ValidationException::withMessages(['amount' => 'Nominal invoice berubah sejak dibuka. Muat ulang dan periksa kembali.']);
        }
    }
}
