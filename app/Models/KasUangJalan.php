<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasUangJalan extends Model
{
    use HasFactory;
    protected $fillable = [
        'tanggal',
        'vendor_id',
        'vehicle_id',
        'kode_kas_uang_jalan',
        'nomor_kode_kas_uang_jalan',
        'kode_uang_jalan',
        'nomor_uang_jalan',
        'jenis_transaksi_id',
        'nominal_transaksi',
        'saldo',
        'customer_id',
        'rute_id',
        'transfer_ke',
        'bank',
        'no_rekening',
    ];

    public function jenis_transaksi()
    {
        return $this->belongsTo(JenisTransaksi::class);
    }

    public function getTanggalAttribute($value)
    {
        return date('d-m-Y', strtotime($value));
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function rute()
    {
        return $this->belongsTo(Rute::class);
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }
}
