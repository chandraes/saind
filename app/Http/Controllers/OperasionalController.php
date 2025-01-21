<?php

namespace App\Http\Controllers;

use App\Models\AktivasiMaintenance;
use App\Models\BanLog;
use App\Models\Customer;
use App\Models\InvoiceBayar;
use App\Models\Vendor;
use App\Models\KasVendor;
use App\Models\KategoriBarangMaintenance;
use App\Models\MaintenanceLog;
use App\Models\OdoLog;
use App\Models\PosisiBan;
use App\Models\Rute;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\Transaksi;
use App\Models\UpahGendong;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class OperasionalController extends Controller
{
    public function kas_vendor(Request $request)
    {
        $vendor = Vendor::find($request->vendor_id);

        if (!$vendor) {
            return redirect()->route('home')->with('error', 'Vendor tidak ditemukan');
        }

        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $db = new KasVendor;

        $dataTahun = $db->dataTahun();

        $data = $db->getKasVendor($vendor->id, $bulan, $tahun);

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = \Carbon\Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = \Carbon\Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;
        // get latest data from month before current month
        $dataSebelumnya = $db->getLatest($vendor->id, $bulanSebelumnya, $tahunSebelumnya);

        $sisaTerakhir = $data->last()->sisa ?? 0;

        return view('operasional.kas-vendor.index', [
            'data' => $data,
            'vendor' => $vendor,
            'dataTahun' => $dataTahun,
            'dataSebelumnya' => $dataSebelumnya,
            'stringBulan' => $stringBulan,
            'tahun' => $tahun,
            'tahunSebelumnya' => $tahunSebelumnya,
            'bulan' => $bulan,
            'stringBulanNow' => $stringBulanNow,
            'sisaTerakhir' => $sisaTerakhir,
        ]);
    }

    public function kas_vendor_detail(InvoiceBayar $invoiceBayar)
    {
        $periode = $invoiceBayar->periode;
        $vendor = Vendor::find($invoiceBayar->vendor_id);

        $data = $invoiceBayar->load(['transaksi', 'transaksi.kas_uang_jalan',
                                    'transaksi.kas_uang_jalan.customer', 'transaksi.kas_uang_jalan.rute',
                                    'transaksi.kas_uang_jalan.vehicle', 'transaksi.kas_uang_jalan.vendor'])
                            ->transaksi;

        return view('operasional.kas-vendor.detail', [
            'data' => $data,
            'vendor' => $vendor,
            'periode' => $periode,
            'invoice_id' => $invoiceBayar->id
        ]);
    }

    public function kas_vendor_print(Request $request)
    {
        $vendor = Vendor::find($request->vendor);

        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $db = new KasVendor;

        $dataTahun = $db->dataTahun();

        $data = $db->getKasVendor($vendor->id, $bulan, $tahun);

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = \Carbon\Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = \Carbon\Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;
        // get latest data from month before current month
        $dataSebelumnya = $db->getLatest($vendor->id, $bulanSebelumnya, $tahunSebelumnya);

        $sisaTerakhir = $data->last()->sisa ?? 0;

        $pdf = PDF::loadview('operasional.kas-vendor.print', [
            'data' => $data,
            'vendor' => $vendor,
            'dataTahun' => $dataTahun,
            'dataSebelumnya' => $dataSebelumnya,
            'stringBulan' => $stringBulan,
            'tahun' => $tahun,
            'tahunSebelumnya' => $tahunSebelumnya,
            'bulan' => $bulan,
            'stringBulanNow' => $stringBulanNow,
            'sisaTerakhir' => $sisaTerakhir,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Rekap Kas Vendor '.$vendor->nama." ".$stringBulanNow.' '.$tahun.'.pdf');
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


        return view('operasional.perform-unit.index', [
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

        $pdf = PDF::loadview('operasional.perform-unit.print', [
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

    public function statistik_vendor()
    {
        $vendors = Vendor::with('kas_vendor')->get();

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
            $sisa = $v->kas_vendor->last()->sisa ?? 0;
            $total_bayar = $nominal_bayar - $nominal_uang_jalan;

            $statistics[$v->nickname] = [
                'vendor' => $v,
                'total_nominal_bayar' => $total_bayar,
                'total_sisa' => $sisa,
                'total' => $sisa-$total_bayar,
            ];
        }

        return view('operasional.statistik-vendor.index', [
            'vendors' => $vendors,
            'statistics' => $statistics,
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


        return view('operasional.upah-gendong.index', [
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

    public function maintenance_vehicle(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => 'required|exists:aktivasi_maintenances,vehicle_id',
        ]);

        $tanggalNow = now();
        $odo = 0;
        $baut = '-';
        $db = new MaintenanceLog();
        $tahun = $request->tahun ?? date('Y');
        $dataTahun = $db->dataTahun();
        // dd($dataTahun);

        $equipment = KategoriBarangMaintenance::select('id', 'nama')->get();

        $tahun = $request->tahun ?? date('Y');

        // Define the start and end of the year
        $startOfYear = Carbon::create($tahun)->startOfYear();
        $endOfYear = Carbon::create($tahun)->endOfYear();

        $activation_start = AktivasiMaintenance::where('vehicle_id', $data['vehicle_id'])->first()->tanggal_mulai;

        // If the activation year is the same as the requested year, use the activation date as the start date
        // Otherwise, use the start of the requested year
        $start_date = $activation_start->year == $tahun ? $activation_start : $startOfYear;

        // Fetch all relevant MaintenanceLog records for the year
        $maintenanceLogs = MaintenanceLog::where('vehicle_id', $data['vehicle_id'])
            ->whereIn('kategori_barang_maintenance_id', $equipment->pluck('id'))
            ->whereBetween('created_at', [$start_date, $endOfYear])
            ->get();

        // Fetch all relevant OdoLog records for the year
        $odoLogs = OdoLog::where('vehicle_id', $data['vehicle_id'])
            ->whereBetween('created_at', [$start_date, $endOfYear])
            ->get();

        $i = 0;
        while (true) {
            $startOfWeek = $start_date->copy()->addWeeks($i)->startOfWeek();
            $endOfWeek = $startOfWeek->copy()->endOfWeek();

            if ($endOfWeek->greaterThan($endOfYear)) {
                break;
            }

            // Set the locale to Indonesian
            Carbon::setLocale('id');

            $week = $startOfWeek->translatedFormat('d M') . ' - ' . $endOfWeek->translatedFormat('d M');

            foreach ($equipment as $eq) {
                // Filter the maintenance logs in memory
                $count = $maintenanceLogs->where('kategori_barang_maintenance_id', $eq->id)
                    ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->sum('qty') ?? 0;

                // Filter the odo logs in memory
                $weekly[$week]['odometer'] = $odoLogs->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->sortByDesc('created_at')
                    ->first()
                    ->odometer ?? 0;

                if ($weekly[$week]['odometer'] !=0) {
                    $odo = $weekly[$week]['odometer'];
                }

                $weekly[$week]['filter_strainer'] = $odoLogs->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->sortByDesc('created_at')
                    ->first()
                    ->filter_strainer ?? '-';

                if (Carbon::parse($tanggalNow)->between($startOfWeek, $endOfWeek)){
                    $state = $odoLogs->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                            ->sortByDesc('created_at')
                            ->first() ? 1 : 0;
                }

                $weekly[$week]['filter_udara'] = $odoLogs->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->sortByDesc('created_at')
                    ->first()
                    ->filter_udara ?? '-';

                $weekly[$week]['baut'] = $odoLogs->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->sortByDesc('created_at')
                    ->first()
                    ->baut ?? '-';

                if ($weekly[$week]['baut'] != '-') {
                    $baut = $weekly[$week]['baut'];
                }

                $weekly[$week][$eq->nama] = $count;
            }

            $i++;
        }

        $vehicle = Vehicle::leftJoin('upah_gendongs', 'vehicles.id', '=', 'upah_gendongs.vehicle_id')
            ->where('vehicles.id', $data['vehicle_id'])
            ->select('vehicles.*', 'upah_gendongs.nama_driver as driver', 'upah_gendongs.nama_pengurus as pengurus', 'upah_gendongs.tanggal_masuk_driver as tanggal_masuk_driver', 'upah_gendongs.tanggal_masuk_pengurus as tanggal_masuk_pengurus')
            ->first();

            // dd($vehicle);

        return view('operasional.maintenance.index', [
            'weekly' => $weekly,
            'vehicle' => $vehicle,
            'equipment' => $equipment,
            'dataTahun' => $dataTahun,
            'tahun' => $tahun,
            'odo' => $odo,
            'baut' => $baut,
        ]);
    }

    public function maintenance_vehicle_print(Request $request)
    {
        ini_set('max_execution_time', 80);
        ini_set('memory_limit', '256M');

        $data = $request->validate([
            'vehicle_id' => 'required|exists:aktivasi_maintenances,vehicle_id',
        ]);

        $tanggalNow = now();

        $db = new MaintenanceLog();
        $tahun = $request->tahun ?? date('Y');
        $dataTahun = $db->dataTahun();
        // dd($dataTahun);

        $equipment = KategoriBarangMaintenance::select('id', 'nama')->get();

        $tahun = $request->tahun ?? date('Y');

        // Define the start and end of the year
        $startOfYear = Carbon::create($tahun)->startOfYear();
        $endOfYear = Carbon::create($tahun)->endOfYear();

        $activation_start = AktivasiMaintenance::where('vehicle_id', $data['vehicle_id'])->first()->tanggal_mulai;

        // If the activation year is the same as the requested year, use the activation date as the start date
        // Otherwise, use the start of the requested year
        $start_date = $activation_start->year == $tahun ? $activation_start : $startOfYear;

        // Fetch all relevant MaintenanceLog records for the year
        $maintenanceLogs = MaintenanceLog::where('vehicle_id', $data['vehicle_id'])
            ->whereIn('kategori_barang_maintenance_id', $equipment->pluck('id'))
            ->whereBetween('created_at', [$start_date, $endOfYear])
            ->get();

        // Fetch all relevant OdoLog records for the year
        $odoLogs = OdoLog::where('vehicle_id', $data['vehicle_id'])
            ->whereBetween('created_at', [$start_date, $endOfYear])
            ->get();

        $i = 0;
        while (true) {
            $startOfWeek = $start_date->copy()->addWeeks($i)->startOfWeek();
            $endOfWeek = $startOfWeek->copy()->endOfWeek();

            if ($endOfWeek->greaterThan($endOfYear)) {
                break;
            }

            // Set the locale to Indonesian
            Carbon::setLocale('id');

            $week = $startOfWeek->translatedFormat('d M') . ' - ' . $endOfWeek->translatedFormat('d M');

            foreach ($equipment as $eq) {
                // Filter the maintenance logs in memory
                $count = $maintenanceLogs->where('kategori_barang_maintenance_id', $eq->id)
                    ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->sum('qty') ?? 0;

                // Filter the odo logs in memory
                $weekly[$week]['odometer'] = $odoLogs->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->max('odometer') ?? 0;

                $weekly[$week]['filter_strainer'] = $odoLogs->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->sortByDesc('created_at')
                    ->first()
                    ->filter_strainer ?? '-';

                $weekly[$week]['filter_udara'] = $odoLogs->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->sortByDesc('created_at')
                    ->first()
                    ->filter_udara ?? '-';

                $weekly[$week]['baut'] = $odoLogs->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->sortByDesc('created_at')
                    ->first()
                    ->baut ?? '-';

                $weekly[$week][$eq->nama] = $count;
            }

            $i++;
        }


        $vehicle = Vehicle::leftJoin('upah_gendongs', 'vehicles.id', '=', 'upah_gendongs.vehicle_id')
            ->where('vehicles.id', $data['vehicle_id'])
            ->select('vehicles.*', 'upah_gendongs.nama_driver as driver', 'upah_gendongs.nama_pengurus as pengurus', 'upah_gendongs.tanggal_masuk_driver as tanggal_masuk_driver', 'upah_gendongs.tanggal_masuk_pengurus as tanggal_masuk_pengurus')
            ->first();

        $pdf = PDF::loadview('operasional.maintenance.print', [
                    'weekly' => $weekly,
                    'vehicle' => $vehicle,
                    'equipment' => $equipment,
                    'dataTahun' => $dataTahun,
                    'tahun' => $tahun,
                ])->setPaper('a4', 'landscape');

        return $pdf->stream('Rekap Maintenance '.$tahun.'.pdf');
    }

    public function store_odo(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => 'required|exists:aktivasi_maintenances,vehicle_id',
            'odometer' => 'required',
            'filter_strainer' => 'required',
            'filter_udara' => 'required',
            'baut' => 'required|numeric',
        ]);

        $data['odometer'] = str_replace('.', '', $data['odometer']);

        OdoLog::create([
            'vehicle_id' => $data['vehicle_id'],
            'odometer' => $data['odometer'],
            'filter_strainer' => $data['filter_strainer'],
            'filter_udara' => $data['filter_udara'],
            'baut' => $data['baut'],
        ]);

        return redirect()->back()->with('success', 'Berhasil menambahkan Odometer!!');
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

        return view('operasional.tonase-tambang.index', [
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

        $pdf = PDF::loadview('operasional.tonase-tambang.pdf', [
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

    public function ban_luar(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
        ]);

        // Retrieve vehicle with driver and pengurus information
        $vehicle = Vehicle::leftJoin('upah_gendongs as ug', 'vehicles.id', 'ug.vehicle_id')
                          ->where('vehicles.id', $request->vehicle_id)
                          ->select('vehicles.*', 'ug.nama_driver as nama_driver', 'ug.nama_pengurus as pengurus')
                          ->first();

        // Retrieve the latest BanLog for each posisi_ban_id for the given vehicle_id
        $banLogs = BanLog::where('vehicle_id', $request->vehicle_id)
                         ->select('posisi_ban_id', 'merk', 'no_seri', 'kondisi', 'created_at')
                         ->orderBy('created_at', 'desc')
                         ->get()
                         ->unique('posisi_ban_id')
                         ->mapWithKeys(function ($banLog) {
                             return [$banLog->posisi_ban_id => [
                                 'merk' => $banLog->merk,
                                 'no_seri' => $banLog->no_seri,
                                 'kondisi' => $banLog->kondisi,
                                 'tanggal_ganti' => \Carbon\Carbon::parse($banLog->created_at)->format('d-m-Y'),
                             ]];
                         });

        // Map the BanLog data to the PosisiBan
        $ban = PosisiBan::all()->map(function ($ban) use ($banLogs) {
            $ban->banLog = $banLogs[$ban->id] ?? null;
            return $ban;
        });

        return view('operasional.ban-luar.index', [
            'vehicle' => $vehicle,
            'ban' => $ban,
        ]);

    }

    public function ban_luar_histori($vehicle, $posisi)
    {
        $vehicle = Vehicle::find($vehicle);

        return view('operasional.ban-luar.histori', [
            'vehicle' => $vehicle,
            'posisi' => PosisiBan::findOrFail($posisi),
        ]);
    }

    public function ban_luar_histori_data(Request $request)
    {
        if ($request->ajax()) {
            $length = $request->get('length'); // Get the requested number of records

            // Define the columns for sorting
            $columns = ['merk', 'no_seri', 'kondisi', 'created_at'];

            $query = BanLog::where('vehicle_id', $request->vehicle)
                        ->where('posisi_ban_id', $request->posisi)
                        ->orderBy('created_at', 'desc');

            // Handle the sorting
            if ($request->has('order')) {
                $columnIndex = $request->get('order')[0]['column']; // Get the index of the sorted column
                $sortDirection = $request->get('order')[0]['dir']; // Get the sort direction
                $column = $columns[$columnIndex]; // Get the column name

                $query->orderBy($column, $sortDirection);
            }

            $data = $query->paginate($length); // Use the requested number of records

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $data->total(),
                'recordsFiltered' => $data->total(),
                'data' => $data->items(),
            ]);
        }

        return abort(404);
    }

}
