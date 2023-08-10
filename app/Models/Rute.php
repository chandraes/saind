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

    
}
