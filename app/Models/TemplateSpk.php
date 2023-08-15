<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stevebauman\Purify\Casts\PurifyHtmlOnGet;

class TemplateSpk extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'halaman',
        'content',
        'created_by',
        'updated_by',
    ];

    // protected $casts = [
    //     'content' => PurifyHtmlOnGet::class,
    // ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
