<?php

namespace App\Actions;

use App\Enums\CommunityStatus;
use App\Mail\CreatedNewCommunity;
use App\Models\Community;
use Illuminate\Support\Facades\Mail;

class CreateCommunity
{
    public function execute(array $data): Community
    {
        $data['status'] = CommunityStatus::Pending->value;
        $data['slug'] = str()->slug($data['name']);
        $user = auth()->user();
        $community = $user->communities()->create($data);
        Mail::to($user)->send(new CreatedNewCommunity($community));
        return $community;
    }
}