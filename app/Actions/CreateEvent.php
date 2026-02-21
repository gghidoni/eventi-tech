<?php

namespace App\Actions;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

class CreateEvent
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Event
    {
        return DB::transaction(function () use ($data): Event {
            $data['status'] = EventStatus::Pending->value;

            /** @var Event $event */
            $event = Event::query()->create($data);

            return $event;
        });
    }
}
