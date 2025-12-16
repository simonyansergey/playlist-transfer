<?php

namespace App\Policies\Models\PlaylistTransfer;

use App\Models\PlaylistTransfer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PlaylistTransferPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PlaylistTransfer $playlistTransfer): bool
    {
        return $user->id == $playlistTransfer->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PlaylistTransfer $playlistTransfer): bool
    {
        return false;
    }

    public function execute(User $user, PlaylistTransfer $playlistTransfer): bool
    {
        return $user->id == $playlistTransfer->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PlaylistTransfer $playlistTransfer): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PlaylistTransfer $playlistTransfer): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PlaylistTransfer $playlistTransfer): bool
    {
        return false;
    }
}
