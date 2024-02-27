<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasUangJalan extends Model
{
    use HasFactory;
    protected $guarded = [];

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
        return $this->hasMany(Transaksi::class);
    }

    public function getKasUangJalan($month, $year)
    {
        return $this->with(['jenis_transaksi', 'vendor', 'vehicle', 'customer', 'rute'])
                    ->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->get();
    }

    public function getLatest($month, $year)
    {
        return $this->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->latest()->orderBy('id', 'desc')->first();
    }

    public function dataTahun()
    {
        return $this->selectRaw('YEAR(tanggal) tahun')->groupBy('tahun')->orderBy('tahun', 'desc')->get();
    }

    public function saldoKasUangJalan()
    {
        return $this->latest()->orderBy('id', 'desc')->first()->saldo ?? 0;
    }
}
