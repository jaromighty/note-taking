<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    /**
     * Determine if the given team can be viewed by the user.
     */
    public function view(User $user, Team $team): bool
    {
        return $user->teams()->where('teams.id', $team->id)->exists();
    }

    /**
     * Determine if the given team can be updated by the user.
     */
    public function update(User $user, Team $team): bool
    {
        return $user->teams()->where('teams.id', $team->id)->exists();
    }

    /**
     * Determine if the given team can be deleted by the user.
     */
    public function delete(User $user, Team $team): bool
    {
        // Only the owner can delete.
        return $user->id === $team->owner_id;
    }
}
