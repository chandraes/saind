<?php

namespace App\Models\Dokumen;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DokumenData extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $appends = ['id_tanggal_expired'];

    // 1 kontrak-tambang, 2 kontrak-vendor, 3 sph, 4 dokumen-lainnya

    public const JENIS_DOKUMEN = [
        1 => 'Kontrak Tambang',
        2 => 'Kontrak Vendor',
        3 => 'SPH',
        4 => 'Company Profile',
    ];

    // scope
    public function scopeKontrakTambang($query)
    {
        return $query->where('jenis_dokumen', 1);
    }

    public function scopeKontrakVendor($query)
    {
        return $query->where('jenis_dokumen', 2);
    }

    public function scopeSph($query)
    {
        return $query->where('jenis_dokumen', 3);
    }

    public function scopeCompanyProfil($query)
    {
        return $query->where('jenis_dokumen', 4);
    }

    public function getIdTanggalExpiredAttribute()
    {
        $tanggal = $this->tanggal_expired != null ? Carbon::parse($this->tanggal_expired)->format('d-m-Y') : '-';

        return $tanggal;
    }

}
