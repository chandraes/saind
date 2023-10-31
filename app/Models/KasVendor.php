<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasVendor extends Model
{
    use HasFactory;
    protected $guarded = [];
    
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

    public function sisa_terakhir()
    {
        return $this->latest()->orderBy('id', 'desc')->first()->sisa;
    }
}
