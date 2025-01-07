<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceTagihan extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $appends = ['id_tanggal', 'nf_total_tagihan', 'nf_total_awal', 'nf_penyesuaian', 'nf_penalty', 'nf_ppn', 'nf_pph', 'nf_sisa_tagihan', 'nf_total_bayar', 'total_dpp', 'id_tanggal_hardcopy', 'id_tanggal_softcopy'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function getIdTanggalAttribute()
    {
        return date('d-m-Y', strtotime($this->tanggal));
    }

    public function getIdTanggalHardcopyAttribute()
    {
        return date('d-m-Y', strtotime($this->tanggal_hardcopy));
    }

    public function getIdTanggalSoftcopyAttribute()
    {
        return date('d-m-Y', strtotime($this->created_at));
    }

    public function getIdEstimasiPembayaranAttribute()
    {
        return date('d-m-Y', strtotime($this->estimasi_pembayaran));
    }

    public function getTotalDppAttribute()
    {
        return number_format($this->total_awal + $this->penyesuaian - $this->penalty, 0, ',', '.');
    }

    public function getNfPenaltyAttribute()
    {
        return number_format($this->penalty, 0, ',', '.');
    }

    public function getNfTotalTagihanAttribute()
    {
        return number_format($this->total_tagihan, 0, ',', '.');
    }

    public function getNfTotalAwalAttribute()
    {
        return number_format($this->total_awal, 0, ',', '.');
    }

    public function getNfPenyesuaianAttribute()
    {
        return number_format($this->penyesuaian, 0, ',', '.');
    }

    public function getNfPpnAttribute()
    {
        return number_format($this->ppn, 0, ',', '.');
    }

    public function getNfPphAttribute()
    {
        return number_format($this->pph, 0, ',', '.');
    }

    public function getNfSisaTagihanAttribute()
    {
        return number_format($this->sisa_tagihan, 0, ',', '.');
    }

    public function getNfTotalBayarAttribute()
    {
        return number_format($this->total_bayar, 0, ',', '.');
    }

    // has many transaksi from pivot table invoice_tagihan_details
    public function invoice_tagihan_details()
    {
        return $this->hasMany(InvoiceTagihanDetail::class);
    }


    public function transaksi()
    {
        return $this->hasManyThrough(
            Transaksi::class,
            InvoiceTagihanDetail::class,
            'invoice_tagihan_id',
            'id',
            'id',
            'transaksi_id'
        );
    }

}
