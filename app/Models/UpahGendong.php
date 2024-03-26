<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpahGendong extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $appends = ['nf_nominal'];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function getNfNominalAttribute()
    {
        return number_format($this->nominal, 0, ',', '.');
    }
}
