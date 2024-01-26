<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasBesar extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function lastKasBesar()
    {
        return $this->latest()->orderBy('id', 'desc')->first();
    }

    public function jenis_transaksi()
    {
        return $this->belongsTo(JenisTransaksi::class);
    }

    public function getTanggalAttribute($value)
    {
        return date('d-m-Y', strtotime($value));
    }

    public function setTanggalAttribute($value)
    {
        $this->attributes['tanggal'] = date('Y-m-d', strtotime($value));
    }

    public function insert_bypass($data)
    {
        $data['tanggal'] = date('Y-m-d');
        $data['nominal_transaksi'] = str_replace('.', '', $data['nominal_transaksi']);

        if($data['jenis_transaksi_id'] == 1){
            $data['saldo'] = $this->lastKasBesar()->saldo + $data['nominal_transaksi'];
        } elseif($data['jenis_transaksi_id'] == 2){

            $data['saldo'] = $this->lastKasBesar()->saldo - $data['nominal_transaksi'];
        }

        $data['transfer_ke'] = '-';
        $data['bank'] = '-';
        $data['no_rekening'] = '-';

        $data['modal_investor_terakhir'] = $this->lastKasBesar()->modal_investor_terakhir;

        $store = $this->create($data);

        return $store;

    }


}
