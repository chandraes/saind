<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
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
        $vehicle = Vehicle::all();
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

        $last = KasVendor::where('vendor_id', $vendorId)->latest()->first();

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



        $data['tanggal'] = date('Y-m-d');
        $data['uraian'] = 'BBM Storing '. $vehicle->nomor_lambung;
        $data['jenis_transaksi_id'] = 2;
        $data['nominal_transaksi'] = $storing->biaya_mekanik;
        $data['transfer_ke'] = $rekening->nama_rekening;
        $data['bank'] = $rekening->nama_bank;
        $data['no_rekening'] = $rekening->nomor_rekening;

        $kasBesar = KasBesar::latest()->first();

        if ($kasBesar) {
            $data['saldo'] = $kasBesar->saldo - $data['nominal_transaksi'];
            $data['modal_investor_terakhir'] = $kasBesar->modal_investor_terakhir;
        } else {
            $data['saldo'] = $data['nominal_transaksi'];
            $data['modal_investor_terakhir'] = $kasBesar->modal_investor_terakhir;
        }

        $store = KasBesar::create($data);

        $group = GroupWa::where('untuk', 'kas-besar')->first();

        $pesan =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Form BBM Storing*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    "No. Lambung : ".$vehicle->nomor_lambung."\n".
                    "Vendor : ".$vehicle->vendor->nama."\n\n".
                    "Lokasi : ".$storing->km."\n".
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
                    "Jasa Mekanik : \n".
                    "Rp. ".number_format($data['jasa'], 0, ',', '.')."\n\n".
                    "Terima kasih 🙏🙏🙏\n";
        $send = new StarSender($group->nama_group, $pesan);
        $res = $send->sendGroup();

        return redirect()->route('billing.index')->with('success', 'Data berhasil disimpan');

    }

    public function get_storing(Request $request)
    {
        $storing = BbmStoring::find($request->id);

        return response()->json($storing);
    }

    public function get_status_so(Request $request)
    {
        $data = Vehicle::find($request->id);
        $so = $data->support_operational;

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
                            ->latest()->first();
        $data = [
            'lokasi' => $storing->bbm_storing->km,
            'biaya_mekanik' => $storing->bbm_storing->biaya_mekanik,
            'total' => $storing->pinjaman,
        ];
        return response()->json($data);
    }
}