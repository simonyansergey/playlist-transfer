<?php


namespace App\Policies;

use App\Models\PlaylistTransfer;
use App\Models\User;

class PlaylistTransferPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PlaylistTransfer $playlistTransfer): bool
    {
        return $user->id == $playlistTransfer->user_id;
    }

    /**
     * Determine whether the user can execute the model
     */
    public function execute(User $user, PlaylistTransfer $playlistTransfer): bool
    {
        return $user->id == $playlistTransfer->user_id;
    }
}
