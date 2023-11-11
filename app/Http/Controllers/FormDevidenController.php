<?php

namespace App\Http\Controllers;

use App\Models\KasBesar;
use App\Models\PemegangSaham;
use App\Models\PersentaseAwal;
use App\Models\Rekening;
use App\Models\GroupWa;
use Illuminate\Http\Request;
use App\Services\StarSender;
use Carbon\Carbon;

class FormDevidenController extends Controller
{
    public function index()
    {
        // carbon month now month name in indonesian
        $persen = PersentaseAwal::all();

        if($persen->sum('persentase') != 100){
            return redirect()->route('billing.index')->with('error', 'Persentase awal belum 100%');
        }

        $pemegangSaham = PemegangSaham::all();

        foreach ($persen as $s) {
            if ($pemegangSaham->where('persentase_awal_id', $s->id)->sum('persentase') != 100) {
                return redirect()->route('billing.index')->with('error', 'Persentase pemegang saham belum 100%');
            }
        }

        return view('billing.deviden.index', [
            'persen' => $persen,
            'data' => $pemegangSaham
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nominal_transaksi' => 'required',
        ]);

        $data['nominal_transaksi'] = str_replace('.', '', $data['nominal_transaksi']);

        $last = KasBesar::latest()->first();

        if ($last == null || $last->saldo < $data['nominal_transaksi']) {
            return redirect()->route('billing.deviden.index')->with('error', 'Saldo tidak cukup');
        }

        $persentase = PersentaseAwal::all();
        $group = GroupWa::where('untuk', 'kas-besar')->first();
        $month = Carbon::now()->locale('id')->monthName;

        $isiPesan = [];

        foreach ($persentase as $persen) {

            $nilai = $data['nominal_transaksi'] * $persen->persentase / 100;

            foreach ($persen->pemegang_saham as $v) {

                usleep(50000);

                $last2 = KasBesar::latest()->orderBy('id', 'desc')->first();

                $nilai2 = $nilai * $v->persentase / 100;

                $k['tanggal'] = date('Y-m-d');
                $k['jenis_transaksi_id'] = 2;
                $k['uraian'] = "Bagi Deviden ".$v->nama;
                $k['nominal_transaksi'] = $nilai2;
                $k['saldo'] = $last2->saldo - $nilai2;
                $k['transfer_ke'] = substr($v->nama_rekening, 0, 15);
                $k['bank'] = $v->bank;
                $k['no_rekening'] = $v->nomor_rekening;
                $k['modal_investor_terakhir'] = $last2->modal_investor_terakhir;

                $store = KasBesar::create($k);

                $pesan =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                            "*Form Deviden ".$month."*\n".
                            "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                            "Nama  : ".$v->nama."\n".
                            "Nilai :  *Rp. ".number_format($k['nominal_transaksi'], 0, ',', '.')."*\n\n".
                            "Ditransfer ke rek:\n\n".
                            "Bank      : ".$k['bank']."\n".
                            "Nama    : ".$k['transfer_ke']."\n".
                            "No. Rek : ".$k['no_rekening']."\n\n".
                            "==========================\n".
                            "Sisa Saldo Kas Besar : \n".
                            "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                            "Total Modal Investor : \n".
                            "Rp. ".number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
                            "Terima kasih 🙏🙏🙏\n";


                array_push($isiPesan, $pesan);

            }
        }

        // looping $isiPesan
        foreach ($isiPesan as $pesan) {

            $send = new StarSender($group->nama_group, $pesan);
            $res = $send->sendGroup();

        }

        return redirect()->route('billing.index')->with('success', 'Data berhasil disimpan');
    }
}
