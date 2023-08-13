<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'nama_dokumen',
        'file',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
