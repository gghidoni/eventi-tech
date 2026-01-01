<?php

namespace App\Actions;

use App\Models\Community;

class UpdateCommunity
{
    public function execute(Community $community, array $data): Community
    {
        $community->update($data);

        return $community;
    }
}
