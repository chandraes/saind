<?php

namespace App\Models;

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
                            ->whereYear('kuj.tanggal', $tahun)->whereMonth('kuj.tanggal', $bulan)
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

}
