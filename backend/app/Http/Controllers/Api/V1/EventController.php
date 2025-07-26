<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\EventStatus;
use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\V1\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Filters\V1\EventFilter;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EventController extends ApiController
{

    public function index(EventFilter $filters)
    {
        return EventResource::collection(Event::filter($filters)->whereStatus(EventStatus::Active)->paginate(10));
    }

    public function show($eventId)
    {
        try {
            $event = Event::findOrFail($eventId);
            return new EventResource($event);

        } catch (ModelNotFoundException $e) {
            return $this->error('Event not found', 404);
        }
    }
}
