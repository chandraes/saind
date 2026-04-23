<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceAdditional extends Model
{

    protected $guarded = ['id'];

    public function details()
    {
        return $this->hasMany(InvoiceAdditionalDetail::class);
    }
}
