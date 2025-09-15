<?php

namespace App\Policies\V1;

use App\Models\User;
use App\Permissions\V1\Abilities;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function showBookmarks(User $user)
    {
        if ($user->tokenCan(Abilities::ShowOwnBookmarks)) {
            return true;
        }

        return false;
    }
}
