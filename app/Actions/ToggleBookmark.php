<?php

namespace App\Actions;

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ToggleBookmark
{
    public function execute(User $user, int $eventId): bool
    {
        if (!Event::find($eventId)) {
            throw new InvalidArgumentException('Event not found');
        }

        return DB::transaction(function () use ($user, $eventId) {
            $exists = $user->bookmarks()->where('event_id', $eventId)->exists();

            if ($exists) {
                $user->bookmarks()->detach($eventId);

                return false; // bookmark rimosso
            }

            $user->bookmarks()->attach($eventId);

            return true; // bookmark aggiunto
        });
    }
}
