<?php

namespace App\Actions;

use App\Models\Community;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ToggleCommunityFavorite
{
    public function execute(User $user, int $communityId): bool
    {
        if (!Community::find($communityId)) {
            throw new InvalidArgumentException('Community not found');
        }

        return DB::transaction(function () use ($user, $communityId): bool {
            $result = $user->favoriteCommunities()->toggle($communityId);

            // toggle ritorna ['attached' => [id], 'detached' => [id]].
            return !empty($result['attached']);
        });
    }
}
