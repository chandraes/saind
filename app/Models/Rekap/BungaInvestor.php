<?php

namespace App\Models\Rekap;

use App\Models\db\Kreditor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BungaInvestor extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['tanggal'];

    public function kreditor()
    {
        return $this->belongsTo(Kreditor::class);
    }

    public function getTanggalAttribute()
    {
        return date('d-m-Y', strtotime($this->created_at));
    }
}
