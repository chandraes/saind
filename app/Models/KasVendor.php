<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasVendor extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function getIdTanggalAttribute()
    {
        return date('d-m-Y', strtotime($this->tanggal));
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function bbm_storing()
    {
        return $this->belongsTo(BbmStoring::class);
    }

    public function sisa_terakhir($vendorId)
    {
        return $this->where('vendor_id', $vendorId)->latest()->orderBy('id', 'desc')->first()->sisa ?? 0;
    }

    public function dataTahun()
    {
        return $this->selectRaw('YEAR(tanggal) tahun')->groupBy('tahun')->orderBy('tahun', 'desc')->get();
    }

    public function getKasVendor($vendorId, $month, $year)
    {
        return $this->with(['vehicle'])
                ->where('vendor_id', $vendorId)->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->get();
    }

    public function getLatest($vendorId, $month, $year)
    {
        return $this->where('vendor_id', $vendorId)
                    ->whereMonth('tanggal', $month)
                    ->whereYear('tanggal', $year)
                    ->latest()
                    ->orderBy('id', 'desc')
                    ->first();
    }
}
