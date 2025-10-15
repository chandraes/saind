<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\KasBesar;
use App\Models\Transaksi;
use Illuminate\Http\Request;

class PerInvestorController extends Controller
{
    public function kas_besar(Request $request)
    {
         // kas besar perbulan dan tahun, jika tidak ada request maka default bulan dan tahun saat ini
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $dataTahun = KasBesar::selectRaw('YEAR(tanggal) tahun')->groupBy('tahun')->get();

        $data = KasBesar::with('jenis_transaksi')->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->get();

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = \Carbon\Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = \Carbon\Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;
        // get latest data from month before current month
        $dataSebelumnya = KasBesar::whereMonth('tanggal', $bulanSebelumnya)->whereYear('tanggal', $tahunSebelumnya)->latest()->orderBy('id', 'desc')->first();
        // dd($bulan);
        return view('per-investor.kas-besar', [
            'data' => $data,
            'dataTahun' => $dataTahun,
            'dataSebelumnya' => $dataSebelumnya,
            'stringBulan' => $stringBulan,
            'tahun' => $tahun,
            'tahunSebelumnya' => $tahunSebelumnya,
            'bulan' => $bulan,
            'stringBulanNow' => $stringBulanNow,
        ]);
    }

    public function tagihan_invoice()
    {
        $db = new Customer();
        $data = $db->tagihanInvoice();

        return view('per-investor.tagihan-invoice', [
            'data' => $data,
        ]);
    }

    public function profit_harian(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $offset = $request->offset ?? 0;

        $db = new Transaksi();

        $all = $db->profitHarian($bulan, $tahun, $offset);

        return view('per-investor.profit-harian', $all);
    }

    public function profit_bulanan(Request $request)
    {
        $tahun = $request->tahun ?? date('Y');

        $db = new Transaksi();

        $all = $db->profitBulanan($tahun);

        return view('per-investor.profit-bulanan', $all);
    }
}
