<?php

namespace App\Models\Pajak;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekapPphVendorDetail extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function rekap_pph_vendor()
    {
        return $this->belongsTo(RekapPphVendor::class);
    }
}
