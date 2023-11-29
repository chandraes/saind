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
        return self::join('kas_uang_jalans as kuj', 'transaksis.kas_uang_jalan_id', 'kuj.id')
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

}
