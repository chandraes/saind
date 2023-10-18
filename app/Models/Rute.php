<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rute extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'jarak',
        'uang_jalan',
        'user_id',
        'edited_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function editedBy()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    // has many customer through CustomerRute
    public function customer()
    {
        return $this->belongsToMany(Customer::class, 'customer_rute', 'rute_id', 'customer_id');
    }

    
}
