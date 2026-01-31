<?php

declare(strict_types=1);

namespace Tests\Unit\Mail;

use App\Mail\AdminNewEventNotification;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNewEventNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mailable_has_correct_subject(): void
    {
        $event = Event::factory()->create();
        $mailable = new AdminNewEventNotification($event);

        $envelope = $mailable->envelope();

        $this->assertEquals('Nuovo evento da approvare', $envelope->subject);
    }

    public function test_mailable_uses_correct_view(): void
    {
        $event = Event::factory()->create();
        $mailable = new AdminNewEventNotification($event);

        $content = $mailable->content();

        $this->assertEquals('emails.admin.new-event', $content->view);
    }

    public function test_mailable_passes_event_to_view(): void
    {
        $event = Event::factory()->create(['title' => 'Event to Approve']);
        $mailable = new AdminNewEventNotification($event);

        $content = $mailable->content();

        $this->assertArrayHasKey('event', $content->with);
        $this->assertEquals($event->id, $content->with['event']->id);
    }

    public function test_mailable_renders_successfully(): void
    {
        $event = Event::factory()->create();
        $mailable = new AdminNewEventNotification($event);

        $mailable->assertSeeInHtml($event->title);
    }
}
