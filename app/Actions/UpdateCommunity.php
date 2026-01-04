<?php

namespace App\Actions;

use App\Models\Community;
use Illuminate\Support\Facades\DB;

class UpdateCommunity
{
    public function execute(Community $community, array $data): Community
    {
        return DB::transaction(function () use ($community, $data) {
            $community->update($data);

            return $community;
        });
    }
}
