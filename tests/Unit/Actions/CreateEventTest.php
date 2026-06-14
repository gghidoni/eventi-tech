<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\CreateEvent;
use App\Enums\CfpMode;
use App\Enums\CfpStatus;
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

    public function test_creates_event_with_external_cfp(): void
    {
        $data = [
            'community_id' => 1,
            'title'        => 'Test Event',
            'description'  => 'Description',
            'type'         => EventType::InPerson,
            'start_date'   => now(),
            'end_date'     => now()->addHour(),
            'cfp'          => [
                'mode'         => CfpMode::External,
                'status'       => CfpStatus::Published,
                'title'        => 'CFP - Test Event',
                'opens_at'     => now(),
                'closes_at'    => now()->addWeek(),
                'external_url' => 'https://example.com/cfp',
            ],
        ];

        $event = $this->action->execute($data);

        $this->assertDatabaseHas('cfps', [
            'event_id'     => $event->id,
            'mode'         => CfpMode::External->value,
            'status'       => CfpStatus::Published->value,
            'external_url' => 'https://example.com/cfp',
        ]);
        $this->assertSame('https://example.com/cfp', $event->fresh()->cfp->external_url);
    }
}
