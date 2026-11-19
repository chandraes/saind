<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Rute;
use App\Models\Transaksi;
use Illuminate\Support\Carbon;

class MineTonnageReport
{
    public function forMonth(Customer $customer, int $month, int $year): array
    {
        $bulan = sprintf('%02d', $month);
        $tahun = $year;

        // nama bulan dalam indonesia berdasarkan $bulan
        $nama_bulan = Carbon::createFromDate($tahun, $bulan, 1)->locale('id')->monthName;

        // get array list date from $bulan
        $date = Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;

        $transactions = Transaksi::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
            ->join('vehicles as v', 'v.id', 'kuj.vehicle_id')
            ->join('rutes as r', 'r.id', 'kuj.rute_id')
            ->where('transaksis.void', 0)
            ->where('kuj.customer_id', $customer->id);

        $dataMuat = (clone $transactions)
            ->whereMonth('transaksis.tanggal_muat', $bulan)
            ->whereYear('transaksis.tanggal_muat', $tahun)
            ->whereNull('transaksis.timbangan_bongkar')
            ->get(['r.id as rute_id', 'transaksis.tanggal_muat', 'transaksis.tonase'])
            ->groupBy([fn (Transaksi $note): int => Carbon::parse($note->tanggal_muat)->day, 'rute_id']);

        $dataBongkar = (clone $transactions)
            ->whereMonth('transaksis.tanggal_bongkar', $bulan)
            ->whereYear('transaksis.tanggal_bongkar', $tahun)
            ->get(['r.id as rute_id', 'transaksis.tanggal_bongkar', 'transaksis.timbangan_bongkar'])
            ->groupBy([fn (Transaksi $note): int => Carbon::parse($note->tanggal_bongkar)->day, 'rute_id']);

        $ruteIds = $transactions
            ->whereMonth('kuj.tanggal', $bulan)
            ->whereYear('kuj.tanggal', $tahun)
            ->distinct()
            ->pluck('r.id');

        $dbRute = Rute::whereIn('id', $ruteIds)->get();

        $statistics = [];

        for ($i = 1; $i <= $date; $i++) {
            $day = sprintf('%02d', $i).'-'.$bulan.'-'.$tahun;

            foreach ($dbRute as $rute) {
                $muat = $dataMuat[$i][$rute->id] ?? collect();
                $bongkar = $dataBongkar[$i][$rute->id] ?? collect();

                $statistics[$i][$rute->id] = [
                    'day' => $day,
                    'rute_id' => $rute->id,
                    'rute' => $rute->nama,
                    'data' => [
                        'ritase' => $muat->count() + $bongkar->count(),
                        'tonase_muat' => $muat->sum('tonase'),
                        'tonase_bongkar' => $bongkar->sum('timbangan_bongkar'),
                    ],
                ];
            }
        }

        return compact('statistics', 'dbRute', 'date', 'nama_bulan');
    }
}
