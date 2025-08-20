<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\EventStatus;
use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\V1\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Filters\V1\EventFilter;
use App\Policies\V1\EventPolicy;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

class EventController extends ApiController
{

    protected $policyClass = EventPolicy::class;


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


    public function toggleBookmark(Request $request, $eventId)
    {
        $user = $request->user();

        try {
            $event = Event::findOrFail($eventId);
            Gate::authorize('toggleBookmark', $event);

            if ($user->bookmarks()->where('event_id', $eventId)->exists()) {
                $user->bookmarks()->detach($eventId);
                $bookmarks = $user->bookmarks->pluck('id');

                return $this->success('Bookmark rimosso', $bookmarks);
            } else {
                $user->bookmarks()->attach($event->id);
                $bookmarks = $user->bookmarks->pluck('id');

                return $this->success('Bookmark aggiunto', $bookmarks);
            }
            return $this->success('Bookmark toggled successfully', $bookmarks);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return $this->error('Forbidden: ' . $e->getMessage(), 403);
        } catch (ModelNotFoundException $e) {
            return $this->error('Event not found', 404);
        }
    }
}
