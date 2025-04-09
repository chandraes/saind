<?php

namespace App\Http\Controllers;

use App\Models\KasVendor;
use App\Models\KasBesar;
use App\Models\Vendor;
use App\Models\Vehicle;
use App\Models\Rekening;
use App\Models\GroupWa;
use App\Services\StarSender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormVendorController extends Controller
{
    public function titipan()
    {
        $vehicle = Vendor::where('status', 'aktif')->get();

        return view('billing.vendor.titipan', [
            'vendor' => $vehicle,
        ]);
    }

    public function titipan_store(Request $request)
    {
        $data = $request->validate([
            'id' => 'required',
            'nilai' => 'required',
            'uraian' => 'required',
            'transfer_ke' => 'required',
            'bank' => 'required',
            'no_rekening' => 'required',
        ]);

        $data['nilai'] = str_replace('.', '', $data['nilai']);

        $data['uraian'] = substr($data['uraian'], 0, 20);

        $v = Vendor::find($data['id']);

        $sisa = KasVendor::where('vendor_id', $data['id'])->latest()->orderBy('id', 'desc')->first()->sisa ?? 0;

        $mobil = Vehicle::where('vendor_id', $data['id'])->whereNot('status', 'nonaktif')->count();

        $plafon = ($v->plafon_titipan * $mobil) - $sisa;

        if ($data['nilai'] > $plafon) {
            return redirect()->back()->with('error', 'Nilai melebihi plafon titipan');
        }
        // dd($plafon);
        $last = KasBesar::latest()->orderBy('id', 'desc')->first();

        if ($last == null || $last->saldo < $data['nilai']) {
            return redirect()->back()->with('error', 'Saldo Kas Besar tidak mencukupi');
        }

        $d['tanggal'] = date('Y-m-d');
        $d['jenis_transaksi_id'] = 2;
        $d['nominal_transaksi'] = $data['nilai'];
        $d['saldo'] = $last->saldo - $d['nominal_transaksi'];
        $d['uraian'] = $data['uraian'];
        $d['transfer_ke'] = substr($data['transfer_ke'], 0, 15);
        $d['bank'] = $data['bank'];
        $d['no_rekening'] = $data['no_rekening'];
        $d['modal_investor_terakhir'] = $last->modal_investor_terakhir;

        $kas['vendor_id'] = $v->id;
        $kas['tanggal'] = $d['tanggal'];
        // $kas['vehicle_id'] = $data['id'];
        $kas['uraian'] = $data['uraian'];
        $kas['pinjaman'] = $d['nominal_transaksi'];

        $kasTerakhir = KasVendor::where('vendor_id', $data['id'])->latest()->orderBy('id', 'desc')->first();

        if ($kasTerakhir) {
            $kas['sisa'] = $kasTerakhir->sisa + $d['nominal_transaksi'];
        } else {
            $kas['sisa'] = $d['nominal_transaksi'];
        }

        try {
            DB::beginTransaction();

            $store2 = KasVendor::create($kas);

            $store = KasBesar::create($d);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Terjadi kesalahan. '. $th->getMessage());
        }

        $dbWa = new GroupWa();

        $group = $dbWa->where('untuk', 'kas-besar')->first();

        $pesan =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Form Titipan Vendor*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    "Vendor : ".$v->nama."\n".
                    "Uraian : ".$d['uraian']."\n\n".
                    "Nilai :  *Rp. ".number_format($d['nominal_transaksi'], 0, ',', '.')."*\n\n".
                    "Ditransfer ke rek:\n\n".
                    "Bank     : ".$d['bank']."\n".
                    "Nama    : ".$d['transfer_ke']."\n".
                    "No. Rek : ".$d['no_rekening']."\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Besar : \n".
                    "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                    "Total Modal Investor : \n".
                    "Rp. ".number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
                    "Total Kasbon Vendor : \n".
                    "Rp. ".number_format($store2->sisa, 0, ',', '.')."\n\n".
                    "Terima kasih 🙏🙏🙏\n";

        $send = $dbWa->sendWa($group->nama_group, $pesan);

        return redirect()->route('billing.index')->with('success', 'Data berhasil disimpan');

    }

    public function get_vehicle(Request $request)
    {
        $data = Vehicle::where('vendor_id', $request->id)->whereNot('status', 'nonaktif')->pluck('nomor_lambung');
        // change data to string with comma separator
        $data = implode(', ', $data->toArray());

        return response()->json($data);
    }

    public function get_plafon_titipan(Request $request)
    {
        $vendor = Vendor::find($request->id);

        $kas = KasVendor::where('vendor_id', $request->id)->latest()->orderBy('id', 'desc')->first()->sisa ?? 0;

        $mobil = Vehicle::where('vendor_id', $request->id)->whereNot('status', 'nonaktif')->count();

        $totalPlafon =$vendor->plafon_titipan * $mobil;

        $plafon = $totalPlafon - $kas;

        // dd($plafon);
        return response()->json($plafon);
    }

    public function get_kas_vendor(Request $request)
    {
        $data = KasVendor::where('vendor_id', $request->vendor_id)->orderBy('id', 'desc')->first();

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

        $dbKasBesar = new KasBesar();
        // make $data['nominal'] into positive number
        $data['nominal'] = $data['nominal'] * -1;

        $v = Vendor::find($data['vendor_id']);

        $last = $dbKasBesar->lastKasBesar();

        if ($last == null || $last->saldo < $data['nominal']) {
            return redirect()->back()->with('error', 'Saldo Kas Besar tidak mencukupi');
        }

        $sisa = KasVendor::where('vendor_id', $data['vendor_id'])->orderBy('id', 'desc')->first()->sisa;

        // make $sisa into positive number
        $sisaPositif = $sisa * -1;

        if ($sisaPositif < $data['nominal']) {
            return redirect()->back()->with('error', 'Nominal melebihi sisa tagihan');
        }

        $kas['nomor_kode_tagihan'] = $dbKasBesar->generateNomorTagihan();

        $vendor['vendor_id'] = $data['vendor_id'];
        $vendor['tanggal'] = date('Y-m-d');
        $vendor['uraian'] = "Pelunasan Vendor";
        $vendor['pinjaman'] = $data['nominal'];
        $vendor['sisa'] = $sisa + $data['nominal'];

        $kas['tanggal'] = date('Y-m-d');
        $kas['uraian'] = "Pelunasan Vendor ".$v->nama;
        $kas['jenis_transaksi_id'] = 2;
        $kas['nominal_transaksi'] = $data['nominal'];
        $kas['saldo'] = $last->saldo - $data['nominal'];
        $kas['transfer_ke'] = substr($v->nama_rekening, 0, 15);
        $kas['bank'] = $v->bank;
        $kas['no_rekening'] = $v->no_rekening;
        $kas['modal_investor_terakhir'] = $last->modal_investor_terakhir;

        try {
            DB::beginTransaction();

            $store2 = KasVendor::create($vendor);

            $store = $dbKasBesar->create($kas);

            DB::commit();
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();

            return redirect()->back()->with('error', 'Terjadi kesalahan. '. $th->getMessage());
        }

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
                    "Total Kasbon Vendor : \n".
                    "Rp. ".number_format($store2->sisa, 0, ',', '.')."\n\n".
                    "Terima kasih 🙏🙏🙏\n";

        $dbKasBesar->sendWa($group->nama_group, $pesan);

        return redirect()->route('billing.index')->with('success', 'Data berhasil disimpan');

    }

    public function bayar()
    {
        $vendor = Vendor::all();

        return view('billing.vendor.bayar', [
            'vendor' => $vendor,
        ]);
    }

    public function bayar_store(Request $request)
    {
        $data = $request->validate([
            'vendor_id' => 'required',
            'nilai' => 'required',
            'uraian' => 'required',
        ]);

        $role = auth()->user()->role;

        $dbKasBesar = new KasBesar();

        $data['nominal'] = str_replace('.', '', $data['nilai']);

        $v = Vendor::find($data['vendor_id']);
        // $last = $dbKasBesar->orderBy('id', 'desc')->first();
        $rekening = Rekening::where('untuk', 'kas-besar')->first();

        $kas['nomor_kode_tagihan'] = $dbKasBesar->generateNomorTagihan();
        $kas['tanggal'] = date('Y-m-d');
        $kas['uraian'] = $data['uraian'];
        $kas['jenis_transaksi_id'] = 1;
        $kas['nominal_transaksi'] = $data['nominal'];
        $kas['saldo'] = $dbKasBesar->saldoTerakhir() + $data['nominal'];
        $kas['transfer_ke'] = substr($rekening->nama_rekening, 0, 15);
        $kas['bank'] = $rekening->nama_bank;
        $kas['no_rekening'] = $rekening->nomor_rekening;
        $kas['modal_investor_terakhir'] = $dbKasBesar->modalInvestorTerakhir();

        $sisaTerakhir = KasVendor::where('vendor_id', $data['vendor_id'])->orderBy('id', 'desc')->first()->sisa ?? 0;

        if ($role != 'admin' && $role != 'su') {
            if ($sisaTerakhir < $data['nominal']) {
                return redirect()->back()->with('error', 'Nilai melebihi sisa tagihan');
            }
        }

        $vendor['vendor_id'] = $data['vendor_id'];
        $vendor['tanggal'] = date('Y-m-d');
        $vendor['uraian'] = $data['uraian'];
        $vendor['bayar'] = $data['nominal'];
        $vendor['sisa'] = $sisaTerakhir - $vendor['bayar'];

        try {
            DB::beginTransaction();

            $store2 = KasVendor::create($vendor);

            $store = $dbKasBesar->create($kas);

            DB::commit();

        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Terjadi kesalahan. '. $th->getMessage());
        }

        $dbWa = new GroupWa();

        $group = $dbWa->where('untuk', 'kas-besar')->first();

        $pesan =    "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                    "*Form Pelunasan dari Vendor*\n".
                    "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                    "Vendor : ".$v->nama."\n".
                    "Uraian : ".$data['uraian']."\n\n".
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
                    "Total Kasbon Vendor : \n".
                    "Rp. ".number_format($store2->sisa, 0, ',', '.')."\n\n".
                    "Terima kasih 🙏🙏🙏\n";

        $dbWa->sendWa($group->nama_group, $pesan);

        return redirect()->route('billing.index')->with('success', 'Data berhasil disimpan');
    }
}
