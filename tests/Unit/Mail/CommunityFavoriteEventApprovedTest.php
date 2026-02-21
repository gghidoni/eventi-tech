<?php

declare(strict_types=1);

namespace Tests\Unit\Mail;

use App\Mail\CommunityFavoriteEventApproved;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityFavoriteEventApprovedTest extends TestCase
{
    use RefreshDatabase;

    public function test_mailable_has_correct_subject(): void
    {
        $event = Event::factory()->create();
        $mailable = new CommunityFavoriteEventApproved($event);

        $envelope = $mailable->envelope();

        $this->assertStringContainsString('Nuovo evento pubblicato da', $envelope->subject);
        $this->assertStringContainsString($event->community->name, $envelope->subject);
    }

    public function test_mailable_uses_correct_view(): void
    {
        $event = Event::factory()->create();
        $mailable = new CommunityFavoriteEventApproved($event);

        $content = $mailable->content();

        $this->assertEquals('emails.events.community-favorite-approved', $content->view);
    }

    public function test_mailable_renders_successfully(): void
    {
        $event = Event::factory()->create();
        $mailable = new CommunityFavoriteEventApproved($event);

        $mailable->assertSeeInHtml($event->title);
    }
}
