<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class StatistikController extends Controller
{
    public function index()
    {
        return view('rekap.statistik.index');
    }

    public function perform_unit(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $offset = $request->offset ?? 0;
        // nama bulan dalam indonesia berdasarkan $bulan
        $nama_bulan = Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;

        // get array list date vrom $bulan
        $date = Carbon::createFromDate($tahun, $bulan)->daysInMonth;

        $data = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->join('vehicles as v', 'v.id', 'kuj.vehicle_id')
                            ->join('rutes as r', 'r.id', 'kuj.rute_id')
                            ->select('transaksis.*', 'kuj.tanggal as tanggal', 'v.nomor_lambung as nomor_lambung', 'r.jarak as jarak')
                            ->whereMonth('tanggal', $bulan)
                            ->whereYear('tanggal', $tahun)
                            ->where('transaksis.void', 0)
                            ->get();

        $dataTahun = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->selectRaw('YEAR(tanggal) tahun')
                            ->groupBy('tahun')
                            ->get();


        $vehicle = Vehicle::orderBy('nomor_lambung')
                    ->limit(10)
                    ->offset($offset)
                    ->get();
        if ($vehicle->count() == 0) {
            $offset = 0;
            $vehicle = Vehicle::orderBy('nomor_lambung')
                    ->limit(10)
                    ->offset($offset)
                    ->get();
        }
        return view('rekap.statistik.perform-unit', [
            'data' => $data,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'bulan_angka' => $bulan,
            'vehicle' => $vehicle,
            'nama_bulan' => $nama_bulan,
            'date' => $date,
            'offset' => $offset,
            'dataTahun' => $dataTahun,
        ]);
    }

    public function profit_bulanan(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $offset = $request->offset ?? 0;
        // nama bulan dalam indonesia berdasarkan $bulan
        $nama_bulan = Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;

        // get array list date vrom $bulan
        $date = Carbon::createFromDate($tahun, $bulan)->daysInMonth;

        $data = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->join('vehicles as v', 'v.id', 'kuj.vehicle_id')
                            ->select('transaksis.*', 'kuj.tanggal as tanggal', 'v.nomor_lambung as nomor_lambung')
                            ->whereMonth('tanggal', $bulan)
                            ->whereYear('tanggal', $tahun)
                            ->where('transaksis.void', 0)
                            ->get();

        $dataTahun = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->selectRaw('YEAR(tanggal) tahun')
                            ->groupBy('tahun')
                            ->get();


        $vehicle = Vehicle::orderBy('nomor_lambung')
                    ->limit(10)
                    ->offset($offset)
                    ->get();
        if ($vehicle->count() == 0) {
            $offset = 0;
            $vehicle = Vehicle::orderBy('nomor_lambung')
                    ->limit(10)
                    ->offset($offset)
                    ->get();
        }
        return view('rekap.statistik.profit-bulanan', [
            'data' => $data,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'bulan_angka' => $bulan,
            'vehicle' => $vehicle,
            'nama_bulan' => $nama_bulan,
            'date' => $date,
            'offset' => $offset,
            'dataTahun' => $dataTahun,
        ]);
    }

    public function profit_bulanan_print(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $offset = $request->offset ?? 0;
        // nama bulan dalam indonesia berdasarkan $bulan
        $nama_bulan = Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;

        // get array list date vrom $bulan
        $date = Carbon::createFromDate($tahun, $bulan)->daysInMonth;

        $data = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->join('vehicles as v', 'v.id', 'kuj.vehicle_id')
                            ->select('transaksis.*', 'kuj.tanggal as tanggal', 'v.nomor_lambung as nomor_lambung')
                            ->whereMonth('tanggal', $bulan)
                            ->whereYear('tanggal', $tahun)
                            ->where('transaksis.void', 0)
                            ->get();


        $vehicle = Vehicle::orderBy('nomor_lambung')
                    ->limit(10)
                    ->offset($offset)
                    ->get();

        if ($vehicle->count() == 0) {
            $offset = 0;
            $vehicle = Vehicle::orderBy('nomor_lambung')
                    ->limit(10)
                    ->offset($offset)
                    ->get();
        }

        $pdf = PDF::loadview('rekap.statistik.profit-bulanan-print', [
            'data' => $data,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'bulan_angka' => $bulan,
            'vehicle' => $vehicle,
            'nama_bulan' => $nama_bulan,
            'date' => $date,
            'offset' => $offset,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Profit Bulan '.$nama_bulan.' '.$tahun.'.pdf');
    }

    public function profit_tahunan(Request $request)
    {

        $tahun = $request->tahun ?? date('Y');
        $offset = $request->offset ?? 0;

        // get array list month from $tahun (1-12) with format m and M
        $nama_bulan = [];
        for ($i = 1; $i <= 12; $i++) {
            $nama_bulan[$i] = Carbon::createFromDate($tahun, $i)->locale('id')->monthName;
        }


        $data = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->join('vehicles as v', 'v.id', 'kuj.vehicle_id')
                            ->select('transaksis.*', 'kuj.tanggal as tanggal', 'v.nomor_lambung as nomor_lambung')
                            ->whereYear('tanggal', $tahun)
                            ->where('transaksis.void', 0)
                            ->get();

        $dataTahun = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->selectRaw('YEAR(tanggal) tahun')
                            ->groupBy('tahun')
                            ->get();

        // dd($data);
        $vehicle = Vehicle::orderBy('nomor_lambung')
                    ->limit(10)
                    ->offset($offset)
                    ->get();

        if ($vehicle->count() == 0) {
            $offset = 0;
            $vehicle = Vehicle::orderBy('nomor_lambung')
                    ->limit(10)
                    ->offset($offset)
                    ->get();
        }

        return view('rekap.statistik.profit-tahunan', [
            'data' => $data,
            'tahun' => $tahun,
            'vehicle' => $vehicle,
            'nama_bulan' => $nama_bulan,
            'offset' => $offset,
            'dataTahun' => $dataTahun,
        ]);
    }
}
