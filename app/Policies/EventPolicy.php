<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EventPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if (!in_array($ability, ['viewPublic', 'bookmark'], true) && $user->is_admin && $user->hasVerifiedEmail()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Event $event): bool
    {
        return $event->community()->where('user_id', $user->id)->exists();
    }

    public function viewPublic(?User $user, Event $event): Response
    {
        return $event->isPubliclyVisible()
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function create(User $user): bool
    {
        return $user->hasVerifiedEmail() && $user->has_active_community;
    }

    public function update(User $user, Event $event): bool
    {
        return $event->community()->where('user_id', $user->id)->exists()
            && !in_array($event->status, [EventStatus::Reject, EventStatus::Terminate], true);
    }

    public function delete(User $user, Event $event): bool
    {
        return false;
    }

    public function bookmark(User $user, Event $event): bool
    {
        return $event->isPubliclyVisible()
            && !$event->community()->where('user_id', $user->id)->exists();
    }
}
