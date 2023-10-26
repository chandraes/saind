<?php

namespace App\Http\Controllers;

use App\Models\InvoiceTagihan;
use App\Models\InvoiceBayar;
use App\Models\InvoiceBonus;
use App\Models\KasBesar;
use App\Models\KasVendor;
use App\Models\Rekening;
use App\Models\GroupWa;
use Illuminate\Http\Request;
use App\Services\StarSender;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoice = InvoiceTagihan::where('lunas', 0)->count();
        $bayar = InvoiceBayar::where('lunas', 0)->count();
        $bonus = InvoiceBonus::where('lunas', 0)->count();

        return view('billing.transaksi.invoice.index', [
            'invoice' => $invoice,
            'bayar' => $bayar,
            'bonus' => $bonus,
        ]);
    }

    public function tagihan()
    {
        $invoice = InvoiceTagihan::where('lunas', 0)->get();

        return view('billing.transaksi.invoice.tagihan.index', [
            'data' => $invoice
        ]);
    }

    public function tagihan_lunas(InvoiceTagihan $invoice)
    {
        $total_bayar = $invoice->sisa_tagihan;

        $invoice->update([
            'total_bayar' => $total_bayar,
            'sisa_tagihan' => 0,
            'lunas' => 1
        ]);

        $group = GroupWa::where('untuk', 'kas-besar')->first();
        $last = KasBesar::latest()->first();

        $lastNomor = KasBesar::whereNotNull('nomor_kode_tagihan')->latest()->first();

        if($lastNomor == null)
        {
            $data['nomor_kode_tagihan'] = 1;
        } else {
            $data['nomor_kode_tagihan'] = $lastNomor->nomor_kode_tagihan + 1;
        }

        if ($last) {
            $data['uraian'] = $invoice->customer->singkatan.' - '.$invoice->periode;
            $data['jenis_transaksi_id'] = 1;
            $data['nominal_transaksi'] = $total_bayar;
            $data['saldo'] = $last->saldo + $total_bayar;
            $data['tanggal'] = date('Y-m-d');

            $rekening = Rekening::where('untuk', 'kas-besar')->first();

            $data['transfer_ke'] = substr($rekening->nama_rekening, 0, 15);
            $data['no_rekening'] = $rekening->nomor_rekening;
            $data['bank'] = $rekening->nama_bank;

            $data['modal_investor_terakhir'] = $last->modal_investor_terakhir;

            $store = KasBesar::create($data);

            $pesan ="🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                "*Invoice Tagihan*\n".
                 "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                 "*T".sprintf("%02d",$data['nomor_kode_tagihan'])."*\n\n".
                 "Tambang : ".$invoice->customer->singkatan."\n".
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

            $lastNomor = KasBesar::whereNotNull('nomor_kode_tagihan')->latest()->first();

            if($lastNomor == null)
            {
                $data['nomor_kode_tagihan'] = 1;
            } else {
                $data['nomor_kode_tagihan'] = $lastNomor->nomor_kode_tagihan + 1;
            }

        }

        $data['total_bayar'] = $data['cicilan'] + $invoice->total_bayar;

        $data['sisa_tagihan'] = $invoice->sisa_tagihan - $data['cicilan'];

        $invoice->update($data);

        $group = GroupWa::where('untuk', 'kas-besar')->first();

        $last = KasBesar::latest()->first();

        if ($last) {
            $data['uraian'] = 'Cicil '.$invoice->customer->singkatan.' - '.$invoice->periode;
            $data['jenis_transaksi_id'] = 1;
            $data['nominal_transaksi'] = $data['cicilan'];
            $data['saldo'] = $last->saldo + $data['cicilan'];
            $data['tanggal'] = date('Y-m-d');

            $rekening = Rekening::where('untuk', 'kas-besar')->first();

            $data['transfer_ke'] = substr($rekening->nama_rekening, 0, 15);
            $data['no_rekening'] = $rekening->nomor_rekening;
            $data['bank'] = $rekening->nama_bank;

            $data['modal_investor_terakhir'] = $last->modal_investor_terakhir;

            $store = KasBesar::create($data);

            $pesan ="🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                "*Invoice Tagihan Cicil*\n".
                 "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                 "Tambang : ".$invoice->customer->singkatan."\n".
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

        return redirect()->back()->with('success', 'Invoice berhasil di cicil');
    }

    public function invoice_bayar()
    {
        $invoice = InvoiceBayar::where('lunas', 0)->get();

        return view('billing.transaksi.invoice.invoice-bayar', [
            'data' => $invoice
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

        $last = KasVendor::where('vendor_id', $invoice->vendor_id)->latest()->first();

        $data['tanggal'] = now();
        $data['uraian'] = "Pembayaran ".' - '.$invoice->periode;
        $data['bayar'] = $total_bayar;
        $data['vendor_id'] = $invoice->vendor_id;

        if ($last) {
            $data['sisa'] = $last->sisa - $total_bayar;
        } else {
            $data['sisa'] = -$total_bayar;
        }

        KasVendor::create($data);

        return redirect()->route('invoice.bayar.index')->with('success', 'Invoice berhasil di lunasi');

    }

    public function invoice_bonus()
    {
        $invoice = InvoiceBonus::where('lunas', 0)->get();

        return view('billing.transaksi.invoice.invoice-bonus', [
            'data' => $invoice
        ]);
    }

    public function invoice_bonus_lunas(InvoiceBonus $invoice)
    {

        $last = KasBesar::latest()->first();

        if ($last->saldo < $invoice->sisa_bonus) {
            return redirect()->back()->with('error', 'Saldo Kas Besar tidak mencukupi');
        }

        $total_bayar = $invoice->sisa_bonus;

        $invoice->update([
            'total_bayar' => $total_bayar,
            'sisa_bonus' => 0,
            'lunas' => 1
        ]);

        $lastNomor = KasBesar::whereNotNull('nomor_kode_tagihan')->latest()->first();
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
                    "*T".sprintf("%02d",$data['nomor_kode_tagihan'])."*\n\n".
                    "Sponsor : ".$invoice->sponsor->nama."\n".
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
}
