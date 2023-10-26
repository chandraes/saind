<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceBonus extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class);
    }
}
