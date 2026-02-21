<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\CommunityStatus;
use App\Models\Community;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateCommunity
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Community
    {
        return DB::transaction(function () use ($data): Community {
            $data['status'] = CommunityStatus::Pending->value;
            $data['slug'] = str()->slug($data['name']);

            /** @var User $user */
            $user = auth()->user();

            /** @var Community $community */
            $community = $user->communities()->create($data);

            return $community;
        });
    }
}
