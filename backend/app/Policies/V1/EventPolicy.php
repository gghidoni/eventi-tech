<?php

declare(strict_types=1);

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
        return $user->tokenCan(Abilities::ToggleBookmark);
    }
}
