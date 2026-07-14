<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Cfp;
use App\Models\User;

class CfpPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($ability !== 'apply' && $user->is_admin && $user->hasVerifiedEmail()) {
            return true;
        }

        return null;
    }

    public function apply(User $user, Cfp $cfp): bool
    {
        $cfp->loadMissing('event.community');
        $event = $cfp->event;

        return $user->hasVerifiedEmail()
            && $cfp->isInternal()
            && $cfp->isPublished()
            && $cfp->isOpen()
            && $event?->isPubliclyVisible() === true
            && $event->community?->user_id !== $user->id;
    }
}
