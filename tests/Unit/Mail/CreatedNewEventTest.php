<?php

declare(strict_types=1);

namespace Tests\Unit\Mail;

use App\Mail\CreatedNewEvent;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreatedNewEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_mailable_has_correct_subject(): void
    {
        $event = Event::factory()->create();
        $mailable = new CreatedNewEvent($event);

        $envelope = $mailable->envelope();

        $this->assertStringContainsString('Complimenti', $envelope->subject);
        $this->assertStringContainsString('evento', $envelope->subject);
        $this->assertStringContainsString('creato', $envelope->subject);
    }

    public function test_mailable_uses_correct_view(): void
    {
        $event = Event::factory()->create();
        $mailable = new CreatedNewEvent($event);

        $content = $mailable->content();

        $this->assertEquals('emails.events.created', $content->view);
    }

    public function test_mailable_passes_event_to_view(): void
    {
        $event = Event::factory()->create(['title' => 'Test Event']);
        $mailable = new CreatedNewEvent($event);

        $content = $mailable->content();

        $this->assertArrayHasKey('community', $content->with);
    }

    public function test_mailable_renders_successfully(): void
    {
        $event = Event::factory()->create();
        $mailable = new CreatedNewEvent($event);

        $mailable->assertSeeInHtml($event->title);
    }
}
