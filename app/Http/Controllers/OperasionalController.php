<?php

namespace App\Http\Controllers;

use App\Models\InvoiceBayar;
use App\Models\Vendor;
use App\Models\KasVendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\Transaksi;
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

}
