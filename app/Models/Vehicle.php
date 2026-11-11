<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['id_tanggal_pajak_stnk', 'id_tanggal_kir', 'id_tanggal_kimper', 'id_tanggal_sim'];

    protected function casts(): array
    {
        return ['pembatasan_filter_oli' => 'boolean'];
    }

    /**
     * Kendaraan yang tampil di Filter & Oli Mesin untuk akun ini: selain
     * nonaktif, dan (kecuali akun vendor) sudah diaktifkan admin.
     *
     * @template TQuery of \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     *
     * @param  TQuery  $query
     * @return TQuery
     */
    public static function whereFilterOilEligible($query, User $user, string $table = 'vehicles')
    {
        return $query->where($table.'.status', '!=', 'nonaktif')
            ->when($user->requiresFilterOilActivation(), fn ($query) => $query->where($table.'.pembatasan_filter_oli', true));
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeNonaktif($query)
    {
        return $query->where('status', 'nonaktif');
    }

    public static function nextNomorLambung()
    {
        return static::max('nomor_lambung') + 1;
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function kas_uang_jalan()
    {
        return $this->hasMany(KasUangJalan::class);
    }

    public function getIdTanggalPajakStnkAttribute()
    {
        return Carbon::parse($this->tanggal_pajak_stnk)->format('d-m-Y');
    }

    public function getIdTanggalKirAttribute()
    {
        return $this->tanggal_kir != null ? Carbon::parse($this->tanggal_kir)->format('d-m-Y') : 00 - 00 - 0000;
    }

    public function getIdTanggalKimperAttribute()
    {
        return Carbon::parse($this->tanggal_kimper)->format('d-m-Y');
    }

    public function getIdTanggalSimAttribute()
    {
        return Carbon::parse($this->tanggal_sim)->format('d-m-Y');
    }
}
