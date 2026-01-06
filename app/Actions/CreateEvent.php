<?php

namespace App\Actions;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

class CreateEvent
{
    public function execute(array $data): Event
    {

        return DB::transaction(function () use ($data) {
            $data['status'] = EventStatus::Pending->value;

            return Event::create($data);
        });
    }
}
