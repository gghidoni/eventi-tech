<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\V1\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Filters\V1\EventFilter;

class EventController extends ApiController
{

    public function index(EventFilter $filters)
    {
        return EventResource::collection(Event::filter($filters)->paginate());
    }
}
