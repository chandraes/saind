<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\GroupWa;
use App\Models\InvoiceAddVendor;
use App\Models\InvoiceBayar;
use App\Models\InvoiceBonus;
use App\Models\InvoiceCsr;
use App\Models\InvoiceTagihan;
use App\Models\InvoiceTagihanDetail;
use App\Models\KasBesar;
use App\Models\Pajak\PpnKeluaran;
use App\Models\Sponsor;
use App\Models\Transaksi;
use App\Models\Vendor;
use App\Services\CustomerInvoicePayment;
use App\Services\StarSender;
use App\Services\VendorInvoicePayment;
use App\Support\AdditionalInvoiceRecap;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoice = InvoiceTagihan::where('lunas', 0)->count();
        $bayar = InvoiceBayar::where('lunas', 0)->count();
        $bonus = InvoiceBonus::where('lunas', 0)->count();
        $csr = InvoiceCsr::where('lunas', 0)->count();

        return view('billing.transaksi.invoice.index', [
            'invoice' => $invoice,
            'bayar' => $bayar,
            'bonus' => $bonus,
            'csr' => $csr,
        ]);
    }

    public function tagihan()
    {
        $invoice = InvoiceTagihan::where('lunas', 0)->get();

        return view('billing.transaksi.invoice.tagihan.index', [
            'data' => $invoice,
        ]);
    }

    public function invoice_tagihan_detail(InvoiceTagihan $invoice)
    {
        // $data = InvoiceTagihanDetail::where('invoice_tagihan_id', $invoice->id)->get();
        $periode = $invoice->periode;
        $customer = Customer::find($invoice->customer_id);
        $data = $invoice->transaksi->load(['kas_uang_jalan', 'kas_uang_jalan.vehicle', 'kas_uang_jalan.vendor', 'kas_uang_jalan.rute', 'kas_uang_jalan.customer', 'do_checker']);

        return view('billing.transaksi.invoice.tagihan.detail', [
            'data' => $data,
            'customer' => $customer,
            'invoice' => $invoice,
            'periode' => $periode,
            'invoice_id' => $invoice->id,
        ]);
    }

    public function invoice_tagihan_detail_export(InvoiceTagihan $invoice)
    {
        $data = $invoice->transaksi->load(['kas_uang_jalan', 'kas_uang_jalan.vehicle', 'kas_uang_jalan.vendor', 'kas_uang_jalan.rute', 'kas_uang_jalan.customer', 'do_checker']);
        $customer = Customer::find($invoice->customer_id);

        // get latest data from month before current month
        // dd($bulan);
        $pdf = Pdf::loadview('billing.transaksi.invoice.tagihan.export', [
            'data' => $data,
            'invoice' => $invoice,
            'customer' => $customer,
            'periode' => $invoice->periode,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Invoice Tagihan '.$invoice->customer->singkatan.'.pdf');
    }

    public function tagihan_lunas(InvoiceTagihan $invoice, CustomerInvoicePayment $payments)
    {
        return $this->customerInvoicePayment($payments, $invoice, null, 'Invoice berhasil di lunasi');
    }

    public function tagihan_cicil(Request $request, InvoiceTagihan $invoice, CustomerInvoicePayment $payments)
    {
        $data = $request->validate([
            'cicilan' => 'required',
        ]);

        return $this->customerInvoicePayment($payments, $invoice, (int) str_replace('.', '', $data['cicilan']), 'Invoice berhasil di cicil');
    }

    /** Lunas/cicil Invoice Tagihan lewat CustomerInvoicePayment, lalu WA ke grup Kas Besar. */
    private function customerInvoicePayment(CustomerInvoicePayment $payments, InvoiceTagihan $invoice, ?int $installment, string $success): RedirectResponse
    {
        try {
            [$invoice, $cash] = $payments->pay($invoice->id, $installment);
        } catch (ValidationException $exception) {
            return redirect()->back()->with('error', collect($exception->errors())->flatten()->first());
        }
        $payments->notify($invoice, $cash, $installment !== null);

        return redirect()->back()->with('success', $success);
    }

    public function invoice_bayar(Request $request)
    {
        $vendorId = $request->vendor_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $user = Auth::user();

        if ($user->isVendorOwner()) {
            $vendorId = $user->vendor_id;
        }

        // 1. Ambil ID vendor yang HANYA memiliki invoice aktif untuk diproses
        $vendorIdsFromInvoice = InvoiceBayar::where('lunas', 0)->pluck('vendor_id');
        $vendorIdsFromAddInvoice = InvoiceAddVendor::where('status', 1)->where('is_finished', 0)->pluck('vendor_id');

        // Gabungkan ID vendor dan hilangkan duplikasi
        $activeVendorIds = $vendorIdsFromInvoice->merge($vendorIdsFromAddInvoice)->unique()->filter();

        // Query vendor berdasarkan ID yang aktif saja
        $vendors = Vendor::whereIn('id', $activeVendorIds)
            ->when($vendorId, function ($q) use ($vendorId) {
                return $q->where('id', $vendorId);
            })->orderBy('nama', 'asc')->get();

        // 2. Query InvoiceBayar dengan Filter
        $invoice = InvoiceBayar::with('vendor')
            ->where('lunas', 0)
            ->when($vendorId, function ($q) use ($vendorId) {
                return $q->where('vendor_id', $vendorId);
            })
            ->when($startDate, function ($q) use ($startDate) {
                return $q->whereDate('tempo', '>=', $startDate);
            })
            ->when($endDate, function ($q) use ($endDate) {
                return $q->whereDate('tempo', '<=', $endDate);
            })
            ->get();

        // 3. Query InvoiceAddVendor dengan Filter
        $addInvoice = InvoiceAddVendor::with(['vendor'])
            ->where('status', 1)
            ->where('is_finished', 0)
            ->when($vendorId, function ($q) use ($vendorId) {
                return $q->where('vendor_id', $vendorId);
            })
            ->when($startDate, function ($q) use ($startDate) {
                return $q->whereDate('tempo', '>=', $startDate);
            })
            ->when($endDate, function ($q) use ($endDate) {
                return $q->whereDate('tempo', '<=', $endDate);
            })
            ->get();

        return view('billing.transaksi.invoice.invoice-bayar', [
            'data' => $invoice,
            'addInvoice' => $addInvoice,
            'vendors' => $vendors,
        ]);
    }

    public function invoice_bayar_detail(InvoiceBayar $invoiceBayar)
    {
        abort_unless(auth()->user()->canAccessVendor($invoiceBayar->vendor_id), 404);
        $periode = $invoiceBayar->periode;
        $vendor = Vendor::find($invoiceBayar->vendor_id);
        // dd($invoiceBayar);

        return view('billing.transaksi.invoice.bayar.detail', [
            'data' => $invoiceBayar->transaksi->load(['kas_uang_jalan.vendor', 'kas_uang_jalan.vehicle', 'kas_uang_jalan.customer']),
            'pph_simpan' => $invoiceBayar->pph_simpan?->nominal ?? 0,
            'vendor' => $vendor,
            'periode' => $periode,
            'invoice_id' => $invoiceBayar->id,
        ]);
    }

    public function invoice_bayar_detail_add(InvoiceAddVendor $invoice)
    {
        if (Auth::user()->isVendorOwner() && $invoice->vendor_id != Auth::user()->vendor_id) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melihat invoice ini');
        }

        return view('billing.transaksi.invoice.invoice-bayar-detail-add', AdditionalInvoiceRecap::data($invoice));
    }

    public function invoice_bayar_lunas(InvoiceBayar $invoice, VendorInvoicePayment $payments)
    {
        if (! in_array(Auth::user()->role, ['admin', 'su'])) {
            return redirect()->back()->with('error', 'Anda Tidak Mempunyai Wewenang untuk Mengeksekusi Perintah Ini!!');
        }

        return $this->vendorInvoiceAction(fn () => $payments->settle('utama', $invoice->id), 'Invoice berhasil di lunasi');
    }

    public function invoice_bayar_jenis_lunas(InvoiceAddVendor $invoice, VendorInvoicePayment $payments)
    {
        return $this->vendorInvoiceAction(fn () => $payments->settle($invoice->jenis, $invoice->id), 'Invoice berhasil dilunasi', 'Terjadi kesalahan saat memproses pembayaran');
    }

    /** Jalankan aksi Invoice Vendor bersama (lihat VendorInvoicePayment) dan kembalikan pesan seperti sebelumnya. */
    private function vendorInvoiceAction(callable $action, string $success, ?string $failure = null): RedirectResponse
    {
        try {
            $action();
        } catch (ValidationException $exception) {
            return redirect()->back()->with('error', collect($exception->errors())->flatten()->first());
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->back()->with('error', $failure ?? 'Terjadi kesalahan saat memproses invoice');
        }

        return redirect()->back()->with('success', $success);
    }

    public function invoice_bonus()
    {
        $invoice = InvoiceBonus::where('lunas', 0)->get();

        return view('billing.transaksi.invoice.invoice-bonus', [
            'data' => $invoice,
        ]);
    }

    public function invoice_bonus_detail(InvoiceBonus $invoiceBonus)
    {
        $periode = $invoiceBonus->periode;
        $sponsor = Sponsor::find($invoiceBonus->sponsor_id);
        // dd($invoiceBayar);

        return view('billing.transaksi.invoice.invoice-bonus-detail', [
            'data' => $invoiceBonus->transaksi->load(['kas_uang_jalan.vendor', 'kas_uang_jalan.vehicle', 'kas_uang_jalan.customer', 'kas_uang_jalan.rute']),
            'sponsor' => $sponsor,
            'periode' => $periode,
        ]);
    }

    public function invoice_bonus_lunas(InvoiceBonus $invoice)
    {

        $last = KasBesar::latest()->orderBy('id', 'desc')->first();

        if ($last->saldo < $invoice->sisa_bonus) {
            return redirect()->back()->with('error', 'Saldo Kas Besar tidak mencukupi');
        }

        $total_bayar = $invoice->sisa_bonus;

        $invoice->update([
            'total_bayar' => $total_bayar,
            'sisa_bonus' => 0,
            'lunas' => 1,
        ]);

        $lastNomor = KasBesar::whereNotNull('nomor_kode_tagihan')->latest()->orderBy('id', 'desc')->first();
        $group = GroupWa::where('untuk', 'kas-besar')->first();

        if ($lastNomor == null) {
            $data['nomor_kode_tagihan'] = 1;
        } else {
            $data['nomor_kode_tagihan'] = $lastNomor->nomor_kode_tagihan + 1;
        }

        if ($last) {
            $data['uraian'] = 'Sponsor '.$invoice->periode;
            $data['jenis_transaksi_id'] = 2;
            $data['nominal_transaksi'] = $total_bayar;
            $data['saldo'] = $last->saldo - $total_bayar;
            $data['tanggal'] = date('Y-m-d');

            $data['transfer_ke'] = substr($invoice->sponsor->transfer_ke, 0, 15);
            $data['no_rekening'] = $invoice->sponsor->nomor_rekening;
            $data['bank'] = $invoice->sponsor->nama_bank;

            $data['modal_investor_terakhir'] = $last->modal_investor_terakhir;

            $store = KasBesar::create($data);

            $pesan = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Invoice Sponsor*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    "Uraian : Sponsor \n".
                    'Periode : '.$invoice->periode."\n\n".
                    'Nilai :  *Rp. '.number_format($data['nominal_transaksi'], 0, ',', '.')."*\n\n".
                    "Ditransfer ke rek:\n\n".
                    'Bank     : '.$data['bank']."\n".
                    'Nama    : '.$data['transfer_ke']."\n".
                    'No. Rek : '.$data['no_rekening']."\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Besar : \n".
                    'Rp. '.number_format($store->saldo, 0, ',', '.')."\n\n".
                    "Total Modal Investor : \n".
                    'Rp. '.number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
                    "Terima kasih 🙏🙏🙏\n";

            $send = new StarSender($group->nama_group, $pesan);
            $res = $send->sendGroup();
        }

        return redirect()->route('invoice.bonus.index')->with('success', 'Invoice berhasil di lunasi');
    }

    public function invoice_csr()
    {
        $invoice = InvoiceCsr::where('lunas', 0)->get();

        return view('billing.transaksi.invoice.invoice-csr', [
            'data' => $invoice,
        ]);
    }

    public function invoice_csr_detail(InvoiceCsr $invoiceCsr)
    {
        $periode = $invoiceCsr->periode;
        $customer = Customer::find($invoiceCsr->customer_id);

        return view('billing.transaksi.invoice.invoice-csr-detail', [
            'data' => $invoiceCsr->transaksi->load(['kas_uang_jalan.vendor', 'kas_uang_jalan.vehicle', 'kas_uang_jalan.customer', 'kas_uang_jalan.rute']),
            'periode' => $periode,
            'customer' => $customer,
        ]);
    }

    public function invoice_csr_lunas(InvoiceCsr $invoiceCsr)
    {
        $total_bayar = $invoiceCsr->total_csr;

        $last = KasBesar::latest()->orderBy('id', 'desc')->first();

        if ($last == null || $last->saldo < $total_bayar) {
            return redirect()->back()->with('error', 'Saldo Kas Besar tidak mencukupi');
        }

        $kasBesar['tanggal'] = date('Y-m-d');
        $kasBesar['uraian'] = 'CSR '.$invoiceCsr->customer->singkatan.' - '.$invoiceCsr->periode;
        $kasBesar['jenis_transaksi_id'] = 2;
        $kasBesar['nominal_transaksi'] = $total_bayar;
        $kasBesar['saldo'] = $last->saldo - $total_bayar;
        $kasBesar['transfer_ke'] = substr($invoiceCsr->customer->csr_transfer_ke, 0, 15);
        $kasBesar['no_rekening'] = $invoiceCsr->customer->csr_no_rekening;
        $kasBesar['bank'] = $invoiceCsr->customer->csr_bank;
        $kasBesar['modal_investor_terakhir'] = $last->modal_investor_terakhir;

        $invoiceCsr->update([
            'lunas' => 1,
        ]);

        $store = KasBesar::create($kasBesar);

        $dbWa = new GroupWa;
        $group = $dbWa->where('untuk', 'kas-besar')->first();

        $pesan = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Invoice CSR*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    'CSR : '.$invoiceCsr->customer->singkatan."\n".
                    'Periode : '.$invoiceCsr->periode."\n\n".
                    'Nilai :  *Rp. '.number_format($kasBesar['nominal_transaksi'], 0, ',', '.')."*\n\n".
                    "Ditransfer ke rek:\n\n".
                    'Bank     : '.$kasBesar['bank']."\n".
                    'Nama    : '.$kasBesar['transfer_ke']."\n".
                    'No. Rek : '.$kasBesar['no_rekening']."\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Besar : \n".
                    'Rp. '.number_format($store->saldo, 0, ',', '.')."\n\n".
                    "Total Modal Investor : \n".
                    'Rp. '.number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
                    "Terima kasih 🙏🙏🙏\n";

        $send = $dbWa->sendWa($group->nama_group, $pesan);

        return redirect()->route('billing.invoice-csr')->with('success', 'Invoice berhasil di lunasi');

    }

    public function invoice_tagihan_back(InvoiceTagihan $invoice)
    {

        if ($invoice->total_bayar != 0) {
            return redirect()->back()->with('error', 'Invoice sudah ada pembayaran');
        }

        DB::beginTransaction();

        $transaksi = $invoice->transaksi;
        // update tagihan in each transaksi
        foreach ($transaksi as $v) {
            $v->update([
                'tagihan' => 0,
            ]);
        }

        PpnKeluaran::where('invoice_tagihan_id', $invoice->id)->delete();

        $invoice->delete();

        DB::commit();

        return redirect()->back()->with('success', 'Invoice berhasil di batalkan');
    }

    public function invoice_bayar_back(InvoiceBayar $invoice, VendorInvoicePayment $payments)
    {
        if (! in_array(Auth::user()->role, ['su', 'admin'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk membatalkan invoice ini');
        }

        return $this->vendorInvoiceAction(fn () => $payments->cancel($invoice->id), 'Invoice berhasil di batalkan');
    }

    public function invoice_csr_back(InvoiceCsr $invoice)
    {
        if ($invoice->lunas != 0) {
            return redirect()->back()->with('error', 'Invoice sudah ada pembayaran');
        }

        DB::beginTransaction();

        $transaksi = $invoice->transaksi;
        // update tagihan in each transaksi
        foreach ($transaksi as $v) {
            $v->update([
                'csr' => 0,
            ]);
        }

        $invoice->delete();

        DB::commit();

        return redirect()->back()->with('success', 'Invoice berhasil di batalkan');

    }

    public function invoice_bonus_back(InvoiceBonus $invoice)
    {
        if ($invoice->lunas != 0) {
            return redirect()->back()->with('error', 'Invoice sudah ada pembayaran');
        }

        DB::beginTransaction();

        $transaksi = $invoice->transaksi;
        // update tagihan in each transaksi
        foreach ($transaksi as $v) {
            $v->update([
                'bonus' => 0,
            ]);
        }

        $invoice->delete();

        DB::commit();

        return redirect()->back()->with('success', 'Invoice berhasil di batalkan');

    }
}
