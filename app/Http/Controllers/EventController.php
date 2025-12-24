<?php

namespace App\Http\Controllers;

use App\Models\Event;

class EventController extends Controller
{
    public function show(Event $event)
    {
        return view('events.show', [
            'event' => $event->load(
                'community',
                'address_book.city',
                'address_book.province',
                'address_book.region',
            ),
        ]);
    }
}
