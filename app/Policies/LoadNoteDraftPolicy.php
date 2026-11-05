<?php

namespace App\Policies;

use App\Models\LoadNoteDraft;
use App\Models\User;

class LoadNoteDraftPolicy
{
    public function inbox(User $user): bool
    {
        return $this->reviewAny($user) || ($user->role === 'vendor-operational' && $user->vendor_id !== null && $user->vendor()->exists());
    }

    public function readInbox(User $user, LoadNoteDraft $note): bool
    {
        return $this->reviewAny($user) || ($this->inbox($user) && (int) $user->vendor_id === $note->vendor_id);
    }

    public function reviewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'su', 'user'], true);
    }

    public function review(User $user, LoadNoteDraft $note): bool
    {
        return $this->reviewAny($user);
    }

    public function adjustPayment(User $user, LoadNoteDraft $note): bool
    {
        return $this->review($user, $note) && in_array($user->role, ['admin', 'su'], true);
    }

    public function viewAny(User $user): bool
    {
        return in_array($user->role, config('mobile.load_note_roles', []), true)
            && $user->vendor_id !== null && $user->vendor()->exists();
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function view(User $user, LoadNoteDraft $note): bool
    {
        return $this->viewAny($user) && (int) $user->vendor_id === $note->vendor_id;
    }
}
