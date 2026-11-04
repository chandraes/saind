<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoadNoteEvent extends Model
{
    use HasFactory;

    protected $fillable = ['load_note_draft_id', 'actor_id', 'action', 'reason', 'before', 'after'];

    protected function casts(): array
    {
        return ['before' => 'array', 'after' => 'array'];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(LoadNoteDraft::class, 'load_note_draft_id');
    }
}
