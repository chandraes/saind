<?php

namespace App\Http\Controllers;

use App\Models\KasVendor;
use App\Models\KasBesar;
use App\Models\Vendor;
use App\Models\Vehicle;
use App\Models\GroupWa;
use App\Services\StarSender;
use Illuminate\Http\Request;

class FormVendorController extends Controller
{
    public function titipan()
    {
        $vehicle = Vehicle::all();

        return view('billing.vendor.titipan', [
            'vehicle' => $vehicle,
        ]);
    }

    public function titipan_store(Request $request)
    {
        $data = $request->validate([
            'id' => 'required',
            'nilai' => 'required',
        ]);
        $data['nilai'] = str_replace('.', '', $data['nilai']);

        $last = KasBesar::latest()->first();

        if ($last == null || $last->saldo < $data['nilai']) {
            return redirect()->back()->with('error', 'Saldo Kas Besar tidak mencukupi');
        }

        $vehicle = Vehicle::find($data['id']);

        $data['tanggal'] = date('Y-m-d');
        $data['jenis_transaksi_id'] = 2;
        $data['nominal_transaksi'] = $data['nilai'];
        $data['saldo'] = $last->saldo - $data['nominal_transaksi'];
        $data['uraian'] = "Titipan ".$vehicle->vendor->nama." (".$vehicle->nomor_lambung.")";
        $data['transfer_ke'] = $vehicle->vendor->nama_rekening;
        $data['bank'] = $vehicle->vendor->bank;
        $data['no_rekening'] = $vehicle->vendor->no_rekening;
        $data['modal_investor_terakhir'] = $last->modal_investor_terakhir;

        $kas['vendor_id'] = $vehicle->vendor_id;
        $kas['tanggal'] = $data['tanggal'];
        $kas['vehicle_id'] = $data['id'];
        $kas['uraian'] = "Titipan "." Nolam ".$vehicle->nomor_lambung;
        $kas['pinjaman'] = $data['nominal_transaksi'];

        $kasTerakhir = KasVendor::where('vendor_id', $vehicle->vendor_id)->latest()->first();

        if ($kasTerakhir) {
            $kas['sisa'] = $kasTerakhir->sisa + $data['nominal_transaksi'];
        } else {
            $kas['sisa'] = $data['nominal_transaksi'];
        }

        KasVendor::create($kas);

        $store = KasBesar::create($data);

        $group = GroupWa::where('untuk', 'kas-besar')->first();

        $pesan =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Form Vendor Titipan*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    "No. Lambung : ".$vehicle->nomor_lambung."\n".
                    "Vendor : ".$vehicle->vendor->nama."\n\n".
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

        return redirect()->route('billing.index')->with('success', 'Data berhasil disimpan');

    }

    public function get_kas_vendor(Request $request)
    {
        $data = KasVendor::where('vendor_id', $request->vendor_id)->latest()->first();

        $sisa = $data ? $data->sisa : 0;

        return response()->json($sisa);
    }

    public function pelunasan()
    {
        $vendor = Vendor::all();

        return view('billing.vendor.pelunasan', [
            'vendor' => $vendor,
        ]);
    }

    public function pelunasan_store(Request $request)
    {
        $data = $request->validate([
            'vendor_id' => 'required',
            'nominal' => 'required',
        ]);

        // make $data['nominal'] into positive number
        $data['nominal'] = $data['nominal'] * -1;
        $v = Vendor::find($data['vendor_id']);
        $last = KasBesar::latest()->first();

        if ($last == null || $last->saldo < $data['nominal']) {
            return redirect()->back()->with('error', 'Saldo Kas Besar tidak mencukupi');
        }

        $lastNomor = KasBesar::whereNotNull('nomor_kode_tagihan')->latest()->first();

        if ($lastNomor)  {
            $kas['nomor_kode_tagihan'] = 1;
        } else {
            $kas['nomor_kode_tagihan'] = $lastNomor->nomor_kode_tagihan + 1;
        }

        $vendor['vendor_id'] = $data['vendor_id'];
        $vendor['tanggal'] = date('Y-m-d');
        $vendor['uraian'] = "Pelunasan Vendor";
        $vendor['pinjaman'] = $data['nominal'];
        $vendor['sisa'] = 0;

        $kas['tanggal'] = date('Y-m-d');
        $kas['uraian'] = "Pelunasan Vendor ".$v->nama;
        $kas['jenis_transaksi_id'] = 2;
        $kas['nominal_transaksi'] = $data['nominal'];
        $kas['saldo'] = $last->saldo - $data['nominal'];
        $kas['transfer_ke'] = substr($v->nama_rekening, 0, 15);
        $kas['bank'] = $v->bank;
        $kas['no_rekening'] = $v->no_rekening;
        $kas['modal_investor_terakhir'] = $last->modal_investor_terakhir;

        KasVendor::create($vendor);

        $store = KasBesar::create($kas);

        $group = GroupWa::where('untuk', 'kas-besar')->first();

        $pesan =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Form Pelunasan Vendor*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    "Vendor : ".$v->nama."\n\n".
                    "Nilai :  *Rp. ".number_format($kas['nominal_transaksi'], 0, ',', '.')."*\n\n".
                    "Ditransfer ke rek:\n\n".
                    "Bank     : ".$kas['bank']."\n".
                    "Nama    : ".$kas['transfer_ke']."\n".
                    "No. Rek : ".$kas['no_rekening']."\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Besar : \n".
                    "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                    "Total Modal Investor : \n".
                    "Rp. ".number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
                    "Terima kasih 🙏🙏🙏\n";

        $send = new StarSender($group->nama_group, $pesan);
        $res = $send->sendGroup();

        return redirect()->route('billing.index')->with('success', 'Data berhasil disimpan');

    }
}
