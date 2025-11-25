<?php

namespace App\Models;

use App\Models\Rekap\BungaInvestor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Transaksi extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['id_tanggal_muat', 'id_tanggal_bongkar'];

    public function do_checker()
    {
        return $this->belongsTo(User::class, 'do_checker_id', 'id');
    }

    public function kas_uang_jalan()
    {
        return $this->belongsTo(KasUangJalan::class);
    }

    public function dataTahun()
    {
        return $this->selectRaw('YEAR(tanggal_muat) as tahun')->whereNotNull('tanggal_muat')->groupBy('tahun')->get();
    }

    public function getIdTanggalMuatAttribute()
    {
        return date('d-m-Y', strtotime($this->tanggal_muat));
    }

    public function getIdTanggalBongkarAttribute()
    {
        return date('d-m-Y', strtotime($this->tanggal_bongkar));
    }

    public static function getTagihanData($customerId, $ruteId = null, $filter = null, $tanggalFilter = null)
    {
        $query = self::with('kas_uang_jalan.vehicle', 'kas_uang_jalan.vendor', 'kas_uang_jalan.customer', 'kas_uang_jalan.rute', 'do_checker')
                ->join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')
                ->where('status', 3)
                ->where('transaksis.void', 0)
                ->where('tagihan', 0)
                ->where('keranjang', 0)
                ->where('kuj.customer_id', $customerId)
                ->when($ruteId, function ($query, $ruteId) {
                    return $query->where('kuj.rute_id', $ruteId);
                });

        if ($tanggalFilter && $filter) {
            if (strpos($tanggalFilter, 'to') !== false) {
                // $tanggalFilter is a date range
                $dates = explode('to', $tanggalFilter);
                $startDate = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
                $endDate = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();

                // dd($startDate, $endDate, $filter, $tanggalFilter);

                $query->whereBetween($filter, [$startDate, $endDate]);
            } else {
                // $tanggalFilter is a single date
                $date = Carbon::createFromFormat('d-m-Y', trim($tanggalFilter));

                $query->where($filter, '>=', $date);
            }
        }

         // If $filter is not null, order by the $filter column
        if ($filter) {
            $query->orderBy($filter)->orderBy('kas_uang_jalan_id');
        }

        return $query->select('transaksis.*', 'kuj.tanggal as tanggal')->get();
    }

    public static function getKeranjangTagihanData($customerId, $ruteId = null, $filter = null, $tanggalFilter = null)
    {
        $query = self::with('kas_uang_jalan.vehicle', 'kas_uang_jalan.vendor', 'kas_uang_jalan.customer', 'kas_uang_jalan.rute', 'do_checker')
                ->join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')
                ->where('status', 3)
                ->where('transaksis.void', 0)
                ->where('tagihan', 0)
                ->where('keranjang', 1)
                ->where('kuj.customer_id', $customerId)
                ->when($ruteId, function ($query, $ruteId) {
                    return $query->where('kuj.rute_id', $ruteId);
                });

        if ($tanggalFilter && $filter) {
            if (strpos($tanggalFilter, 'to') !== false) {
                // $tanggalFilter is a date range
                $dates = explode('to', $tanggalFilter);
                $startDate = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
                $endDate = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();

                // dd($startDate, $endDate, $filter, $tanggalFilter);

                $query->whereBetween($filter, [$startDate, $endDate]);
            } else {
                // $tanggalFilter is a single date
                $date = Carbon::createFromFormat('d-m-Y', trim($tanggalFilter));

                $query->where($filter, '>=', $date);
            }
        }

         // If $filter is not null, order by the $filter column
        if ($filter) {
            $query->orderBy($filter)->orderBy('kas_uang_jalan_id');
        }

        return $query->select('transaksis.*', 'kuj.tanggal as tanggal')->get();
    }

    public function notaBonus($sponsorId,$bulan,$tahun)
    {
        return self::with(['kas_uang_jalan',
                            'kas_uang_jalan.vendor',
                            'kas_uang_jalan.vendor.sponsor',
                            'kas_uang_jalan.vehicle',
                            'kas_uang_jalan.customer',
                            'kas_uang_jalan.rute'])
                        ->whereYear('tanggal_bongkar', $tahun)
                        ->whereMonth('tanggal_bongkar', $bulan)
                        ->where('status', 3)
                        ->where('void', 0)
                        ->where('bonus', 0)
                        ->whereHas('kas_uang_jalan.vendor.sponsor', function ($query) use ($sponsorId) {
                            $query->where('id', $sponsorId);
                        })

                        ->get();

            // return self::with([
            //         'kas_uang_jalan',
            //         'kas_uang_jalan.vendor',
            //         'kas_uang_jalan.vendor.sponsor',
            //         'kas_uang_jalan.vehicle',
            //         'kas_uang_jalan.customer',
            //         'kas_uang_jalan.rute'
            //         ])
            //         ->where('status', 3)
            //         ->where('void', 0)
            //         ->where('bonus', 0)
            //         ->whereHas('kas_uang_jalan.vendor.sponsor', function ($query) use ($sponsorId) {
            //         $query->where('id', $sponsorId);
            //         })
            //         ->whereHas('kas_uang_jalan', function ($query) use ($bulan, $tahun) {
            //                 $query->whereYear('tanggal', $tahun)
            //                     ->whereMonth('tanggal', $bulan);
            //                 })
            //         ->get();
    }

    public function getIdNotaBonus($sponsorId, $bulan, $tahun)
    {
        return self::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->join('vendors as v', 'kuj.vendor_id', 'v.id')
                            ->join('sponsors as s', 'v.sponsor_id', 's.id')
                            ->select('transaksis.id')
                            ->whereYear('transaksis.tanggal_bongkar', $tahun)->whereMonth('transaksis.tanggal_bongkar', $bulan)
                            ->where('transaksis.status', 3)
                            ->where('transaksis.void', 0)
                            ->where('bonus', 0)
                            ->where('s.id', $sponsorId)
                            ->get();
    }

    public static function getNotaBayar($vendorId)
    {
        return self::with(['kas_uang_jalan', 'kas_uang_jalan.vendor', 'kas_uang_jalan.customer', 'kas_uang_jalan.rute', 'kas_uang_jalan.vehicle'])
                    ->join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')
                    ->where('status', 3)->where('transaksis.void', 0)
                    ->where('bayar', 0)->where('kuj.vendor_id', $vendorId)->get();
    }

    public static function getNotaBongkar()
    {
        return self::with(['kas_uang_jalan', 'kas_uang_jalan.vendor', 'kas_uang_jalan.customer', 'kas_uang_jalan.rute', 'kas_uang_jalan.vehicle'])
                    ->where('status', 2)->where('void', 0)->get();
    }

    public static function getNotaCsr($customerId)
    {
        return self::with(['kas_uang_jalan', 'kas_uang_jalan.vendor', 'kas_uang_jalan.customer', 'kas_uang_jalan.rute', 'kas_uang_jalan.vehicle'])
                    ->join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')
                    ->where('kuj.customer_id', $customerId)
                    ->where('transaksis.status', 3)
                    ->where('transaksis.void', 0)
                    ->where('csr', 0)
                    ->where('nominal_csr', '>', 0)
                    ->select('transaksis.*')
                    ->get();
    }

    public function getNotaCsrNew($customerId, $bulan, $tahun)
    {
        return self::with(['kas_uang_jalan', 'kas_uang_jalan.vendor', 'kas_uang_jalan.customer', 'kas_uang_jalan.rute', 'kas_uang_jalan.vehicle'])
                    ->join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')
                    ->where('kuj.customer_id', $customerId)
                    ->whereYear('transaksis.tanggal_bongkar', $tahun)->whereMonth('transaksis.tanggal_bongkar', $bulan)
                    ->where('transaksis.status', 3)
                    ->where('transaksis.void', 0)
                    ->where('transaksis.csr', 0)
                    ->where('transaksis.nominal_csr', '>', 0)
                    ->select('transaksis.*')
                    ->get();
    }

    public function getNotaVoid($month, $year)
    {
        return self::with(['kas_uang_jalan', 'kas_uang_jalan.vendor', 'kas_uang_jalan.customer', 'kas_uang_jalan.rute', 'kas_uang_jalan.vehicle'])
                        ->join('kas_uang_jalans as kuj', 'kuj.id', '=', 'transaksis.kas_uang_jalan_id')
                        ->select('transaksis.*')
                        ->whereMonth('kuj.tanggal', $month)->whereYear('kuj.tanggal', $year)->where('transaksis.void', 1)->get();
    }

    public function countNotaTagihan($customerId)
    {
        return self::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                    ->where('kuj.customer_id', $customerId)
                    ->where('transaksis.status', 3)->where('transaksis.tagihan', 0)->where('transaksis.void', 0)
                    ->count();
    }

    public function sumNotaTagihan($customerId)
    {
        return self::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                    ->where('kuj.customer_id', $customerId)
                    ->where('transaksis.status', 3)->where('transaksis.tagihan', 0)->where('transaksis.void', 0)
                    ->sum('nominal_tagihan');
    }

    public function changeStateNotaFisik($id)
    {
        $transaksi = self::findOrFail($id);
        // if $transaksi->nota_fisik is 0, then change it to 1, and vice versa
        $transaksi->nota_fisik = !$transaksi->nota_fisik;

        if ($transaksi->nota_fisik) {
            $transaksi->do_checker_id = auth()->user()->id;
        } else {
            $transaksi->do_checker_id = null;
        }

        $vehicle = $transaksi->kas_uang_jalan->vehicle;

        // If $transaksi->nota_fisik is true, decrement do_count, otherwise increment
        $transaksi->nota_fisik ? $vehicle->do_count-- : $vehicle->do_count++;

        // Start the transaction
        DB::beginTransaction();

        try {
            $transaksi->save();
            $vehicle->save();

            // If both saves were successful, commit the transaction
            DB::commit();
        } catch (\Exception $e) {
            // If there was an error, rollback the transaction
            DB::rollback();

            // Then rethrow the exception so it can be handled elsewhere
            throw $e;
        }
    }

    public function profitHarian($bulan, $tahun, $offset = 0)
    {
        // nama bulan dalam indonesia berdasarkan $bulan
        $nama_bulan = Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;

        // get array list date vrom $bulan
        $date = Carbon::createFromDate($tahun, $bulan)->daysInMonth;

        $dateRange = Carbon::createFromDate($tahun, $bulan);
        $tanggalAwal = $dateRange->startOfMonth()->toDateTimeString(); // Hasil: '2025-09-01 00:00:00'
        $tanggalAkhir = $dateRange->endOfMonth()->toDateTimeString();   // Hasil: '2025-09-30 23:59:59'

        $data = Transaksi::with(['kas_uang_jalan', 'kas_uang_jalan.vendor'])
                    ->selectRaw('DATE(transaksis.tanggal_bongkar) as tanggal_bongkar, SUM(transaksis.profit) as total_nominal_profit, SUM(transaksis.nominal_tagihan) as total_nominal_tagihan, SUM(transaksis.nominal_bayar) as total_nominal_bayar, SUM(transaksis.nominal_bonus) as total_nominal_bonus,  SUM(transaksis.nominal_csr) as total_nominal_csr')
                    ->whereBetween('transaksis.tanggal_bongkar', [$tanggalAwal, $tanggalAkhir])
                    ->where('transaksis.void', 0)
                    ->groupBy('tanggal_bongkar')
                    ->get()
                    ->keyBy('tanggal_bongkar');

        // dd($data);

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

        $all = [
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
        ];

        return $all;
    }

    public function profitBulanan($tahun)
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
        $grant_total_co = 0;
        $grand_total_gaji = 0;
        $grand_total_kas_kecil = 0;
        $grand_total_bunga_investor = 0;
        $gt_penyesuaian = 0;
        $gt_penalty = 0;
        $gt_lain = 0;

        for ($bulan = 1; $bulan <= 12; $bulan++) {

            $data = Transaksi::with(['kas_uang_jalan', 'kas_uang_jalan.vehicle', 'kas_uang_jalan.vendor'])
                                // ->join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                                // ->join('vehicles as v', 'v.id', 'kuj.vehicle_id')
                                ->select('transaksis.*')
                                ->whereMonth('tanggal_bongkar', $bulan)
                                ->whereYear('tanggal_bongkar', $tahun)
                                ->where('transaksis.void', 0)
                                ->get();


            $startDate = Carbon::createFromDate($tahun, $bulan, 1)->startOfDay();
            $endDate = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->endOfDay();

            $invoiceData = InvoiceTagihan::whereBetween('updated_at', [$startDate, $endDate])
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

            $lainTransactions = KasBesar::whereMonth('tanggal', $bulan)
                                ->whereYear('tanggal', $tahun)
                                ->where('lain_lain', 1)
                                ->whereIn('jenis_transaksi_id', [1, 2])
                                ->get()
                                ->groupBy('jenis_transaksi_id');

            $pengeluaran_lain = $lainTransactions->has(2) ? $lainTransactions[2]->sum('nominal_transaksi') : 0;
            $pemasukan_lain = $lainTransactions->has(1) ? $lainTransactions[1]->sum('nominal_transaksi') : 0;

            $pengeluaran_co = $coTransactions->has(2) ? $coTransactions[2]->sum('nominal_transaksi') : 0;
            $pemasukan_co = $coTransactions->has(1) ? $coTransactions[1]->sum('nominal_transaksi') : 0;

            $total_lain = $pengeluaran_lain - $pemasukan_lain;
            $total_co = $pengeluaran_co - $pemasukan_co;

            $gaji = RekapGaji::where('bulan', $bulan)
                                ->where('tahun', $tahun)
                                ->first();

            $total_gaji_bersih = $gaji ? $gaji->rekap_gaji_detail->sum('pendapatan_bersih') : 0;

            $grand_total_profit += $data->sum('profit');
            $grand_total_pengeluaran += $pengeluaran_kas_kecil+$total_gaji_bersih+$total_co+$bungaInvestor;
            $grand_total_bersih += $data->sum('profit') - ($pengeluaran_kas_kecil+$total_gaji_bersih+$total_co+$bungaInvestor+$penalty+$total_lain) + $penyesuaian;

            $grand_total_gaji += $total_gaji_bersih;
            $grant_total_co += $total_co;
            $grand_total_kas_kecil += $pengeluaran_kas_kecil;
            $grand_total_bunga_investor += $bungaInvestor;
            $gt_penyesuaian += $penyesuaian;
            $gt_penalty += $penalty;
            $gt_lain += $total_lain;

            $total_pengeluaran = $pengeluaran_kas_kecil+$total_gaji_bersih+$total_co+$bungaInvestor+$penalty+$total_lain;

            $statistics[$bulan] = [
                'nama_bulan' => $nama_bulan[$bulan],
                'profit' => $data->sum('profit'),
                'total_gaji' => $total_gaji_bersih,
                'total_co' => $total_co,
                'kas_kecil' => $pengeluaran_kas_kecil,
                'bunga_investor' => $bungaInvestor,
                'penyesuaian' => $penyesuaian,
                'lain' => $total_lain,
                'penalty' => $penalty,
                'pengeluaran' => $total_pengeluaran,
                'bersih' => ($data->sum('profit') - $total_pengeluaran) + $penyesuaian,
            ];

        }

        $all = [
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
            'gt_lain' => $gt_lain,
        ];

        return $all;
    }

    public function performUnit($bulan, $tahun, $offset, $vendor = null)
    {
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

        $all = [
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
        ];

        return $all;
    }

   // Di dalam Model Transaksi.php

   public function performUnitAllVendor($filters)
    {
        $month = $filters['month'];
        $year = $filters['year'];
        $vendorId = $filters['vendor'];

        // --- LOGIKA UTAMA (LEFT JOIN STRATEGY) ---

        // 1. Kita mulai query dari 'Vehicle', bukan 'Transaksi'
        // Karena kita ingin semua vehicle tampil, entah ada transaksi atau tidak.
        $query = Vehicle::query()
            ->join('vendors as ven', 'ven.id', '=', 'vehicles.vendor_id') // Tetap Inner Join ke Vendor (karena vehicle pasti punya vendor)

            // 2. LEFT JOIN ke Kas Uang Jalan (KUJ)
            // PENTING: Filter Bulan & Tahun ditaruh DI DALAM fungsi join (closure).
            // Jika ditaruh di global ->where(), data kosong akan terbuang.
            ->leftJoin('kas_uang_jalans as kuj', function($join) use ($month, $year) {
                $join->on('vehicles.id', '=', 'kuj.vehicle_id')
                    ->whereMonth('kuj.tanggal', $month)
                    ->whereYear('kuj.tanggal', $year);
            })

            // 3. LEFT JOIN ke Transaksi
            // Filter void juga harus di dalam sini
            ->leftJoin('transaksis as t', function($join) {
                $join->on('kuj.id', '=', 't.kas_uang_jalan_id')
                    ->where('t.void', 0);
            })

            // 4. LEFT JOIN ke Rute
            ->leftJoin('rutes as r', 'r.id', '=', 'kuj.rute_id')

            // 5. Filter Global (Hanya untuk Master Data)
            ->where('vehicles.status', '!=', 'nonaktif')
            ->where('ven.status', 'aktif');

        // Filter Vendor Spesifik (Jika dipilih)
        if ($vendorId) {
            $query->where('vehicles.vendor_id', $vendorId);
        }

        // --- SELECT RAW & GROUP BY ---
        // Logika SUM tetap sama.
        // Karena pakai LEFT JOIN, jika tidak ada rute, r.jarak bernilai NULL.
        // NULL < 50 adalah False (0), jadi aman.
        $data = $query->selectRaw("
                ven.nama as vendor_name,
                vehicles.nomor_lambung,
                SUM(CASE WHEN r.jarak < 50 THEN 1 ELSE 0 END) as total_rute_pendek,
                SUM(CASE WHEN r.jarak >= 50 THEN 1 ELSE 0 END) as total_rute_panjang
            ")
            ->groupBy('ven.nama', 'vehicles.id', 'vehicles.nomor_lambung') // Group by ID vehicle untuk akurasi
            ->orderBy('ven.nama')
            ->orderBy('vehicles.nomor_lambung')
            ->get();

        // --- DATA PENDUKUNG LAINNYA (TIDAK BERUBAH) ---
        $vendors = Vendor::where('status', 'aktif')->get();

        // Ambil tahun dari tabel transaksi (tetap inner join tidak masalah untuk list tahun)
        $dataTahun = self::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                        ->selectRaw('YEAR(tanggal) tahun')
                        ->groupBy('tahun')
                        ->get();

        $nama_bulan = \Carbon\Carbon::createFromDate($year, $month)->locale('id')->monthName;

        return [
            'data'        => $data,
            'vendors'     => $vendors,
            'dataTahun'   => $dataTahun,
            'nama_bulan'  => $nama_bulan,
            'bulan'       => str_pad($month, 2, '0', STR_PAD_LEFT),
            'bulan_angka' => $month,
            'tahun'       => $year,
            'vendor'      => $vendorId,
            'offset'      => $filters['offset'] ?? 0
        ];
    }

    public function performUnitTahunan($tahun)
    {
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

        $all = [
            'statistics' => $statistics,
            'tahun' => $tahun,
            'vehicle' => $vehicle,
            'dataTahun' => $dataTahun,
        ];

        return $all;
    }

    public function upahGendong($vehicle,$bulan,$tahun, $tanggal_filter = null)
    {
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

        $all = [
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
        ];

        return $all;
    }

}
