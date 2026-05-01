<?php

namespace App\Models;

use BcMath\Number;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class InvoiceAddVendor extends Model
{
    protected $guarded = [];

    protected $appends = ['nf_dpp', 'nf_nominal', 'periode_invoice', 'tanggal', 'nf_ppn', 'nf_pph', 'nf_total', 'tanggal_lunas'];

    protected static function booted()
    {
        static::creating(function ($model) {
            // Hanya generate jika periode belum diisi manual
            if (empty($model->periode)) {
                $maxPeriode = self::where('vendor_id', $model->vendor_id)
                    ->where('jenis', $model->jenis)
                    ->max('periode');

                // Jika belum ada data (null), mulai dari 1. Jika ada, tambah 1.
                $model->periode = ($maxPeriode ?? 0) + 1;
            }
        });
    }

    public function getTanggalAttribute()
    {
        return $this->created_at ? $this->created_at->format('Y-m-d') : null;
    }

    public function getTanggalLunasAttribute()
    {
        return $this->updated_at ? $this->updated_at->format('Y-m-d') : null;
    }

    public function getPeriodeInvoiceAttribute()
    {
        $jenis = $this->jenis == 'kompensasi_jr' ? "Komp. Jalan Rusak" : ucfirst(str_replace("_", ' ', $this->jenis));
        return $jenis.' '.$this->periode;
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function details()
    {
        return $this->hasMany(InvoiceAddVendorDetail::class);
    }

    public function getNfDppAttribute()
    {
        return number_format($this->dpp, 0 ,',','.');
    }


    public function getNfNominalAttribute()
    {
        return number_format($this->nominal, 0 ,',','.');
    }

    public function getNfPpnAttribute()
    {
        return number_format($this->ppn, 0 ,',','.');
    }

    public function getNfPphAttribute()
    {
        return number_format($this->pph, 0 ,',','.');
    }

    public function getNfTotalAttribute()
    {
        return number_format($this->total, 0 ,',','.');
    }
}
