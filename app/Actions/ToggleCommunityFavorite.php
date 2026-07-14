<?php

namespace App\Actions;

use App\Models\Community;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class ToggleCommunityFavorite
{
    public function execute(User $user, int $communityId): bool
    {
        $community = Community::query()->find($communityId);

        if (!$community) {
            throw new InvalidArgumentException('Community not found');
        }

        Gate::forUser($user)->authorize('favorite', $community);

        return DB::transaction(function () use ($user, $communityId): bool {
            $result = $user->favoriteCommunities()->toggle($communityId);

            // toggle ritorna ['attached' => [id], 'detached' => [id]].
            return !empty($result['attached']);
        });
    }
}
