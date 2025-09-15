<?php

declare(strict_types=1);

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
        return $user->tokenCan(Abilities::ShowOwnBookmarks);
    }
}
