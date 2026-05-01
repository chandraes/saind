<?php

namespace App\Http\Controllers;

use App\Models\InvoiceTagihan;
use App\Models\InvoiceTagihanDetail;
use App\Models\InvoiceBayar;
use App\Models\InvoiceBonus;
use App\Models\InvoiceCsr;
use App\Models\Customer;
use App\Models\Sponsor;
use App\Models\KasBesar;
use App\Models\Vendor;
use App\Models\KasVendor;
use App\Models\Rekening;
use App\Models\GroupWa;
use App\Models\InvoiceAddVendor;
use App\Models\Pajak\PphPerusahaan;
use App\Models\Pajak\PphSimpan;
use App\Models\Pajak\PpnKeluaran;
use App\Models\Pajak\PpnMasukan;
use App\Models\Transaksi;
use App\Models\TransaksiAdditional;
use Illuminate\Http\Request;
use App\Services\StarSender;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

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
            'csr' => $csr
        ]);
    }

    public function tagihan()
    {
        $invoice = InvoiceTagihan::where('lunas', 0)->get();

        return view('billing.transaksi.invoice.tagihan.index', [
            'data' => $invoice
        ]);
    }

    public function invoice_tagihan_detail(InvoiceTagihan $invoice)
    {
        // $data = InvoiceTagihanDetail::where('invoice_tagihan_id', $invoice->id)->get();
        $periode = $invoice->periode;
        $customer = Customer::find($invoice->customer_id);
        $data = $invoice->transaksi->load(['kas_uang_jalan', 'kas_uang_jalan.vehicle', 'kas_uang_jalan.vendor', 'kas_uang_jalan.rute', 'kas_uang_jalan.customer','do_checker']);
        return view('billing.transaksi.invoice.tagihan.detail', [
            'data' => $data,
            'customer' => $customer,
            'invoice' => $invoice,
            'periode' => $periode,
            'invoice_id' => $invoice->id
        ]);
    }

    public function invoice_tagihan_detail_export(InvoiceTagihan $invoice)
    {
        $data = $invoice->transaksi->load(['kas_uang_jalan', 'kas_uang_jalan.vehicle', 'kas_uang_jalan.vendor', 'kas_uang_jalan.rute', 'kas_uang_jalan.customer','do_checker']);
        $customer = Customer::find($invoice->customer_id);

        // get latest data from month before current month
        // dd($bulan);
        $pdf = PDF::loadview('billing.transaksi.invoice.tagihan.export', [
            'data' => $data,
            'invoice' => $invoice,
            'customer' => $customer,
            'periode' => $invoice->periode,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Invoice Tagihan '.$invoice->customer->singkatan.'.pdf');
    }

    public function tagihan_lunas(InvoiceTagihan $invoice)
    {
        $total_bayar = $invoice->sisa_tagihan;

        $invoice->update([
            'total_bayar' => $total_bayar,
            'sisa_tagihan' => 0,
            'lunas' => 1
        ]);

        $ppn = PpnKeluaran::where('invoice_tagihan_id', $invoice->id)->first();

        if ($ppn) {
            $ppn->update([
                'onhold' => 0
            ]);
        }

        $pph = PphPerusahaan::where('invoice_tagihan_id', $invoice->id)->first();

        if ($pph) {
            $pph->update([
                'onhold' => 0
            ]);
        }

        $group = GroupWa::where('untuk', 'kas-besar')->first();

        // $last = KasBesar::latest()->first();

        $lastNomor = KasBesar::whereNotNull('nomor_kode_tagihan')->latest()->first();

        if($lastNomor == null)
        {
            $data['nomor_kode_tagihan'] = 1;
        } else {
            $data['nomor_kode_tagihan'] = $lastNomor->nomor_kode_tagihan + 1;
        }

        // if ($last) {

            $dbKasBesar = new KasBesar();

            $data['uraian'] = $invoice->customer->singkatan.' - '.$invoice->periode;
            $data['jenis_transaksi_id'] = 1;
            $data['nominal_transaksi'] = $total_bayar;
            $data['saldo'] = $dbKasBesar->saldoTerakhir() + $total_bayar;
            $data['tanggal'] = date('Y-m-d');

            $rekening = Rekening::where('untuk', 'kas-besar')->first();

            $data['transfer_ke'] = substr($rekening->nama_rekening, 0, 15);
            $data['no_rekening'] = $rekening->nomor_rekening;
            $data['bank'] = $rekening->nama_bank;

            $data['modal_investor_terakhir'] = $dbKasBesar->modalInvestorTerakhir();

            $store = $dbKasBesar->create($data);

            $invoiceSisa = InvoiceTagihan::where('customer_id', $invoice->customer_id)->where('lunas', 0)->get();

            $invoiceSisaString = '';

            if ($invoiceSisa->count() > 0) {
                foreach ($invoiceSisa as $v) {
                    $invoiceSisaString .= $v->periode.' : Rp. '.number_format($v->sisa_tagihan, 0, ',', '.');
                    $invoiceSisaString .= "\n";
                }
            } else {
                $invoiceSisaString = '0';
            }

            $t = new Transaksi();

            $totalNotaTagihan = $t->sumNotaTagihan($invoice->customer_id);

            $pesan ="🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                "*PEMBAYARAN INVOICE*\n".
                 "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                 "Tambang : ".$invoice->customer->singkatan."\n".
                "Periode : ".$invoice->no_invoice."\n\n".
                 "Nilai :  *Rp. ".number_format($data['nominal_transaksi'], 0, ',', '.')."*\n\n".
                 "Ditransfer ke rek:\n\n".
                "Bank     : ".$data['bank']."\n".
                "Nama    : ".$data['transfer_ke']."\n".
                "No. Rek : ".$data['no_rekening']."\n\n".
                "==========================\n".
                "Sisa Saldo Kas Besar : \n".
                "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                "Total Modal Investor : \n".
                "Rp. ".number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
                "Tagihan : \n".
                "Rp. ".number_format($totalNotaTagihan, 0, ',', '.')."\n\n".
                "Invoice : \n".
                $invoiceSisaString."\n\n".
                "Terima kasih 🙏🙏🙏\n";

            $dbKasBesar->sendWa($group->nama_group, $pesan);
            // $send = new StarSender($group->nama_group, $pesan);
            // $res = $send->sendGroup();
        // }

        return redirect()->back()->with('success', 'Invoice berhasil di lunasi');
    }

    public function tagihan_cicil(Request $request, InvoiceTagihan $invoice)
    {
        $data = $request->validate([
            'cicilan' => 'required'
        ]);

        $data['cicilan'] = str_replace('.', '', $data['cicilan']);

        if($data['cicilan'] > $invoice->sisa_tagihan)
        {
            return redirect()->back()->with('error', 'Cicilan tidak boleh lebih besar dari sisa tagihan');
        }

        if ($data['cicilan'] == $invoice->sisa_tagihan) {
            $data['lunas'] = 1;

            PpnKeluaran::where('invoice_tagihan_id', $invoice->id)->update([
                'onhold' => 0
            ]);

            $lastNomor = KasBesar::whereNotNull('nomor_kode_tagihan')->latest()->first();

            if($lastNomor == null)
            {
                $data['nomor_kode_tagihan'] = 1;
            } else {
                $data['nomor_kode_tagihan'] = $lastNomor->nomor_kode_tagihan + 1;
            }

        } else {
            $data['lunas'] = 0;
            $data['nomor_kode_tagihan'] = null;
        }

        $data['total_bayar'] = $data['cicilan'] + $invoice->total_bayar;

        $data['sisa_tagihan'] = $invoice->sisa_tagihan - $data['cicilan'];

        $invoice->update([
            'total_bayar' => $data['total_bayar'],
            'sisa_tagihan' => $data['sisa_tagihan'],
            'lunas' => $data['lunas']
        ]);

        $group = GroupWa::where('untuk', 'kas-besar')->first();

        // $last = KasBesar::latest()->first();

        // if ($last) {
            $dbKas = new KasBesar();

            $data['uraian'] = 'Cicil '.$invoice->customer->singkatan.' - '.$invoice->periode;
            $data['jenis_transaksi_id'] = 1;
            $data['nominal_transaksi'] = $data['cicilan'];
            $data['saldo'] = $dbKas->saldoTerakhir() + $data['cicilan'];
            $data['tanggal'] = date('Y-m-d');

            $rekening = Rekening::where('untuk', 'kas-besar')->first();

            $data['transfer_ke'] = substr($rekening->nama_rekening, 0, 15);
            $data['no_rekening'] = $rekening->nomor_rekening;
            $data['bank'] = $rekening->nama_bank;

            $data['modal_investor_terakhir'] = $dbKas->modalInvestorTerakhir();

            $store = $dbKas->create([
                'uraian' => $data['uraian'],
                'jenis_transaksi_id' => $data['jenis_transaksi_id'],
                'nominal_transaksi' => $data['nominal_transaksi'],
                'nomor_kode_tagihan' => $data['nomor_kode_tagihan'],
                'saldo' => $data['saldo'],
                'tanggal' => $data['tanggal'],
                'transfer_ke' => $data['transfer_ke'],
                'no_rekening' => $data['no_rekening'],
                'bank' => $data['bank'],
                'modal_investor_terakhir' => $data['modal_investor_terakhir']
            ]);

            $pesan ="🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                "*CICILAN INVOICE*\n".
                 "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                 "Customer : ".$invoice->customer->singkatan."\n".
                "Periode : ".$invoice->periode."\n\n".
                 "Nilai :  *Rp. ".number_format($data['nominal_transaksi'], 0, ',', '.')."*\n\n".
                 "Ditransfer ke rek:\n\n".
                "Bank     : ".$data['bank']."\n".
                "Nama    : ".$data['transfer_ke']."\n".
                "No. Rek : ".$data['no_rekening']."\n\n".
                "==========================\n".
                "Sisa Saldo Kas Besar : \n".
                "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                "Total Modal Investor : \n".
                "Rp. ".number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
                "Terima kasih 🙏🙏🙏\n";

            $dbKas->sendWa($group->nama_group, $pesan);
            // $send = new StarSender($group->nama_group, $pesan);
            // $res = $send->sendGroup();
        // }

        return redirect()->back()->with('success', 'Invoice berhasil di cicil');
    }

    public function invoice_bayar()
    {
        $invoice = InvoiceBayar::with('vendor')->where('lunas', 0)->get();
        $addInvoice = InvoiceAddVendor::with(['vendor'])->where('status', 1)->where('is_finished', 0)->get();


        return view('billing.transaksi.invoice.invoice-bayar', [
            'data' => $invoice,
            'addInvoice' => $addInvoice
        ]);
    }

    public function invoice_bayar_detail(InvoiceBayar $invoiceBayar)
    {
        $periode = $invoiceBayar->periode;
        $vendor = Vendor::find($invoiceBayar->vendor_id);
        // dd($invoiceBayar);

        return view('billing.transaksi.invoice.bayar.detail', [
            'data' => $invoiceBayar->transaksi->load(['kas_uang_jalan.vendor', 'kas_uang_jalan.vehicle', 'kas_uang_jalan.customer']),
            'pph_simpan' => $invoiceBayar->pph_simpan?->nominal ?? 0,
            'vendor' => $vendor,
            'periode' => $periode,
            'invoice_id' => $invoiceBayar->id
        ]);
    }

    public function invoice_bayar_detail_add(InvoiceAddVendor $invoice)
    {

        $vendor = $invoice->vendor;

        $jenis = $invoice->jenis;
        $taId = $invoice->details->pluck('transaksi_additional_id');

        $data = TransaksiAdditional::with(['transaksi.kas_uang_jalan.vendor','transaksi.kas_uang_jalan.rute','transaksi.kas_uang_jalan.vehicle', 'rute', 'customer'])
                ->whereIn('id', $taId)->get();

        $dpp = $invoice->dpp;

        $groupedData = [];
        $totalKeseluruhan = 0;

        foreach ($data as $item) {
            // ... (Kode grouping Anda tetap tidak berubah sampai bagian bawah foreach)
            $customerName = $item->customer->nama ?? 'Tidak Diketahui';
            $ruteName = $item->rute->nama ?? 'Rute Lain';
            $tagihan_dari = $item->customer->tagihan_dari == 1 ? 'tonase' : 'timbangan_bongkar';
            $jarak = (float) ($item->jarak ?? 0);
            $muatan = (float) ($item->transaksi->{$tagihan_dari} ?? 0);

            if (!isset($groupedData[$customerName])) {
                $groupedData[$customerName] = [
                    'rutes' => [],
                    'subtotal_customer' => 0
                ];
            }

            if (!isset($groupedData[$customerName]['rutes'][$ruteName])) {
                $groupedData[$customerName]['rutes'][$ruteName] = [
                    'jarak' => $jarak,
                    'total_muatan' => 0,
                    'jumlah_trx' => 0
                ];
            }

            $groupedData[$customerName]['rutes'][$ruteName]['total_muatan'] += $muatan;
            $groupedData[$customerName]['rutes'][$ruteName]['jumlah_trx']++;
        }

        foreach ($groupedData as $customerName => &$customerData) {
            foreach ($customerData['rutes'] as $ruteName => &$ruteData) {
                $ruteData['subtotal'] = (int) round($ruteData['jarak'] * $ruteData['total_muatan'] * $dpp);
                $customerData['subtotal_customer'] += $ruteData['subtotal'];
            }
            $totalKeseluruhan += $customerData['subtotal_customer'];
        }
        unset($customerData, $ruteData);

        // === TAMBAHAN PERHITUNGAN PAJAK UNTUK TAMPILAN ===
        $ppn = $vendor->ppn == 1 ? (int) round($totalKeseluruhan * 0.11) : 0;
        $pph = $vendor->pph == 1 ? (int) round($totalKeseluruhan * ($vendor->pph_val / 100)) : 0;
        $totalAkhir = $totalKeseluruhan + $ppn - $pph;
        // ==================================================

        $stringJenis = TransaksiAdditional::JENIS[$jenis] ?? $jenis;

         return view('billing.transaksi.invoice.invoice-bayar-detail-add', [
            'data' => $data,
            'vendor' => $vendor,
            'jenis' => $jenis,
            'stringJenis' => $stringJenis,
            'invoice' => $invoice,
            'totalKeseluruhan' => $totalKeseluruhan,
            'groupedData' => $groupedData,
            // Kirim variabel baru ke blade
            'ppn' => $ppn,
            'pph' => $pph,
            'totalAkhir' => $totalAkhir,
        ]);
    }

    public function invoice_bayar_lunas(InvoiceBayar $invoice)
    {
        $total_bayar = $invoice->sisa_bayar;

        $invoice->update([
            'bayar' => $total_bayar,
            'sisa_bayar' => 0,
            'lunas' => 1
        ]);

        $last = KasVendor::where('vendor_id', $invoice->vendor_id)->latest()->orderBy('id', 'desc')->first();

        $data['tanggal'] = now();
        $data['uraian'] = "Pembayaran ".' - '.$invoice->periode;
        $data['bayar'] = $total_bayar;
        $data['vendor_id'] = $invoice->vendor_id;
        $data['invoice_bayar_id'] = $invoice->id;

        if ($last) {
            $data['sisa'] = $last->sisa - $total_bayar;
        } else {
            $data['sisa'] = -$total_bayar;
        }

        $ppn = PpnMasukan::where('invoice_bayar_id', $invoice->id)->first();

        if ($ppn) {
            $ppn->update([
                'onhold' => 0
            ]);
        }

        $pph = PphSimpan::where('invoice_bayar_id', $invoice->id)->first();

        if ($pph) {
            $pph->update([
                'onhold' => 0
            ]);
        }

        KasVendor::create($data);

        return redirect()->route('invoice.bayar.index')->with('success', 'Invoice berhasil di lunasi');

    }

   public function invoice_bayar_jenis_lunas(InvoiceAddVendor $invoice)
    {
        $invoice = $invoice->load(['vendor']);
        try {
            return DB::transaction(function () use ($invoice) {
                $uraianUmum = "{$invoice->vendor->nama} {$invoice->periode_invoice}";

                // 1. Catat PPN Masukan jika ada
                if ($invoice->ppn > 0) {
                    PpnMasukan::create([
                        'invoice_add_vendor_id' => $invoice->id,
                        'uraian' => $uraianUmum,
                        'nominal' => $invoice->ppn,
                        'onhold' => 0
                    ]);
                }

                // 2. Catat PPH Simpan jika ada
                if ($invoice->pph > 0) {
                    PphSimpan::create([
                        'invoice_add_vendor_id' => $invoice->id,
                        'uraian' => $uraianUmum,
                        'nominal' => $invoice->pph,
                        'onhold' => 0
                    ]);
                }

                // 3. Hitung Sisa Saldo di Kas Vendor
                $lastKas = KasVendor::where('vendor_id', $invoice->vendor_id)
                    ->orderBy('id', 'desc')
                    ->first();

                $sisaSebelumnya = $lastKas ? $lastKas->sisa : 0;

                // 4. Catat ke Kas Vendor
                KasVendor::create([
                    'tanggal' => now(),
                    'uraian' => "Pembayaran - {$invoice->periode_invoice}",
                    'bayar' => $invoice->total,
                    'vendor_id' => $invoice->vendor_id,
                    'invoice_add_vendor_id' => $invoice->id,
                    'sisa' => $sisaSebelumnya - $invoice->total,
                ]);

                $invoice->update(['is_finished' => true]);

                return redirect()
                    ->route('invoice.bayar.index')
                    ->with('success', 'Invoice berhasil dilunasi');
            });
        } catch (\Exception $e) {

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan saat memproses pembayaran');
        }
    }

    public function invoice_bonus()
    {
        $invoice = InvoiceBonus::where('lunas', 0)->get();

        return view('billing.transaksi.invoice.invoice-bonus', [
            'data' => $invoice
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
            'lunas' => 1
        ]);

        $lastNomor = KasBesar::whereNotNull('nomor_kode_tagihan')->latest()->orderBy('id', 'desc')->first();
        $group = GroupWa::where('untuk', 'kas-besar')->first();

        if($lastNomor == null)
        {
            $data['nomor_kode_tagihan'] = 1;
        } else {
            $data['nomor_kode_tagihan'] = $lastNomor->nomor_kode_tagihan + 1;
        }

        if ($last) {
            $data['uraian'] = "Bonus ".$invoice->sponsor->nama.' - '.$invoice->periode;
            $data['jenis_transaksi_id'] = 2;
            $data['nominal_transaksi'] = $total_bayar;
            $data['saldo'] = $last->saldo - $total_bayar;
            $data['tanggal'] = date('Y-m-d');

            $data['transfer_ke'] = substr($invoice->sponsor->transfer_ke, 0, 15);
            $data['no_rekening'] = $invoice->sponsor->nomor_rekening;
            $data['bank'] = $invoice->sponsor->nama_bank;

            $data['modal_investor_terakhir'] = $last->modal_investor_terakhir;

            $store = KasBesar::create($data);

            $pesan ="🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Invoice Bonus*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    "Uraian : Bonus ".$invoice->sponsor->nama."\n".
                    "Periode : ".$invoice->periode."\n\n".
                    "Nilai :  *Rp. ".number_format($data['nominal_transaksi'], 0, ',', '.')."*\n\n".
                    "Ditransfer ke rek:\n\n".
                    "Bank     : ".$data['bank']."\n".
                    "Nama    : ".$data['transfer_ke']."\n".
                    "No. Rek : ".$data['no_rekening']."\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Besar : \n".
                    "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                    "Total Modal Investor : \n".
                    "Rp. ".number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
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
            'data' => $invoice
        ]);
    }

    public function invoice_csr_detail(InvoiceCsr $invoiceCsr)
    {
        $periode = $invoiceCsr->periode;
        $customer = Customer::find($invoiceCsr->customer_id);

        return view('billing.transaksi.invoice.invoice-csr-detail', [
            'data' => $invoiceCsr->transaksi->load(['kas_uang_jalan.vendor', 'kas_uang_jalan.vehicle', 'kas_uang_jalan.customer', 'kas_uang_jalan.rute']),
            'periode' => $periode,
            'customer' => $customer
        ]);
    }

    public function invoice_csr_lunas(InvoiceCsr $invoiceCsr)
    {
        $total_bayar = $invoiceCsr->total_csr;

        $last = KasBesar::latest()->orderBy('id', 'desc')->first();

        if($last == null || $last->saldo < $total_bayar)
        {
            return redirect()->back()->with('error', 'Saldo Kas Besar tidak mencukupi');
        }

        $kasBesar['tanggal'] = date('Y-m-d');
        $kasBesar['uraian'] = "CSR ".$invoiceCsr->customer->singkatan.' - '.$invoiceCsr->periode;
        $kasBesar['jenis_transaksi_id'] = 2;
        $kasBesar['nominal_transaksi'] = $total_bayar;
        $kasBesar['saldo'] = $last->saldo - $total_bayar;
        $kasBesar['transfer_ke'] = substr($invoiceCsr->customer->csr_transfer_ke, 0, 15);
        $kasBesar['no_rekening'] = $invoiceCsr->customer->csr_no_rekening;
        $kasBesar['bank'] = $invoiceCsr->customer->csr_bank;
        $kasBesar['modal_investor_terakhir'] = $last->modal_investor_terakhir;

        $invoiceCsr->update([
            'lunas' => 1
        ]);

        $store = KasBesar::create($kasBesar);

        $dbWa = new GroupWa();
        $group = $dbWa->where('untuk', 'kas-besar')->first();

        $pesan ="🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Invoice CSR*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    "CSR : ".$invoiceCsr->customer->singkatan."\n".
                    "Periode : ".$invoiceCsr->periode."\n\n".
                    "Nilai :  *Rp. ".number_format($kasBesar['nominal_transaksi'], 0, ',', '.')."*\n\n".
                    "Ditransfer ke rek:\n\n".
                    "Bank     : ".$kasBesar['bank']."\n".
                    "Nama    : ".$kasBesar['transfer_ke']."\n".
                    "No. Rek : ".$kasBesar['no_rekening']."\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Besar : \n".
                    "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                    "Total Modal Investor : \n".
                    "Rp. ".number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
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
                'tagihan' => 0
            ]);
        }

        PpnKeluaran::where('invoice_tagihan_id', $invoice->id)->delete();

        $invoice->delete();

        DB::commit();

        return redirect()->back()->with('success', 'Invoice berhasil di batalkan');
    }

    public function invoice_bayar_back(InvoiceBayar $invoice)
    {
        if ($invoice->lunas != 0) {
            return redirect()->back()->with('error', 'Invoice sudah ada pembayaran');
        }

        DB::beginTransaction();

        $transaksi = $invoice->transaksi;
        // update tagihan in each transaksi
        foreach ($transaksi as $v) {
            $v->update([
                'bayar' => 0
            ]);
        }

        PpnMasukan::where('invoice_bayar_id', $invoice->id)->delete();

        $invoice->delete();

        DB::commit();

        return redirect()->back()->with('success', 'Invoice berhasil di batalkan');

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
                'csr' => 0
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
                'bonus' => 0
            ]);
        }

        $invoice->delete();

        DB::commit();

        return redirect()->back()->with('success', 'Invoice berhasil di batalkan');

    }

}
