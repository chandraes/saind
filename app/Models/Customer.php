<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_customer',
        'nama',
        'singkatan',
        'npwp',
        'alamat',
        'contact_person',
        'jabatan',
        'no_hp',
        'no_wa',
        'email',
        'harga_opname',
        'harga_titipan',
        'created_by',
        'edited_by',
        'tanggal_muat',
        'nota_muat',
        'tonase',
        'tanggal_bongkar',
        'selisih',
        'ppn',
        'pph',
        'tagihan_dari',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editedBy()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    // has many rute through CustomerRute
    public function rute()
    {
        return $this->belongsToMany(Rute::class, 'customer_rute', 'customer_id', 'rute_id');
    }

    public function document()
    {
        return $this->hasMany(CustomerDocument::class);
    }

    public function customer_tagihan()
    {
        return $this->hasMany(CustomerTagihan::class);
    }
}
