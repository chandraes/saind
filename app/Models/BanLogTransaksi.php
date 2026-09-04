<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BanLogTransaksi extends Model
{
    protected $guarded = ['id'];

    public function banLog()
    {
        return $this->belongsTo(BanLog::class, 'ban_log_id');
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }

    
}
