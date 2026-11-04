<?php

namespace Database\Factories;

use App\Models\LoadNoteDraft;
use App\Models\LoadNoteEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LoadNoteEvent> */
class LoadNoteEventFactory extends Factory
{
    public function definition(): array
    {
        return ['action' => 'updated', 'reason' => fake()->sentence(), 'before' => [], 'after' => [], 'actor_id' => User::factory()];
    }

    public function forNote(LoadNoteDraft $note, User $actor): static
    {
        return $this->state(fn (): array => ['load_note_draft_id' => $note->id, 'actor_id' => $actor->id]);
    }
}
