<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $guarded = [];

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

    public static function getTagihanData($customerId, $ruteId = null)
    {
        return self::with('kas_uang_jalan.vehicle', 'kas_uang_jalan.vendor', 'kas_uang_jalan.customer', 'kas_uang_jalan.rute')
                    ->join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')
                    ->where('status', 3)
                    ->where('transaksis.void', 0)
                    ->where('tagihan', 0)
                    ->where('kuj.customer_id', $customerId)
                    ->when($ruteId, function ($query, $ruteId) {
                        return $query->where('kuj.rute_id', $ruteId);
                    })
                    ->select('transaksis.*')
                    ->get();
    }

    public function notaBonus($sponsorId,$bulan,$tahun)
    {
        return self::with(['kas_uang_jalan',
                            'kas_uang_jalan.vendor',
                            'kas_uang_jalan.vendor.sponsor',
                            'kas_uang_jalan.vehicle',
                            'kas_uang_jalan.customer',
                            'kas_uang_jalan.rute'])
                        ->whereYear('tanggal_muat', $tahun)
                        ->whereMonth('tanggal_muat', $bulan)
                        ->where('status', 3)
                        ->where('void', 0)
                        ->where('bonus', 0)
                        ->whereHas('kas_uang_jalan.vendor.sponsor', function ($query) use ($sponsorId) {
                            $query->where('id', $sponsorId);
                        })
                        ->get();
    }

    public function getIdNotaBonus($sponsorId, $bulan, $tahun)
    {
        return self::join('kas_uang_jalans as kuj', 'kuj.id', 'transaksis.kas_uang_jalan_id')
                            ->join('vendors as v', 'kuj.vendor_id', 'v.id')
                            ->join('sponsors as s', 'v.sponsor_id', 's.id')
                            ->select('transaksis.id')
                            ->whereYear('transaksis.tanggal_muat', $tahun)->whereMonth('transaksis.tanggal_muat', $bulan)
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

}
