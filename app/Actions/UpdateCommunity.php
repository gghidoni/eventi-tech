<?php

namespace App\Actions;

use App\Models\Community;
use Illuminate\Support\Facades\DB;

class UpdateCommunity
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Community $community, array $data): Community
    {
        return DB::transaction(function () use ($community, $data): Community {
            $community->update($data);

            return $community;
        });
    }
}
