<?php

namespace App\Policies\V1;

use App\Models\Event;
use App\Models\User;
use App\Permissions\V1\Abilities;

class EventPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function toggleBookmark(User $user, Event $event)
    {
        if ($user->tokenCan(Abilities::ToggleBookmark)) {
            return true;
        }

        return false;
    }
}
