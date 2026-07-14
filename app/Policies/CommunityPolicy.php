<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\CommunityStatus;
use App\Models\Community;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CommunityPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($ability !== 'viewPublic' && $user->is_admin && $user->hasVerifiedEmail()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Community $community): bool
    {
        return $community->user_id === $user->id;
    }

    public function viewPublic(?User $user, Community $community): Response
    {
        return $community->isPubliclyVisible()
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function create(User $user): bool
    {
        return $user->hasVerifiedEmail();
    }

    public function update(User $user, Community $community): bool
    {
        return $community->user_id === $user->id
            && $community->status !== CommunityStatus::Rejected;
    }

    public function delete(User $user, Community $community): bool
    {
        return false;
    }

    public function createEvent(User $user, Community $community): bool
    {
        return $community->user_id === $user->id
            && $community->status === CommunityStatus::Active;
    }

    public function favorite(User $user, Community $community): bool
    {
        return $community->isPubliclyVisible() && $community->user_id !== $user->id;
    }
}
