<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Vehicle;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class StatistikController extends Controller
{
    public function index()
    {
        return view('rekap.statistik.index');
    }

    public function perform_unit_tahunan(Request $request)
    {
        $tahun = $request->tahun ?? date('Y');

        $vehicle = Vehicle::orderBy('nomor_lambung')->get();

        $dataTahun = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->selectRaw('YEAR(tanggal) tahun')
                            ->groupBy('tahun')
                            ->get();

        $statistics = [];

        foreach ($vehicle as $v) {
            $statistics[$v->nomor_lambung] = [
                'vehicle' => $v,
                'monthly' => array_fill(1, 12, ['long_route_count' => 0, 'short_route_count' => 0]),
            ];
        }

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $data = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                                ->join('vehicles as v', 'v.id', 'kuj.vehicle_id')
                                ->join('rutes as r', 'r.id', 'kuj.rute_id')
                                ->select('transaksis.*', 'kuj.tanggal as tanggal', 'v.nomor_lambung as nomor_lambung', 'r.jarak as jarak')
                                ->whereMonth('tanggal', $bulan)
                                ->whereYear('tanggal', $tahun)
                                ->where('transaksis.void', 0)
                                ->get();

            foreach ($data as $transaction) {
                $v = $transaction->kas_uang_jalan->vehicle;

                $jarak = $transaction->jarak ?? 0;

                if (!isset($statistics[$v->nomor_lambung])) {
                    continue;
                }

                if ($jarak > 50) {
                    $statistics[$v->nomor_lambung]['monthly'][$bulan]['long_route_count']++;
                } else if ($jarak > 0 && $jarak <= 50) {
                    $statistics[$v->nomor_lambung]['monthly'][$bulan]['short_route_count']++;
                }
            }
        }

        return view('rekap.statistik.perform-unit-tahunan', [
            'statistics' => $statistics,
            'tahun' => $tahun,
            'vehicle' => $vehicle,
            'dataTahun' => $dataTahun,
        ]);
    }

    public function perform_unit_tahunan_print(Request $request)
    {
        $tahun = $request->tahun ?? date('Y');

        $vehicle = Vehicle::orderBy('nomor_lambung')->get();

        $dataTahun = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->selectRaw('YEAR(tanggal) tahun')
                            ->groupBy('tahun')
                            ->get();

        $statistics = [];

        foreach ($vehicle as $v) {
            $statistics[$v->nomor_lambung] = [
                'vehicle' => $v,
                'monthly' => array_fill(1, 12, ['long_route_count' => 0, 'short_route_count' => 0]),
            ];
        }

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $data = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                                ->join('vehicles as v', 'v.id', 'kuj.vehicle_id')
                                ->join('rutes as r', 'r.id', 'kuj.rute_id')
                                ->select('transaksis.*', 'kuj.tanggal as tanggal', 'v.nomor_lambung as nomor_lambung', 'r.jarak as jarak')
                                ->whereMonth('tanggal', $bulan)
                                ->whereYear('tanggal', $tahun)
                                ->where('transaksis.void', 0)
                                ->get();

            foreach ($data as $transaction) {
                $v = $transaction->kas_uang_jalan->vehicle;

                $jarak = $transaction->jarak ?? 0;

                if (!isset($statistics[$v->nomor_lambung])) {
                    continue;
                }

                if ($jarak > 50) {
                    $statistics[$v->nomor_lambung]['monthly'][$bulan]['long_route_count']++;
                } else if ($jarak > 0 && $jarak <= 50) {
                    $statistics[$v->nomor_lambung]['monthly'][$bulan]['short_route_count']++;
                }
            }
        }

        $pdf = PDF::loadview('rekap.statistik.perform-unit-tahunan-print', [
            'statistics' => $statistics,
            'tahun' => $tahun,
            'vehicle' => $vehicle,
            'dataTahun' => $dataTahun,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Perform Unit Bulan '.$tahun.'.pdf');

    }

    public function perform_unit(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $offset = $request->offset ?? 0;

        $vendor = $request->vendor ?? 0;
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
                            ->when($vendor, function ($query, $vendor) {
                                return $query->where('v.vendor_id', $vendor);
                            })
                            ->get();

        $dataTahun = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->selectRaw('YEAR(tanggal) tahun')
                            ->groupBy('tahun')
                            ->get();


        $vehicle = Vehicle::orderBy('nomor_lambung')
                    ->when($vendor, function ($query, $vendor) {
                        return $query->where('vendor_id', $vendor);
                    })
                    ->limit(10)
                    ->offset($offset)
                    ->get();

        if ($vehicle->count() == 0) {
            $offset = 0;
            $vehicle = Vehicle::orderBy('nomor_lambung')
                        ->when($vendor, function ($query, $vendor) {
                            return $query->where('vendor_id', $vendor);
                        })
                        ->limit(10)
                        ->offset($offset)
                        ->get();
        }

        $statistics = [];

        foreach ($vehicle as $v) {
            $statistics[$v->nomor_lambung] = [
                'vehicle' => $v,
                'long_route_count' => 0,
                'short_route_count' => 0,
            ];
        }

        for ($i = 1; $i <= $date; $i++) {
            foreach ($vehicle as $v) {
                $dateString = date('Y-m-d', strtotime($i.'-'.$bulan.'-'.$tahun));

                $transactions = $data->filter(function ($transaction) use ($v, $dateString) {
                    return $transaction->nomor_lambung == $v->nomor_lambung && $transaction->tanggal == $dateString && $transaction->void == 0;
                });

                if ($transactions->isEmpty()) {
                    $statistics[$v->nomor_lambung]['data'][] = [
                        'day' => $i,
                        'rute' => '-',
                        'tonase' => '-',
                    ];
                } else {
                    $rutes = [];
                    $tonases = [];

                    foreach ($transactions as $transaction) {
                        $rute = $transaction->kas_uang_jalan->rute->nama ?? '-';
                        $jarak = $transaction->jarak ?? 0;

                        if ($jarak > 50) {
                            $statistics[$v->nomor_lambung]['long_route_count']++;
                        } else if ($jarak > 0 && $jarak <= 50) {
                            $statistics[$v->nomor_lambung]['short_route_count']++;
                        }

                        $tonase = $transaction->timbangan_bongkar ?? "-";

                        $rutes[] = $rute;
                        $tonases[] = $tonase;
                    }

                    $statistics[$v->nomor_lambung]['data'][] = [
                        'day' => $i,
                        'rute' => implode(",", $rutes),
                        'tonase' => implode(",", $tonases),
                    ];
                }
            }
        }

        $vendors = Vendor::all();

        // dd($statistics);
        return view('rekap.statistik.perform-unit', [
            // 'data' => $data,
            'statistics' => $statistics,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'vendor' => $vendor,
            'bulan_angka' => $bulan,
            'vehicle' => $vehicle,
            'nama_bulan' => $nama_bulan,
            'date' => $date,
            'vendors' => $vendors,
            'offset' => $offset,
            'dataTahun' => $dataTahun,
        ]);
    }

    public function perform_unit_print(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $offset = $request->offset ?? 0;
        $vendor = $request->vendor ?? 0;
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
                            ->when($vendor, function ($query, $vendor) {
                                return $query->where('v.vendor_id', $vendor);
                            })
                            ->get();

        $dataTahun = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->selectRaw('YEAR(tanggal) tahun')
                            ->groupBy('tahun')
                            ->get();


        $vehicle = Vehicle::orderBy('nomor_lambung')
                    ->when($vendor, function ($query, $vendor) {
                        return $query->where('vendor_id', $vendor);
                    })
                    ->limit(10)
                    ->offset($offset)
                    ->get();

        if ($vehicle->count() == 0) {
            $offset = 0;
            $vehicle = Vehicle::orderBy('nomor_lambung')
                        ->when($vendor, function ($query, $vendor) {
                            return $query->where('vendor_id', $vendor);
                        })
                        ->limit(10)
                        ->offset($offset)
                        ->get();
        }

        $statistics = [];

        foreach ($vehicle as $v) {
            $statistics[$v->nomor_lambung] = [
                'vehicle' => $v,
                'long_route_count' => 0,
                'short_route_count' => 0,
            ];
        }

        for ($i = 1; $i <= $date; $i++) {
            foreach ($vehicle as $v) {
                $dateString = date('Y-m-d', strtotime($i.'-'.$bulan.'-'.$tahun));

                $transactions = $data->filter(function ($transaction) use ($v, $dateString) {
                    return $transaction->nomor_lambung == $v->nomor_lambung && $transaction->tanggal == $dateString && $transaction->void == 0;
                });

                if ($transactions->isEmpty()) {
                    $statistics[$v->nomor_lambung]['data'][] = [
                        'day' => $i,
                        'rute' => '-',
                        'tonase' => '-',
                    ];
                } else {
                    $rutes = [];
                    $tonases = [];

                    foreach ($transactions as $transaction) {
                        $rute = $transaction->kas_uang_jalan->rute->nama ?? '-';
                        $jarak = $transaction->jarak ?? 0;

                        if ($jarak > 50) {
                            $statistics[$v->nomor_lambung]['long_route_count']++;
                        } else if ($jarak > 0 && $jarak <= 50) {
                            $statistics[$v->nomor_lambung]['short_route_count']++;
                        }

                        $tonase = $transaction->timbangan_bongkar ?? "-";

                        $rutes[] = $rute;
                        $tonases[] = $tonase;
                    }

                    $statistics[$v->nomor_lambung]['data'][] = [
                        'day' => $i,
                        'rute' => implode(",", $rutes),
                        'tonase' => implode(",", $tonases),
                    ];
                }
            }
        }

        $vendors = Vendor::all();

        $pdf = PDF::loadview('rekap.statistik.perform-unit-print', [
            // 'data' => $data,
            'statistics' => $statistics,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'vendor' => $vendor,
            'bulan_angka' => $bulan,
            'vehicle' => $vehicle,
            'nama_bulan' => $nama_bulan,
            'date' => $date,
            'vendors' => $vendors,
            'offset' => $offset,
            'dataTahun' => $dataTahun,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Perform Unit Bulan '.$nama_bulan.' '.$tahun.'.pdf');
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

        $nama_bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'May',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Augustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        // create statistics array
        $statistics = [];

        // get all vehicle
        $vehicle = Vehicle::orderBy('nomor_lambung')->get();

        $dataTahun = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->selectRaw('YEAR(tanggal) tahun')
                            ->groupBy('tahun')
                            ->get();
        // looping sum profit each vehicle for each month
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $data = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                                ->join('vehicles as v', 'v.id', 'kuj.vehicle_id')
                                ->select('transaksis.*', 'kuj.tanggal as tanggal', 'v.nomor_lambung as nomor_lambung')
                                ->whereMonth('tanggal', $bulan)
                                ->whereYear('tanggal', $tahun)
                                ->where('transaksis.void', 0)
                                ->get();

            foreach ($data as $transaction) {
                $v = $transaction->kas_uang_jalan->vehicle;
                $vendor = $transaction->kas_uang_jalan->vendor;

                if (!isset($statistics[$v->nomor_lambung])) {
                    $statistics[$v->nomor_lambung] = [
                        'vehicle' => $v,
                        'vendor' => $vendor->nama,
                        'monthly' => array_fill(1, 12, 0),
                    ];
                }

                $statistics[$v->nomor_lambung]['monthly'][$bulan] += $transaction->profit;
            }
        }

        // dd($statistics);

        uksort($statistics, function($a, $b) {
            return $a <=> $b;
        });

        return view('rekap.statistik.profit-tahunan', [
            'statistics' => $statistics,
            'tahun' => $tahun,
            'vehicle' => $vehicle,
            'offset' => $offset,
            'dataTahun' => $dataTahun,
            'nama_bulan' => $nama_bulan, // pass the variable to the view
        ]);
    }

    public function profit_tahunan_print(Request $request)
    {
        $tahun = $request->tahun ?? date('Y');
        $offset = $request->offset ?? 0;

        $nama_bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'May',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Augustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        // create statistics array
        $statistics = [];

        // get all vehicle
        $vehicle = Vehicle::orderBy('nomor_lambung')->get();

        $dataTahun = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->selectRaw('YEAR(tanggal) tahun')
                            ->groupBy('tahun')
                            ->get();
        // looping sum profit each vehicle for each month
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $data = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                                ->join('vehicles as v', 'v.id', 'kuj.vehicle_id')
                                ->select('transaksis.*', 'kuj.tanggal as tanggal', 'v.nomor_lambung as nomor_lambung')
                                ->whereMonth('tanggal', $bulan)
                                ->whereYear('tanggal', $tahun)
                                ->where('transaksis.void', 0)
                                ->get();

            foreach ($data as $transaction) {
                $v = $transaction->kas_uang_jalan->vehicle;
                $vendor = $transaction->kas_uang_jalan->vendor;

                if (!isset($statistics[$v->nomor_lambung])) {
                    $statistics[$v->nomor_lambung] = [
                        'vehicle' => $v,
                        'vendor' => $vendor->nama,
                        'monthly' => array_fill(1, 12, 0),
                    ];
                }

                $statistics[$v->nomor_lambung]['monthly'][$bulan] += $transaction->profit;
            }
        }

        uksort($statistics, function($a, $b) {
            return $a <=> $b;
        });

        $pdf = PDF::loadview('rekap.statistik.profit-tahunan-print', [
            'statistics' => $statistics,
            'tahun' => $tahun,
            'vehicle' => $vehicle,
            'offset' => $offset,
            'dataTahun' => $dataTahun,
            'nama_bulan' => $nama_bulan,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Profit Tahun '.$tahun.'.pdf');
    }
}
