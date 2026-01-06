<?php

namespace App\Actions;

use App\Enums\CommunityStatus;
use App\Models\Community;
use Illuminate\Support\Facades\DB;

class CreateCommunity
{
    public function execute(array $data): Community
    {
        return DB::transaction(function () use ($data) {
            $data['status'] = CommunityStatus::Pending->value;
            $data['slug'] = str()->slug($data['name']);

            $user = auth()->user();
            $community = $user->communities()->create($data);

            return $community;
        });
    }
}
