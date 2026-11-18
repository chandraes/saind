<?php

namespace App\Services;

use App\Models\GroupWa;
use App\Models\InvoiceTagihan;
use App\Models\KasBesar;
use App\Models\Pajak\PphPerusahaan;
use App\Models\Pajak\PpnKeluaran;
use App\Models\Rekening;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Pelunasan dan cicilan Invoice Tagihan customer: uang masuk Kas Besar, pajak
 * dilepas dari status tertahan, lalu WA ke grup Kas Besar. Dipakai bersama
 * halaman Invoice Tagihan web dan API mobile (Admin & SU).
 */
class CustomerInvoicePayment
{
    /**
     * Lunasi ([$installment] null) atau cicil invoice. Invoice dan saldo Kas
     * Besar dikunci di dalam transaksi; invoice lunas ditolak sehingga ketukan
     * ulang tidak mencatat uang masuk dua kali. [$expectedRemaining] (mobile):
     * sisa tagihan yang dilihat pengguna; bila berbeda, ditolak.
     *
     * @return array{0: InvoiceTagihan, 1: KasBesar}
     */
    public function pay(int $id, ?int $installment = null, ?int $expectedRemaining = null): array
    {
        return DB::transaction(function () use ($id, $installment, $expectedRemaining): array {
            $invoice = InvoiceTagihan::with('customer')->lockForUpdate()->findOrFail($id);
            if ((int) $invoice->lunas === 1) {
                throw ValidationException::withMessages(['invoice' => 'Invoice ini sudah lunas.']);
            }
            $remaining = (float) $invoice->sisa_tagihan;
            if ($expectedRemaining !== null && $expectedRemaining !== (int) round($remaining)) {
                throw ValidationException::withMessages(['amount' => 'Sisa tagihan berubah sejak dibuka. Muat ulang dan periksa kembali.']);
            }
            if ($installment !== null && ($installment <= 0 || $installment > $remaining)) {
                throw ValidationException::withMessages(['cicilan' => 'Cicilan tidak boleh lebih besar dari sisa tagihan']);
            }
            $amount = $installment ?? $remaining;
            $paidOff = $installment === null || $installment == $remaining;
            $invoice->update(['total_bayar' => $invoice->total_bayar + $amount, 'sisa_tagihan' => $remaining - $amount, 'lunas' => $paidOff ? 1 : 0]);
            if ($paidOff) {
                PpnKeluaran::where('invoice_tagihan_id', $invoice->id)->update(['onhold' => 0]);
                // Web: tombol Lunas juga melepas PPh; cicilan terakhir hanya PPN.
                if ($installment === null) {
                    PphPerusahaan::where('invoice_tagihan_id', $invoice->id)->update(['onhold' => 0]);
                }
            }
            $last = KasBesar::orderBy('id', 'desc')->lockForUpdate()->first();
            $rekening = Rekening::where('untuk', 'kas-besar')->firstOrFail();
            $cash = KasBesar::create([
                'uraian' => ($installment === null ? '' : 'Cicil ').$invoice->customer->singkatan.' - '.$invoice->periode,
                'jenis_transaksi_id' => 1, 'nominal_transaksi' => $amount, 'tanggal' => date('Y-m-d'),
                'nomor_kode_tagihan' => $paidOff ? (int) KasBesar::whereNotNull('nomor_kode_tagihan')->latest()->value('nomor_kode_tagihan') + 1 : null,
                'saldo' => ($last->saldo ?? 0) + $amount, 'modal_investor_terakhir' => $last->modal_investor_terakhir ?? 0,
                'transfer_ke' => substr($rekening->nama_rekening, 0, 15), 'no_rekening' => $rekening->nomor_rekening, 'bank' => $rekening->nama_bank,
            ]);

            return [$invoice, $cash];
        });
    }

    /** WA ke grup Kas Besar setelah commit; false bila gagal (uang sudah tercatat, jangan ulangi). */
    public function notify(InvoiceTagihan $invoice, KasBesar $cash, bool $installment): bool
    {
        try {
            $group = GroupWa::where('untuk', 'kas-besar')->firstOrFail();
            $rupiah = fn ($value): string => number_format((float) $value, 0, ',', '.');
            $account = "Ditransfer ke rek:\n\nBank     : {$cash->bank}\nNama    : {$cash->transfer_ke}\nNo. Rek : {$cash->no_rekening}\n\n".
                "==========================\nSisa Saldo Kas Besar : \nRp. ".$rupiah($cash->saldo)."\n\n".
                "Total Modal Investor : \nRp. ".$rupiah($cash->modal_investor_terakhir)."\n\n";
            if ($installment) {
                $message = "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n*CICILAN INVOICE*\n🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                    'Customer : '.$invoice->customer->singkatan."\nPeriode : ".$invoice->periode."\n\n".
                    'Nilai :  *Rp. '.$rupiah($cash->nominal_transaksi)."*\n\n".$account."Terima kasih 🙏🙏🙏\n";
            } else {
                $open = InvoiceTagihan::where('customer_id', $invoice->customer_id)->where('lunas', 0)->get();
                $openText = $open->isEmpty() ? '0' : $open->map(fn (InvoiceTagihan $row): string => $row->periode.' : Rp. '.$rupiah($row->sisa_tagihan)."\n")->implode('');
                $message = "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n*PEMBAYARAN INVOICE*\n🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                    'Tambang : '.$invoice->customer->singkatan."\nPeriode : ".$invoice->no_invoice."\n\n".
                    'Nilai :  *Rp. '.$rupiah($cash->nominal_transaksi)."*\n\n".$account.
                    "Tagihan : \nRp. ".$rupiah((new Transaksi)->sumNotaTagihan($invoice->customer_id))."\n\n".
                    "Invoice : \n".$openText."\n\nTerima kasih 🙏🙏🙏\n";
            }

            // Sama dengan KasBesar::sendWa (catat PesanWa lalu kirim), tetapi hasilnya diketahui.
            return app(GroupWa::class)->sendWa($group->nama_group, $message) !== false;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }
}
