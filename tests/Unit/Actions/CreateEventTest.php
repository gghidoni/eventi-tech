<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\CreateEvent;
use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateEventTest extends TestCase
{
    use RefreshDatabase;

    protected CreateEvent $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CreateEvent();
    }

    public function test_creates_event_with_valid_data_and_sets_status_to_pending(): void
    {
        $data = [
            'community_id' => 1,
            'title'        => 'Test Event',
            'description'  => 'Description',
            'type'         => EventType::InPerson,
            'start_date'   => now(),
            'end_date'     => now()->addHour(),
        ];

        $event = $this->action->execute($data);

        $this->assertInstanceOf(Event::class, $event);
        $this->assertEquals('Test Event', $event->title);
        $this->assertEquals(EventStatus::Pending, $event->status);
        $this->assertDatabaseHas('events', [
            'title'  => 'Test Event',
            'status' => EventStatus::Pending->value,
        ]);
    }
}
