<?php

namespace App\Actions;

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class ToggleBookmark
{
    public function execute(User $user, int $eventId): bool
    {
        $event = Event::query()->find($eventId);

        if (!$event) {
            throw new InvalidArgumentException('Event not found');
        }

        Gate::forUser($user)->authorize('bookmark', $event);

        return DB::transaction(function () use ($user, $eventId) {
            $result = $user->bookmarks()->toggle($eventId);

            // toggle returns ['attached' => [id], 'detached' => [id]]
            return !empty($result['attached']);
        });
    }
}
