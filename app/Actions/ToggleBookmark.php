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
            $result = $user->bookmarks()->toggle($eventId);

            // toggle returns ['attached' => [id], 'detached' => [id]]
            return !empty($result['attached']);
        });
    }
}
