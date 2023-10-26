<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\KasBon;
use App\Models\KasBesar;
use App\Models\GroupWa;
use App\Services\StarSender;
use Illuminate\Http\Request;

class FormKasbonController extends Controller
{
    public function index()
    {
        $karyawan = Karyawan::all();

        return view('billing.kasbon.index', [
            'karyawan' => $karyawan,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'karyawan_id' => 'required',
            'nominal' => 'required',
        ]);

        $data['nominal'] = str_replace('.', '', $data['nominal']);
        $data['tanggal'] = date('Y-m-d');

        $kasbon = KasBon::where('karyawan_id', $data['karyawan_id'])->where('lunas', 0)->sum('nominal');

        $karyawan = Karyawan::find($data['karyawan_id']);

        if ($karyawan->gaji_pokok < $data['nominal'] || $karyawan->gaji_pokok < ($kasbon+$data['nominal'])) {
            return redirect()->route('billing.kasbon.index')->with('error', 'Kasbon sudah melebihi gaji pokok!!');
        }

        $last = KasBesar::latest()->first();

        if ($last == null || $last->saldo < $data['nominal']) {
            return redirect()->back()->with('error', 'Saldo Kas Besar tidak mencukupi');
        }

        $kas['tanggal'] = $data['tanggal'];
        $kas['uraian'] = "Kasbon ".$karyawan->nama;
        $kas['jenis_transaksi_id'] = 2;
        $kas['nominal_transaksi'] = $data['nominal'];
        $kas['saldo'] = $last->saldo - $data['nominal'];
        $kas['modal_investor_terakhir'] = $last->modal_investor_terakhir;
        $kas['transfer_ke'] = $karyawan->nama_rekening;
        $kas['bank'] = $karyawan->bank;
        $kas['no_rekening'] = $karyawan->no_rekening;

        KasBon::create($data);

        $store = KasBesar::create($kas);

        $group = GroupWa::where('untuk', 'kas-besar')->first();

        $pesan =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Form Kasbon*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    "Nama : ".$karyawan->nama."\n".
                    "Nominal :  *Rp. ".number_format($kas['nominal_transaksi'], 0, ',', '.')."*\n\n".
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


        return redirect()->route('billing.index')->with('success', 'Kasbon berhasil ditambahkan');
    }
}
