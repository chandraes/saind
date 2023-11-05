<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Vendor;
use App\Models\BbmStoring;
use App\Models\KasBesar;
use App\Models\KasVendor;
use App\Models\GroupWa;
use App\Services\StarSender;
use App\Models\Rekening;
use Illuminate\Http\Request;

class FormStoringConroller extends Controller
{
    public function index()
    {
        $vehicle = Vehicle::whereNot('status', 'nonaktif')->get();
        $storing = BbmStoring::all();
        $rekening = Rekening::where('untuk', 'mekanik')->first();

        return view('billing.storing.index', [
            'vehicle' => $vehicle,
            'storing' => $storing,
            'rekening' => $rekening,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
                    'id' => 'required',
                    'storing_id' => 'required',
                    'jasa' => 'nullable',
                ]);


        $rekening = Rekening::where('untuk', 'mekanik')->first();

        $vehicle = Vehicle::find($request->id);

        $vendorId = $vehicle->vendor_id;

        // dd($vendorId);
        $storing = BbmStoring::find($request->storing_id);

        $last = KasVendor::where('vendor_id', $vendorId)->latest()->orderBy('id', 'desc')->first();

        $vendor['vendor_id'] = $vendorId;
        $vendor['bbm_storing_id'] = $request->storing_id;
        $vendor['vehicle_id'] = $request->id;
        $vendor['tanggal'] = date('Y-m-d');
        $vendor['uraian'] = 'BBM Storing '.$vehicle->nomor_lambung;
        $vendor['storing'] = 1;

        if (!empty($data['jasa'])) {
            $data['jasa'] = str_replace('.', '', $data['jasa']);
            $vendor['pinjaman'] = $storing->biaya_vendor;
        } else {
            $data['jasa'] = 0;
            $vendor['pinjaman'] = $storing->biaya_vendor;
        }

        if ($last) {
            $vendor['sisa'] = $last->sisa + $vendor['pinjaman'];
        } else {
            $vendor['sisa'] = $vendor['pinjaman'];
        }

        $bio = Vendor::find($vendorId);

        $totalMobil = Vehicle::where('vendor_id', $vendorId)->whereNot('status', 'nonaktif')->count();

        $sisa = KasVendor::where('vendor_id', $vehicle->vendor_id)->latest()->orderBy('id', 'desc')->first()->sisa ?? 0;

        $plafon = ($bio->plafon_lain * $totalMobil) + ($bio->plafon_titipan * $totalMobil) - $sisa;

        if ($plafon < ($vendor['pinjaman'] + $data['jasa'])) {
            return redirect()->back()->with('error', 'Nilai melebihi plafon vendor!!');
        }

        $simpan = KasVendor::create($vendor);

        if ($data['jasa'] > 0) {
            $jasa['vendor_id'] = $vendorId;
            $jasa['bbm_storing_id'] = $request->storing_id;
            $jasa['vehicle_id'] = $request->id;
            $jasa['tanggal'] = date('Y-m-d');
            $jasa['uraian'] = 'Jasa Mekanik '.$vehicle->nomor_lambung;
            $jasa['storing'] = 0;
            $jasa['jasa'] = 1;
            $jasa['pinjaman'] = $data['jasa'];
            $jasa['sisa'] = $simpan->sisa + $data['jasa'];

            KasVendor::create($jasa);
        }

        $kasArray['tanggal'] = date('Y-m-d');
        $kasArray['uraian'] = 'BBM Storing '. $vehicle->nomor_lambung;
        $kasArray['jenis_transaksi_id'] = 2;
        $kasArray['nominal_transaksi'] = $storing->biaya_mekanik;
        $kasArray['transfer_ke'] = $rekening->nama_rekening;
        $kasArray['bank'] = $rekening->nama_bank;
        $kasArray['no_rekening'] = $rekening->nomor_rekening;

        $kasBesar = KasBesar::latest()->first();

        if ($kasBesar) {
            $kasArray['saldo'] = $kasBesar->saldo - $kasArray['nominal_transaksi'];
            $kasArray['modal_investor_terakhir'] = $kasBesar->modal_investor_terakhir;
        } else {
            $kasArray['saldo'] = $kasArray['nominal_transaksi'];
            $kasArray['modal_investor_terakhir'] = $kasBesar->modal_investor_terakhir;
        }

        $store = KasBesar::create($kasArray);

        $group = GroupWa::where('untuk', 'kas-besar')->first();

        $pesan =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Form BBM Storing*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    "No. Lambung : ".$vehicle->nomor_lambung."\n".
                    "Vendor : ".$vehicle->vendor->nama."\n\n".
                    "Lokasi : ".$storing->km."\n".
                    "Nilai :  *Rp. ".number_format($kasArray['nominal_transaksi'], 0, ',', '.')."*\n\n".
                    "Ditransfer ke rek:\n\n".
                    "Bank     : ".$kasArray['bank']."\n".
                    "Nama    : ".$kasArray['transfer_ke']."\n".
                    "No. Rek : ".$kasArray['no_rekening']."\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Besar : \n".
                    "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                    "Total Modal Investor : \n".
                    "Rp. ".number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
                    "Jasa Mekanik : \n".
                    "Rp. ".number_format($data['jasa'], 0, ',', '.')."\n\n".
                    "Terima kasih 🙏🙏🙏\n";
        $send = new StarSender($group->nama_group, $pesan);
        $res = $send->sendGroup();

        return redirect()->route('billing.index')->with('success', 'Data berhasil disimpan');

    }

    public function get_vendor(Request $request)
    {
        $vehicle = Vehicle::find($request->id);

        $vendor = $vehicle->vendor;

        return response()->json($vendor);
    }

    public function get_storing(Request $request)
    {
        $storing = BbmStoring::find($request->id);

        return response()->json($storing);
    }

    public function get_status_so(Request $request)
    {
        $data = Vehicle::find($request->id);
        $so = $data->vendor->support_operational;

        return response()->json($so);
    }

    public function void()
    {
        $vehicle = Vehicle::all();
        $storing = BbmStoring::all();

        return view('billing.storing.void', [
            'vehicle' => $vehicle,
            'storing' => $storing,
        ]);
    }

    public function void_store(Request $request)
    {
        $data = $request->validate([
                    'total' => 'required',
                    'mekanik' => 'required',
                    'id' => 'required',
                    'vendor_id' => 'required',
                ]);
    }

    public function storing_latest(Request $request)
    {
        $vendorId = $request->vendor_id;
        $vehicleId = $request->vehicle_id;

        $storing = KasVendor::where('vendor_id', $vendorId)
                            ->where('vehicle_id', $vehicleId)
                            ->where('storing', 1)
                            ->latest()->orderBy('id', 'desc')->first();
        $data = [
            'lokasi' => $storing->bbm_storing->km,
            'biaya_mekanik' => $storing->bbm_storing->biaya_mekanik,
            'total' => $storing->pinjaman,
        ];
        return response()->json($data);
    }
}
