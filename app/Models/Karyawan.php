<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'nickname',
        'jabatan_id',
        'nik',
        'npwp',
        'bpjs_tk',
        'bpjs_kesehatan',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'no_hp',
        'no_wa',
        'bank',
        'no_rekening',
        'nama_rekening',
        'mulai_bekerja',
        'status',
        'foto_ktp',
        'foto_diri',
        'created_by',
        'updated_by',
    ];

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
