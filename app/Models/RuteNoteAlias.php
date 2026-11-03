<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RuteNoteAlias extends Model
{
    protected $fillable = ['rute_id', 'name'];

    public static function normalize(string $name): string
    {
        return preg_replace('/[^A-Z0-9]/', '', strtoupper(Str::ascii($name)));
    }

    protected static function booted(): void
    {
        static::saving(function (self $alias): void {
            $alias->name = trim($alias->name);
            $alias->normalized_name = self::normalize($alias->name);
        });
    }

    public function rute(): BelongsTo
    {
        return $this->belongsTo(Rute::class);
    }
}
