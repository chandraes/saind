<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Vehicle;
use App\Models\Vendor;
use App\Models\Customer;
use App\Models\KasVendor;
use App\Models\InvoiceTagihan;
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

        $grand_total_tonase = $data->reduce(function ($carry, $transaction) {
                            $tonase = $transaction->timbangan_bongkar ?? 0;
                            return $carry + $tonase;
                        }, 0);

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
                ->offset(0)
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

                $total_tonase = 0; // reset total tonase for each vehicle

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

                        $tonase = $transaction->timbangan_bongkar ?? 0;
                        $total_tonase += $tonase; // add tonase to total

                        $rutes[] = $rute;
                        $tonases[] = $tonase;
                    }

                    $statistics[$v->nomor_lambung]['data'][] = [
                        'day' => $i,
                        'rute' => implode(",", $rutes),
                        'tonase' => implode(",", $tonases),
                    ];
                }

                $statistics[$v->nomor_lambung]['total_tonase'] = $total_tonase; // store total tonase for each vehicle
            }
        }



        foreach ($statistics as $nomor_lambung => $statistic) {
            $total_tonase = array_reduce($statistic['data'], function ($carry, $item) {
                if (strpos($item['tonase'], ',') !== false) {
                    $tonases = explode(',', $item['tonase']);
                    $tonase_sum = array_sum(array_map('floatval', $tonases));
                } else {
                    $tonase_sum = is_numeric($item['tonase']) ? $item['tonase'] : 0;
                }

                return $carry + $tonase_sum;
            }, 0);

            $statistics[$nomor_lambung]['total_tonase'] = $total_tonase;
        }
        // dd($statistics);
        $vendors = Vendor::all();


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
            'grand_total_tonase' => $grand_total_tonase,
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

        $grand_total_tonase = $data->reduce(function ($carry, $transaction) {
                                    $tonase = $transaction->timbangan_bongkar ?? 0;
                                    return $carry + $tonase;
                                }, 0);

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

                $total_tonase = 0; // reset total tonase for each vehicle

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

                        $tonase = $transaction->timbangan_bongkar ?? 0;
                        $total_tonase += $tonase; // add tonase to total

                        $rutes[] = $rute;
                        $tonases[] = $tonase;
                    }

                    $statistics[$v->nomor_lambung]['data'][] = [
                        'day' => $i,
                        'rute' => implode(",", $rutes),
                        'tonase' => implode(",", $tonases),
                    ];
                }

                $statistics[$v->nomor_lambung]['total_tonase'] = $total_tonase; // store total tonase for each vehicle
            }
        }



        foreach ($statistics as $nomor_lambung => $statistic) {
            $total_tonase = array_reduce($statistic['data'], function ($carry, $item) {
                if (strpos($item['tonase'], ',') !== false) {
                    $tonases = explode(',', $item['tonase']);
                    $tonase_sum = array_sum(array_map('floatval', $tonases));
                } else {
                    $tonase_sum = is_numeric($item['tonase']) ? $item['tonase'] : 0;
                }

                return $carry + $tonase_sum;
            }, 0);

            $statistics[$nomor_lambung]['total_tonase'] = $total_tonase;
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
            'grand_total_tonase' => $grand_total_tonase,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Perform Unit Bulan '.$nama_bulan.' '.$tahun.'.pdf');
    }

    public function profit_bulanan(Request $request)
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
                            ->select('transaksis.*', 'kuj.tanggal as tanggal', 'v.nomor_lambung as nomor_lambung')
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

        $vendors = Vendor::all();

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
            'vendors' => $vendors,
            'vendor' => $vendor,
        ]);
    }

    public function profit_bulanan_print(Request $request)
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
                            ->select('transaksis.*', 'kuj.tanggal as tanggal', 'v.nomor_lambung as nomor_lambung')
                            ->whereMonth('tanggal', $bulan)
                            ->whereYear('tanggal', $tahun)
                            ->where('transaksis.void', 0)
                            ->when($vendor, function ($query, $vendor) {
                                return $query->where('v.vendor_id', $vendor);
                            })
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

    public function perform_unit_pervendor(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $offset = $request->offset ?? 0;

        $vendor = auth()->user()->vendor_id;
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

        $grand_total_tonase = $data->reduce(function ($carry, $transaction) {
                                $tonase = $transaction->timbangan_bongkar ?? 0;
                                return $carry + $tonase;
                            }, 0);

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

                $total_tonase = 0; // reset total tonase for each vehicle

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

                        $tonase = $transaction->timbangan_bongkar ?? 0;
                        $total_tonase += $tonase; // add tonase to total

                        $rutes[] = $rute;
                        $tonases[] = $tonase;
                    }

                    $statistics[$v->nomor_lambung]['data'][] = [
                        'day' => $i,
                        'rute' => implode(",", $rutes),
                        'tonase' => implode(",", $tonases),
                    ];
                }

                $statistics[$v->nomor_lambung]['total_tonase'] = $total_tonase; // store total tonase for each vehicle
            }
        }



        foreach ($statistics as $nomor_lambung => $statistic) {
            $total_tonase = array_reduce($statistic['data'], function ($carry, $item) {
                if (strpos($item['tonase'], ',') !== false) {
                    $tonases = explode(',', $item['tonase']);
                    $tonase_sum = array_sum(array_map('floatval', $tonases));
                } else {
                    $tonase_sum = is_numeric($item['tonase']) ? $item['tonase'] : 0;
                }

                return $carry + $tonase_sum;
            }, 0);

            $statistics[$nomor_lambung]['total_tonase'] = $total_tonase;
        }

        $vendors = Vendor::find($vendor);

        // dd($statistics);
        return view('rekap.statistik.perform-unit-pervendor', [
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
            'grand_total_tonase' => $grand_total_tonase,
        ]);
    }

    public function perform_vendor(Request $request)
    {
        $vendors = Vendor::all();

        $sum_nominal_bayar = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                                        ->where('transaksis.bayar', 0)->where('transaksis.void', 0)->where('transaksis.status', 3)
                                        ->groupBy('kuj.vendor_id')
                                        ->selectRaw('kuj.vendor_id, sum(nominal_bayar) as total_nominal_bayar, sum(kuj.nominal_transaksi) as total_kas_uang_jalan')
                                        ->get()
                                        ->keyBy('vendor_id');
        $statistics = [];

        foreach ($vendors as $v) {
            $nominal_uang_jalan =  $sum_nominal_bayar[$v->id]->total_kas_uang_jalan ?? 0;
            $nominal_bayar = $sum_nominal_bayar[$v->id]->total_nominal_bayar  ?? 0;
            $sisa = KasVendor::where('vendor_id', $v->id)->latest()->orderBy('id', 'desc')->first()->sisa ?? 0;
            $total_bayar = $nominal_bayar - $nominal_uang_jalan;

            $statistics[$v->nickname] = [
                'vendor' => $v,
                'total_nominal_bayar' => $total_bayar,
                'total_sisa' => $sisa,
                'total' => $sisa-$total_bayar,
            ];
        }


        return view('rekap.statistik.perform-vendor', [
            'vendors' => $vendors,
            'statistics' => $statistics,
        ]);

    }

    public function perform_vendor_print(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        // nama bulan dalam indonesia berdasarkan $bulan
        $nama_bulan = Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;

        // get array list date vrom $bulan
        $date = Carbon::createFromDate($tahun, $bulan)->daysInMonth;

        // get data all vendor
        $vendors = Vendor::all();

        $dataTahun = KasVendor::selectRaw('YEAR(tanggal) tahun')
                            ->groupBy('tahun')
                            ->get();

        $grand_total = 0;

        foreach ($vendors as $v) {
            $sisa = KasVendor::where('vendor_id', $v->id)->latest()->orderBy('id', 'desc')->first()->sisa ?? 0;
            $grand_total += $sisa;
        }

        $statistics = [];

        foreach ($vendors as $v) {
            $statistics[$v->nickname] = [
                'vendor_id' => $v->id,
                'vendor' => $v,
            ];
        }

        $kasVendors = KasVendor::whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->orderBy('id', 'desc')
            ->get();

        // Group the records by vendor and day
        $groupedKasVendors = $kasVendors->groupBy([
            'vendor_id',
            function ($kasVendor) {
                return Carbon::parse($kasVendor->tanggal)->day;
            },
        ]);

        // Calculate 'sisa' value for each day for each vendor
        foreach ($statistics as $vendor_name => $statistic) {
            for ($day = 1; $day <= $date; $day++) {
                if (isset($groupedKasVendors[$statistic['vendor_id']]) && isset($groupedKasVendors[$statistic['vendor_id']][$day])) {
                    $sisa = $groupedKasVendors[$statistic['vendor_id']][$day]->first()->sisa ?? '-';
                } else {
                    $sisa = '-';
                }
                $statistics[$vendor_name]['sisa'][$day] = $sisa;
            }
        }

        $pdf = PDF::loadview('rekap.statistik.perform-vendor-print', [
            'statistics' => $statistics,
            'date' => $date,
            'grand_total' => $grand_total,
            'tahun' => $tahun,
            'dataTahun' => $dataTahun,
            'nama_bulan' => $nama_bulan,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Perform Vendor '.$nama_bulan.' '.$tahun.'.pdf');
    }

    public function statistik_customer()
    {
        $customers = Customer::all();

        $sum_nominal_tagihan = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                                        ->where('transaksis.tagihan', 0)->where('transaksis.void', 0)->where('transaksis.status', 3)
                                        ->groupBy('kuj.customer_id')
                                        ->selectRaw('kuj.customer_id, sum(nominal_tagihan) as total_nominal_tagihan')
                                        ->get()
                                        ->keyBy('customer_id');

        $sum_sisa_tagihan = InvoiceTagihan::groupBy('customer_id')
                                        ->where('lunas', 0)
                                        ->selectRaw('customer_id, sum(sisa_tagihan) as total_sisa_tagihan')
                                        ->get()
                                        ->keyBy('customer_id');
        $statistics = [];

        foreach ($customers as $c) {
            $nominal_tagihan = $sum_nominal_tagihan[$c->id]->total_nominal_tagihan ?? 0;
            $sisa_tagihan = $sum_sisa_tagihan[$c->id]->total_sisa_tagihan ?? 0;

            $statistics[$c->singkatan] = [
                'customer' => $c,
                'total_nominal_tagihan' => $nominal_tagihan,
                'total_sisa_tagihan' => $sisa_tagihan,
                'total' => $nominal_tagihan + $sisa_tagihan,
            ];
        }


        return view('rekap.statistik.statistik-customer', [
            'customers' => $customers,
            'statistics' => $statistics,
        ]);
    }

    public function statistik_pervendor()
    {
        $vendorId = auth()->user()->vendor_id;

        $sums = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                        ->where('kuj.vendor_id', $vendorId)
                        ->where('transaksis.bayar', 0)
                        ->where('transaksis.void', 0)
                        ->where('transaksis.status', 3)
                        ->selectRaw('sum(transaksis.nominal_bayar) as total_nominal_bayar, sum(kuj.nominal_transaksi) as total_uang_jalan')
                        ->first();

        $latest_sisa = KasVendor::where('vendor_id', $vendorId)
                                ->latest()
                                ->orderBy('id', 'desc')
                                ->first()
                                ->sisa ?? 0;
        $total_bayar = $sums->total_nominal_bayar - $sums->total_uang_jalan;
        $statistics = [
            'total_nominal_bayar' => $total_bayar,
            'latest_sisa' => $latest_sisa,
        ];

        return view('rekap.statistik-pervendor', [
            'statistics' => $statistics,
        ]);

    }
}
