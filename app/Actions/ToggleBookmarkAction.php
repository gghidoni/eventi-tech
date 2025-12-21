<?php

namespace App\Actions;

use App\Models\User;

class ToggleBookmarkAction
{
    public function execute(User $user, int $eventId): void
    {
        if($user->bookmarks()->where('event_id', $eventId)->exists()) {
            $user->bookmarks()->detach($eventId);
        } else {
            $user->bookmarks()->attach($eventId);
        }
    }
}