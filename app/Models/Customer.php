<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editedBy()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    // has many rute through CustomerRute
    public function rute()
    {
        return $this->belongsToMany(Rute::class, 'customer_rute', 'customer_id', 'rute_id');
    }

    public function document()
    {
        return $this->hasMany(CustomerDocument::class);
    }

    public function customer_tagihan()
    {
        return $this->hasMany(CustomerTagihan::class);
    }
}
