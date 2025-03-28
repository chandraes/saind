<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Vehicle;
use App\Models\Vendor;
use App\Models\Customer;
use App\Models\KasVendor;
use App\Models\InvoiceTagihan;
use App\Models\KasBesar;
use App\Models\Rekap\BungaInvestor;
use App\Models\RekapGaji;
use App\Models\RekapGajiDetail;
use App\Models\Rute;
use App\Models\UpahGendong;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class StatistikController extends Controller
{
    public function index()
    {
        $data = UpahGendong::with(['vehicle'])->get();
        $vehicle = Vehicle::whereNot('status', 'nonaktif')->get();
        $customer = Customer::where('status', 1)->get();

        return view('rekap.statistik.index', [
            'data' => $data,
            'vehicle' => $vehicle,
            'customer' => $customer,
        ]);
    }

    public function upah_gendong(Request $request)
    {
        $vehicle = $request->vehicle_id;
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $tanggal_filter = $request->tanggal_filter ?? null;

        $check = Vehicle::where('id', $vehicle)->first();

        if($check == null){
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }

        $ug = UpahGendong::with(['vehicle'])
                            ->where('vehicle_id', $vehicle)
                            ->first();

        $nama_bulan = Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;

        // get array list date vrom $bulan
        $date = Carbon::createFromDate($tahun, $bulan)->daysInMonth;


        if ($tanggal_filter != null) {
            if (strpos($tanggal_filter, 'to') !== false) {
                // $tanggalFilter is a date range
                $dates = explode('to', $tanggal_filter);
                $startDate = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
                $endDate = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();

                // dd($startDate, $endDate, $filter, $tanggalFilter);
                $data = Transaksi::with(['kas_uang_jalan', 'kas_uang_jalan.vehicle', 'kas_uang_jalan.vendor', 'kas_uang_jalan.rute', 'kas_uang_jalan.customer'])
                                ->join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                                ->join('vehicles as v', 'v.id', 'kuj.vehicle_id')
                                ->join('rutes as r', 'r.id', 'kuj.rute_id')
                                ->select('transaksis.*', 'kuj.tanggal as tanggal', 'v.nomor_lambung as nomor_lambung', 'r.jarak as jarak')
                                ->where('transaksis.void', 0)
                                ->where('kuj.vehicle_id', $vehicle)
                                ->whereBetween('tanggal', [$startDate, $endDate])
                                ->orderBy('kuj.tanggal', 'asc')
                                ->get();

            } else {
                // $tanggalFilter is a single date
                $date = Carbon::createFromFormat('d-m-Y', trim($tanggal_filter));
                $data = Transaksi::with(['kas_uang_jalan', 'kas_uang_jalan.vehicle', 'kas_uang_jalan.vendor', 'kas_uang_jalan.rute', 'kas_uang_jalan.customer'])
                                ->join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                                ->join('vehicles as v', 'v.id', 'kuj.vehicle_id')
                                ->join('rutes as r', 'r.id', 'kuj.rute_id')
                                ->select('transaksis.*', 'kuj.tanggal as tanggal', 'v.nomor_lambung as nomor_lambung', 'r.jarak as jarak')
                                ->where('transaksis.void', 0)
                                ->where('kuj.vehicle_id', $vehicle)
                                ->where('tanggal', '>=', $date)
                                ->orderBy('kuj.tanggal', 'asc')
                                ->get();

            }
        } else{
            $data = Transaksi::with(['kas_uang_jalan', 'kas_uang_jalan.vehicle', 'kas_uang_jalan.vendor', 'kas_uang_jalan.rute', 'kas_uang_jalan.customer'])
                        ->join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                        ->join('vehicles as v', 'v.id', 'kuj.vehicle_id')
                        ->join('rutes as r', 'r.id', 'kuj.rute_id')
                        ->select('transaksis.*', 'kuj.tanggal as tanggal', 'v.nomor_lambung as nomor_lambung', 'r.jarak as jarak')
                        ->whereMonth('tanggal', $bulan)
                        ->whereYear('tanggal', $tahun)
                        ->where('transaksis.void', 0)
                        ->where('kuj.vehicle_id', $vehicle)
                        ->orderBy('kuj.tanggal', 'asc')
                        ->get();
        }


        // dd($data);
        $grand_total_tonase = $data->reduce(function ($carry, $transaction) {
                            $tonase = $transaction->timbangan_bongkar ?? 0;
                            return $carry + $tonase;
                        }, 0);

        $dataTahun = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->selectRaw('YEAR(tanggal) tahun')
                            ->groupBy('tahun')
                            ->get();


        return view('rekap.statistik.upah-gendong.index', [
            'data' => $data,
            'ug'    => $ug,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'bulan_angka' => $bulan,
            'vehicle' => $vehicle,
            'nama_bulan' => $nama_bulan,
            'date' => $date,
            'tanggal_filter' => $tanggal_filter,
            'dataTahun' => $dataTahun,
            'grand_total_tonase' => $grand_total_tonase,
        ]);

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
            $data = Transaksi::with(['kas_uang_jalan', 'kas_uang_jalan.vehicle', 'kas_uang_jalan.vendor', 'kas_uang_jalan.rute'])
                                ->join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
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

        $data = Transaksi::with(['kas_uang_jalan', 'kas_uang_jalan.vehicle', 'kas_uang_jalan.vendor', 'kas_uang_jalan.rute'])
                            ->join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
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


        $vehicle = Vehicle::with('vendor')->orderBy('nomor_lambung')
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

    public function profit_harian(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $offset = $request->offset ?? 0;
        // nama bulan dalam indonesia berdasarkan $bulan
        $nama_bulan = Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;

        // get array list date vrom $bulan
        $date = Carbon::createFromDate($tahun, $bulan)->daysInMonth;

        $data = Transaksi::with(['kas_uang_jalan', 'kas_uang_jalan.vendor'])
                    ->join('kas_uang_jalans as kuj', 'kuj.id', '=', 'transaksis.kas_uang_jalan_id')
                    ->join('vehicles as v', 'v.id', '=', 'kuj.vehicle_id')
                    ->selectRaw('DATE(kuj.tanggal) as tanggal, SUM(transaksis.profit) as total_nominal_profit, SUM(transaksis.nominal_tagihan) as total_nominal_tagihan, SUM(transaksis.nominal_bayar) as total_nominal_bayar, SUM(transaksis.nominal_bonus) as total_nominal_bonus,  SUM(transaksis.nominal_csr) as total_nominal_csr')
                    ->whereMonth('kuj.tanggal', $bulan)
                    ->whereYear('kuj.tanggal', $tahun)
                    ->where('transaksis.void', 0)
                    ->groupBy('tanggal')
                    ->get()
                    ->keyBy('tanggal');

        $profitHarian = [];
        $grandTotal = 0;
        $grandTotalTagihan = 0;
        $grandTotalBayar = 0;
        $grandTotalBonus = 0;
        $grandTotalCsr = 0;
        $grandTotalPenalty = 0;

        for ($i = 1; $i <= $date; $i++) {
            $tanggal = sprintf('%04d-%02d-%02d', $tahun, $bulan, $i);
            $dailyData = $data->get($tanggal);

            $tagihan = $dailyData ? $dailyData->total_nominal_tagihan * 0.98 : 0;
            $bayar = $dailyData->total_nominal_bayar ?? 0;
            $bonus = $dailyData->total_nominal_bonus ?? 0;
            $csr = $dailyData->total_nominal_csr ?? 0;
            $profit = $dailyData->total_nominal_profit ?? 0;
            $penalty = ($tagihan - $bayar - $bonus - $csr - $profit);

            $profitHarian[$tanggal] = [
                'nominal_tagihan' => $tagihan,
                'nominal_bayar' => $bayar,
                'nominal_bonus' => $bonus,
                'nominal_csr' => $csr,
                'penalty' => $penalty,
                'profit' => $profit,
            ];

            $grandTotal += $profitHarian[$tanggal]['profit'];
            $grandTotalPenalty += $profitHarian[$tanggal]['penalty'];
            $grandTotalTagihan += $profitHarian[$tanggal]['nominal_tagihan'];
            $grandTotalBayar += $profitHarian[$tanggal]['nominal_bayar'];
            $grandTotalBonus += $profitHarian[$tanggal]['nominal_bonus'];
            $grandTotalCsr += $profitHarian[$tanggal]['nominal_csr'];
        }
        // dd($profitHarian);
        $dataTahun = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->selectRaw('YEAR(tanggal) tahun')
                            ->groupBy('tahun')
                            ->get();


        return view('rekap.statistik.profit.harian-kotor', [
            'data' => $data,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'bulan_angka' => $bulan,
            'nama_bulan' => $nama_bulan,
            'date' => $date,
            'offset' => $offset,
            'dataTahun' => $dataTahun,
            'profitHarian' => $profitHarian,
            'grandTotal' => $grandTotal,
            'grandTotalTagihan' => $grandTotalTagihan,
            'grandTotalBayar' => $grandTotalBayar,
            'grandTotalBonus' => $grandTotalBonus,
            'grandTotalCsr' => $grandTotalCsr,
            'grandTotalPenalty' => $grandTotalPenalty,
        ]);
    }

    public function profit_harian_download(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $offset = $request->offset ?? 0;
        // nama bulan dalam indonesia berdasarkan $bulan
        $nama_bulan = Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;

        // get array list date vrom $bulan
        $date = Carbon::createFromDate($tahun, $bulan)->daysInMonth;

        $data = Transaksi::with(['kas_uang_jalan', 'kas_uang_jalan.vendor'])
                    ->join('kas_uang_jalans as kuj', 'kuj.id', '=', 'transaksis.kas_uang_jalan_id')
                    ->join('vehicles as v', 'v.id', '=', 'kuj.vehicle_id')
                    ->selectRaw('DATE(kuj.tanggal) as tanggal, SUM(transaksis.profit) as total_nominal_profit')
                    ->whereMonth('kuj.tanggal', $bulan)
                    ->whereYear('kuj.tanggal', $tahun)
                    ->where('transaksis.void', 0)
                    ->groupBy('tanggal')
                    ->get()
                    ->keyBy('tanggal');

        $profitHarian = [];
        $grandTotal = 0;

        for ($i = 1; $i <= $date; $i++) {
            $tanggal = sprintf('%04d-%02d-%02d', $tahun, $bulan, $i);
            $profitHarian[$tanggal] = $data->get($tanggal)->total_nominal_profit ?? 0;
            $grandTotal += $profitHarian[$tanggal];
        }

        $pdf = PDF::loadview('rekap.statistik.profit.harian-kotor-pdf', [
            'data' => $data,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'bulan_angka' => $bulan,
            'nama_bulan' => $nama_bulan,
            'date' => $date,
            'profitHarian' => $profitHarian,
            'grandTotal' => $grandTotal,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('Profit Bulan '.$nama_bulan.' '.$tahun.'.pdf');
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

        $data = Transaksi::with(['kas_uang_jalan','kas_uang_jalan.vendor'])
                            ->join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
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


        $vehicle = Vehicle::with(['vendor'])->orderBy('nomor_lambung')
                    ->when($vendor, function ($query, $vendor) {
                        return $query->where('vendor_id', $vendor);
                    })
                    ->whereNot('status', 'nonaktif')
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
                    ->whereNot('status', 'nonaktif')
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


    public function profit_tahunan_bersih(Request $request)
    {
        $tahun = $request->tahun ?? date('Y');

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

        $dataTahun = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->selectRaw('YEAR(tanggal) tahun')
                            ->groupBy('tahun')
                            ->get();
        // looping sum profit each vehicle for each month
        $grand_total_profit = 0;
        $grand_total_pengeluaran = 0;
        $grand_total_bersih = 0;
        $grant_total_co = 0;
        $grand_total_gaji = 0;
        $grand_total_kas_kecil = 0;
        $grand_total_bunga_investor = 0;
        $gt_penyesuaian = 0;
        $gt_penalty = 0;

        for ($bulan = 1; $bulan <= 12; $bulan++) {

            $data = Transaksi::with(['kas_uang_jalan', 'kas_uang_jalan.vehicle', 'kas_uang_jalan.vendor'])
                                ->join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                                ->join('vehicles as v', 'v.id', 'kuj.vehicle_id')
                                ->select('transaksis.*', 'kuj.tanggal as tanggal', 'v.nomor_lambung as nomor_lambung')
                                ->whereMonth('tanggal', $bulan)
                                ->whereYear('tanggal', $tahun)
                                ->where('transaksis.void', 0)
                                ->get();

            $invoiceData = InvoiceTagihan::whereMonth('tanggal', $bulan)
                            ->whereYear('tanggal', $tahun)
                            ->where('lunas', 1)
                            ->select(DB::raw('SUM(penyesuaian) as penyesuaian, SUM(penalty) as penalty'))
                            ->first();

            $penyesuaian = $invoiceData->penyesuaian ?? 0;
            $penalty = $invoiceData->penalty ?? 0;

            $bungaInvestor = BungaInvestor::whereMonth('created_at', $bulan)
                                ->whereYear('created_at', $tahun)
                                ->sum('nominal');

            $pengeluaran_kas_kecil = KasBesar::whereMonth('tanggal', $bulan)
                                ->whereYear('tanggal', $tahun)
                                ->whereNotNull('nomor_kode_kas_kecil')
                                ->sum('nominal_transaksi');

            $coTransactions = KasBesar::whereMonth('tanggal', $bulan)
                                ->whereYear('tanggal', $tahun)
                                ->where('cost_operational', 1)
                                ->whereIn('jenis_transaksi_id', [1, 2])
                                ->get()
                                ->groupBy('jenis_transaksi_id');

            $pengeluaran_co = $coTransactions->has(2) ? $coTransactions[2]->sum('nominal_transaksi') : 0;
            $pemasukan_co = $coTransactions->has(1) ? $coTransactions[1]->sum('nominal_transaksi') : 0;

            $total_co = $pengeluaran_co - $pemasukan_co;

            $gaji = RekapGaji::where('bulan', $bulan)
                                ->where('tahun', $tahun)
                                ->first();

            $total_gaji_bersih = $gaji ? $gaji->rekap_gaji_detail->sum('pendapatan_bersih') : 0;

            $grand_total_profit += $data->sum('profit');
            $grand_total_pengeluaran += $pengeluaran_kas_kecil+$total_gaji_bersih+$total_co+$bungaInvestor;
            $grand_total_bersih += $data->sum('profit') - ($pengeluaran_kas_kecil+$total_gaji_bersih+$total_co+$bungaInvestor+$penalty) + $penyesuaian;

            $grand_total_gaji += $total_gaji_bersih;
            $grant_total_co += $total_co;
            $grand_total_kas_kecil += $pengeluaran_kas_kecil;
            $grand_total_bunga_investor += $bungaInvestor;
            $gt_penyesuaian += $penyesuaian;
            $gt_penalty += $penalty;

            $total_pengeluaran = $pengeluaran_kas_kecil+$total_gaji_bersih+$total_co+$bungaInvestor+$penalty;

            $statistics[$bulan] = [
                'nama_bulan' => $nama_bulan[$bulan],
                'profit' => $data->sum('profit'),
                'total_gaji' => $total_gaji_bersih,
                'total_co' => $total_co,
                'kas_kecil' => $pengeluaran_kas_kecil,
                'bunga_investor' => $bungaInvestor,
                'penyesuaian' => $penyesuaian,
                'penalty' => $penalty,
                'pengeluaran' => $total_pengeluaran,
                'bersih' => ($data->sum('profit') - $total_pengeluaran) + $penyesuaian,
            ];

        }

        return view('rekap.statistik.profit.bulanan-bersih', [
            'statistics' => $statistics,
            'tahun' => $tahun,
            'dataTahun' => $dataTahun,
            'nama_bulan' => $nama_bulan,
            'grand_total_profit' => $grand_total_profit,
            'grand_total_pengeluaran' => $grand_total_pengeluaran,
            'grand_total_bersih' => $grand_total_bersih,
            'grand_total_gaji' => $grand_total_gaji,
            'grand_total_co' => $grant_total_co,
            'grand_total_kas_kecil' => $grand_total_kas_kecil,
            'grand_total_bunga_investor' => $grand_total_bunga_investor,
            'gt_penyesuaian' => $gt_penyesuaian,
            'gt_penalty' => $gt_penalty,
        ]);
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
            $data = Transaksi::with(['kas_uang_jalan', 'kas_uang_jalan.vehicle', 'kas_uang_jalan.vendor'])
                                ->join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
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

    public function statistik_vendor(Request $request)
    {
        $sum_nominal_bayar = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                                    ->where('transaksis.bayar', 0)->where('transaksis.void', 0)->where('transaksis.status', 3)
                                    ->groupBy('kuj.vendor_id')
                                    ->selectRaw('kuj.vendor_id, sum(nominal_bayar) as total_nominal_bayar, sum(kuj.nominal_transaksi) as total_kas_uang_jalan')
                                    ->get()
                                    ->keyBy('vendor_id');

        $vendors = Vendor::with(['vehicle' => function ($query) {
            $query->whereNot('status', 'nonaktif');
        },
        'kas_vendor' => function ($query) {
            $query->orderBy('id', 'desc');
        }])->withCount(['vehicle' => function ($query) {
            $query->whereNot('status', 'nonaktif');
        }])->get();

        $statistics = $vendors->mapWithKeys(function ($v) use ($sum_nominal_bayar) {
            $nominal_uang_jalan =  $sum_nominal_bayar[$v->id]->total_kas_uang_jalan ?? 0;
            $nominal_bayar = $sum_nominal_bayar[$v->id]->total_nominal_bayar  ?? 0;
            $sisa = $v->kas_vendor->first()->sisa ?? 0;
            $total_bayar = $nominal_bayar - $nominal_uang_jalan;

            return [$v->nickname => [
                'vendor' => $v,
                'total_nominal_bayar' => $total_bayar,
                'total_sisa' => $sisa,
                'total' => $sisa-$total_bayar,
                'total_vehicle' => $v->vehicle_count,
            ]];
        });

        return view('rekap.statistik.statistik-vendor', [
            'vendors' => $vendors,
            'statistics' => $statistics,
        ]);
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
            $ppn = 0;
            $pph = 0;

            $total_nominal_tagihan = $sum_nominal_tagihan[$c->id]->total_nominal_tagihan ?? 0;
            $total_sisa_tagihan = $sum_sisa_tagihan[$c->id]->total_sisa_tagihan ?? 0;

            if ($total_nominal_tagihan && $c->ppn == 1) {
                $ppn = $total_nominal_tagihan * 0.11;
                $pph = $total_nominal_tagihan * 0.02;
            }

            $nominal_tagihan = $total_nominal_tagihan + $ppn - $pph;
            $sisa_tagihan = $total_sisa_tagihan;

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

    public function tahunan_bersih()
    {
        // create statistics array
        // $tahun = $request->tahun ?? date('Y');

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

        $dataTahun = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->selectRaw('YEAR(tanggal) tahun')
                            ->groupBy('tahun')
                            ->get();
        // looping sum profit each vehicle for each month
        $grand_total_profit = 0;
        $grand_total_pengeluaran = 0;
        $grand_total_bersih = 0;

        foreach ($dataTahun as $tahun) {
            $gt_peryear = 0;
            for ($bulan = 1; $bulan <= 12; $bulan++) {

                $data = Transaksi::with(['kas_uang_jalan', 'kas_uang_jalan.vehicle', 'kas_uang_jalan.vendor'])
                                    ->join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                                    ->join('vehicles as v', 'v.id', 'kuj.vehicle_id')
                                    ->select('transaksis.*', 'kuj.tanggal as tanggal', 'v.nomor_lambung as nomor_lambung')
                                    ->whereMonth('tanggal', $bulan)
                                    ->whereYear('tanggal', $tahun->tahun)
                                    ->where('transaksis.void', 0)
                                    ->get();

                $pengeluaran_kas_kecil = KasBesar::whereMonth('tanggal', $bulan)
                                    ->whereYear('tanggal', $tahun->tahun)
                                    ->whereNotNull('nomor_kode_kas_kecil')
                                    ->sum('nominal_transaksi');

                $coTransactions = KasBesar::whereMonth('tanggal', $bulan)
                                    ->whereYear('tanggal', $tahun->tahun)
                                    ->where('cost_operational', 1)
                                    ->whereIn('jenis_transaksi_id', [1, 2])
                                    ->get()
                                    ->groupBy('jenis_transaksi_id');

                $pengeluaran_co = $coTransactions->has(2) ? $coTransactions[2]->sum('nominal_transaksi') : 0;
                $pemasukan_co = $coTransactions->has(1) ? $coTransactions[1]->sum('nominal_transaksi') : 0;

                $total_co = $pengeluaran_co - $pemasukan_co;

                $gaji = RekapGaji::where('bulan', $bulan)
                                    ->where('tahun', $tahun->tahun)
                                    ->first();

                $total_gaji_bersih = $gaji ? $gaji->rekap_gaji_detail->sum('pendapatan_bersih') : 0;

                $grand_total_profit += $data->sum('profit');
                $grand_total_pengeluaran += $pengeluaran_kas_kecil+$total_gaji_bersih+$total_co;
                $grand_total_bersih += $data->sum('profit') - ($pengeluaran_kas_kecil+$total_gaji_bersih+$total_co);
                $gt_peryear += $data->sum('profit') - ($pengeluaran_kas_kecil+$total_gaji_bersih+$total_co);
                $statistics[$tahun->tahun]['data'][$bulan] = [
                    'nama_bulan' => $nama_bulan[$bulan],
                    'bersih' => $data->sum('profit') - ($pengeluaran_kas_kecil+$total_gaji_bersih+$total_co),

                ];

            }

            $statistics[$tahun->tahun]['total'] = $gt_peryear;
        }


        // dd($statistics);

        return view('rekap.statistik.profit.tahunan-bersih', [
            'statistics' => $statistics,
            'nama_bulan' => $nama_bulan,
            'grand_total_bersih' => $grand_total_bersih,
        ]);
    }

    public function tahunan_bersih_download()
    {

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

        $dataTahun = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->selectRaw('YEAR(tanggal) tahun')
                            ->groupBy('tahun')
                            ->get();
        // looping sum profit each vehicle for each month
        $grand_total_profit = 0;
        $grand_total_pengeluaran = 0;
        $grand_total_bersih = 0;

        foreach ($dataTahun as $tahun) {
            $gt_peryear = 0;
            for ($bulan = 1; $bulan <= 12; $bulan++) {

                $data = Transaksi::with(['kas_uang_jalan', 'kas_uang_jalan.vehicle', 'kas_uang_jalan.vendor'])
                                    ->join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                                    ->join('vehicles as v', 'v.id', 'kuj.vehicle_id')
                                    ->select('transaksis.*', 'kuj.tanggal as tanggal', 'v.nomor_lambung as nomor_lambung')
                                    ->whereMonth('tanggal', $bulan)
                                    ->whereYear('tanggal', $tahun->tahun)
                                    ->where('transaksis.void', 0)
                                    ->get();

                $pengeluaran_kas_kecil = KasBesar::whereMonth('tanggal', $bulan)
                                    ->whereYear('tanggal', $tahun->tahun)
                                    ->whereNotNull('nomor_kode_kas_kecil')
                                    ->sum('nominal_transaksi');

                $coTransactions = KasBesar::whereMonth('tanggal', $bulan)
                                    ->whereYear('tanggal', $tahun->tahun)
                                    ->where('cost_operational', 1)
                                    ->whereIn('jenis_transaksi_id', [1, 2])
                                    ->get()
                                    ->groupBy('jenis_transaksi_id');

                $pengeluaran_co = $coTransactions->has(2) ? $coTransactions[2]->sum('nominal_transaksi') : 0;
                $pemasukan_co = $coTransactions->has(1) ? $coTransactions[1]->sum('nominal_transaksi') : 0;

                $total_co = $pengeluaran_co - $pemasukan_co;

                $gaji = RekapGaji::where('bulan', $bulan)
                                    ->where('tahun', $tahun->tahun)
                                    ->first();

                $total_gaji_bersih = $gaji ? $gaji->rekap_gaji_detail->sum('pendapatan_bersih') : 0;

                $grand_total_profit += $data->sum('profit');
                $grand_total_pengeluaran += $pengeluaran_kas_kecil+$total_gaji_bersih+$total_co;
                $grand_total_bersih += $data->sum('profit') - ($pengeluaran_kas_kecil+$total_gaji_bersih+$total_co);
                $gt_peryear += $data->sum('profit') - ($pengeluaran_kas_kecil+$total_gaji_bersih+$total_co);
                $statistics[$tahun->tahun]['data'][$bulan] = [
                    'nama_bulan' => $nama_bulan[$bulan],
                    'bersih' => $data->sum('profit') - ($pengeluaran_kas_kecil+$total_gaji_bersih+$total_co),

                ];

            }

            $statistics[$tahun->tahun]['total'] = $gt_peryear;
        }

        $pdf = PDF::loadview('rekap.statistik.profit.tahunan-bersih-pdf', [
            'statistics' => $statistics,
            'nama_bulan' => $nama_bulan,
            'grand_total_bersih' => $grand_total_bersih,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Grand Total Tahunan Bersih.pdf');
    }


    public function tonase_tambang(Request $request, Customer $customer)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        // nama bulan dalam indonesia berdasarkan $bulan
        $nama_bulan = Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;

        // get array list date from $bulan
        $date = Carbon::createFromDate($tahun, $bulan)->daysInMonth;

        $dataMuat = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                    ->join('vehicles as v', 'v.id', 'kuj.vehicle_id')
                    ->join('rutes as r', 'r.id', 'kuj.rute_id')
                    ->select('transaksis.*', 'kuj.tanggal as tanggal', 'r.nama as nama_rute', 'r.id as rute_id')
                    ->whereMonth('transaksis.tanggal_muat', $bulan)
                    ->whereYear('transaksis.tanggal_muat', $tahun)
                    ->where('transaksis.void', 0)
                    ->whereNull('transaksis.timbangan_bongkar')
                    ->where('kuj.customer_id', $customer->id)
                    ->get();

        $dataBongkar = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->join('vehicles as v', 'v.id', 'kuj.vehicle_id')
                            ->join('rutes as r', 'r.id', 'kuj.rute_id')
                            ->select('transaksis.*', 'kuj.tanggal as tanggal', 'r.nama as nama_rute', 'r.id as rute_id')
                            ->whereMonth('transaksis.tanggal_bongkar', $bulan)
                            ->whereYear('transaksis.tanggal_bongkar', $tahun)
                            ->where('transaksis.void', 0)
                            ->where('kuj.customer_id', $customer->id)
                            ->get();

        $ruteIds = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->join('vehicles as v', 'v.id', 'kuj.vehicle_id')
                            ->join('rutes as r', 'r.id', 'kuj.rute_id')
                            ->whereMonth('tanggal', $bulan)
                            ->whereYear('tanggal', $tahun)
                            ->where('transaksis.void', 0)
                            ->where('kuj.customer_id', $customer->id)
                            ->pluck('r.id');

        $dbRute = Rute::whereIn('id', $ruteIds)->get();

        $statistics = [];

        for ($i = 1; $i <= $date; $i++) {
            $day = sprintf('%02d', $i) . '-' . $bulan . '-' . $tahun;

            foreach ($dbRute as $rute) {
                $filteredDataMuat = $dataMuat->filter(function ($item) use ($i, $bulan, $tahun, $rute) {
                    return Carbon::parse($item->tanggal_muat)->day == $i &&
                           Carbon::parse($item->tanggal_muat)->month == $bulan &&
                           Carbon::parse($item->tanggal_muat)->year == $tahun &&
                           $item->rute_id == $rute->id;
                });

                $filteredDataBongkar = $dataBongkar->filter(function ($item) use ($i, $bulan, $tahun, $rute) {
                    return Carbon::parse($item->tanggal_bongkar)->day == $i &&
                           Carbon::parse($item->tanggal_bongkar)->month == $bulan &&
                           Carbon::parse($item->tanggal_bongkar)->year == $tahun &&
                           $item->rute_id == $rute->id;
                });
                $ritaseMuat = $filteredDataMuat->count();
                $ritaseBongkar = $filteredDataBongkar->count();
                $tonase_muat = $filteredDataMuat->sum('tonase');
                $tonase_bongkar = $filteredDataBongkar->sum('timbangan_bongkar');

                $statistics[$i][$rute->id] = [
                    'day' => $day,
                    'rute_id' => $rute->id,
                    'rute' => $rute->nama,
                    'data' => [
                        'ritase' => $ritaseMuat+$ritaseBongkar,
                        'tonase_muat' => $tonase_muat,
                        'tonase_bongkar' => $tonase_bongkar,
                    ],
                ];
            }
        }

        $dataTahun = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
            ->selectRaw('YEAR(tanggal) tahun')
            ->groupBy('tahun')
            ->get();

        return view('statistik.tonase-tambang.index', [
            'statistics' => $statistics,
            'nama_bulan' => $nama_bulan,
            'date' => $date,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'dbRute' => $dbRute,
            'customer' => $customer,
            'bulan_angka' => $bulan,
            'dataTahun' => $dataTahun,
        ]);
    }

    public function tonase_tambang_download(Request $request, Customer $customer)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;

        // nama bulan dalam indonesia berdasarkan $bulan
        $nama_bulan = Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;

        // get array list date from $bulan
        $date = Carbon::createFromDate($tahun, $bulan)->daysInMonth;

        $dataMuat = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                    ->join('vehicles as v', 'v.id', 'kuj.vehicle_id')
                    ->join('rutes as r', 'r.id', 'kuj.rute_id')
                    ->select('transaksis.*', 'kuj.tanggal as tanggal', 'r.nama as nama_rute', 'r.id as rute_id')
                    ->whereMonth('transaksis.tanggal_muat', $bulan)
                    ->whereYear('transaksis.tanggal_muat', $tahun)
                    ->where('transaksis.void', 0)
                    ->whereNull('transaksis.timbangan_bongkar')
                    ->where('kuj.customer_id', $customer->id)
                    ->get();

        $dataBongkar = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->join('vehicles as v', 'v.id', 'kuj.vehicle_id')
                            ->join('rutes as r', 'r.id', 'kuj.rute_id')
                            ->select('transaksis.*', 'kuj.tanggal as tanggal', 'r.nama as nama_rute', 'r.id as rute_id')
                            ->whereMonth('transaksis.tanggal_bongkar', $bulan)
                            ->whereYear('transaksis.tanggal_bongkar', $tahun)
                            ->where('transaksis.void', 0)
                            ->where('kuj.customer_id', $customer->id)
                            ->get();

        $ruteIds = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->join('vehicles as v', 'v.id', 'kuj.vehicle_id')
                            ->join('rutes as r', 'r.id', 'kuj.rute_id')
                            ->whereMonth('tanggal', $bulan)
                            ->whereYear('tanggal', $tahun)
                            ->where('transaksis.void', 0)
                            ->where('kuj.customer_id', $customer->id)
                            ->pluck('r.id');

        $dbRute = Rute::whereIn('id', $ruteIds)->get();

        $statistics = [];

        for ($i = 1; $i <= $date; $i++) {
            $day = sprintf('%02d', $i) . '-' . $bulan . '-' . $tahun;

            foreach ($dbRute as $rute) {
                $filteredDataMuat = $dataMuat->filter(function ($item) use ($i, $bulan, $tahun, $rute) {
                    return Carbon::parse($item->tanggal_muat)->day == $i &&
                           Carbon::parse($item->tanggal_muat)->month == $bulan &&
                           Carbon::parse($item->tanggal_muat)->year == $tahun &&
                           $item->rute_id == $rute->id;
                });

                $filteredDataBongkar = $dataBongkar->filter(function ($item) use ($i, $bulan, $tahun, $rute) {
                    return Carbon::parse($item->tanggal_bongkar)->day == $i &&
                           Carbon::parse($item->tanggal_bongkar)->month == $bulan &&
                           Carbon::parse($item->tanggal_bongkar)->year == $tahun &&
                           $item->rute_id == $rute->id;
                });
                $ritaseMuat = $filteredDataMuat->count();
                $ritaseBongkar = $filteredDataBongkar->count();
                $tonase_muat = $filteredDataMuat->sum('tonase');
                $tonase_bongkar = $filteredDataBongkar->sum('timbangan_bongkar');

                $statistics[$i][$rute->id] = [
                    'day' => $day,
                    'rute_id' => $rute->id,
                    'rute' => $rute->nama,
                    'data' => [
                        'ritase' => $ritaseMuat+$ritaseBongkar,
                        'tonase_muat' => $tonase_muat,
                        'tonase_bongkar' => $tonase_bongkar,
                    ],
                ];
            }
        }

        $pdf = PDF::loadview('statistik.tonase-tambang.pdf', [
            'statistics' => $statistics,
            'nama_bulan' => $nama_bulan,
            'date' => $date,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'dbRute' => $dbRute,
            'customer' => $customer,
            'bulan_angka' => $bulan,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Statistik Tonase_'.$bulan.'_'.$tahun.'.pdf');


    }

}
