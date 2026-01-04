<?php

namespace App\Actions;

use App\Models\Event;
use Illuminate\Support\Facades\DB;

class UpdateEvent
{
    public function execute(Event $event, array $data): Event
    {
        return DB::transaction(function () use ($event, $data) {
            $event->update($data);

            return $event;
        });
    }
}
