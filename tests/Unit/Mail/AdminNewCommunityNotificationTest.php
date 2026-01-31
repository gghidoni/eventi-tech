<?php

declare(strict_types=1);

namespace Tests\Unit\Mail;

use App\Mail\AdminNewCommunityNotification;
use App\Models\Community;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNewCommunityNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mailable_has_correct_subject(): void
    {
        $community = Community::factory()->create();
        $mailable = new AdminNewCommunityNotification($community);

        $envelope = $mailable->envelope();

        $this->assertEquals('Nuova community da approvare', $envelope->subject);
    }

    public function test_mailable_uses_correct_view(): void
    {
        $community = Community::factory()->create();
        $mailable = new AdminNewCommunityNotification($community);

        $content = $mailable->content();

        $this->assertEquals('emails.admin.new-community', $content->view);
    }

    public function test_mailable_passes_community_to_view(): void
    {
        $community = Community::factory()->create(['name' => 'Community to Approve']);
        $mailable = new AdminNewCommunityNotification($community);

        $content = $mailable->content();

        $this->assertArrayHasKey('community', $content->with);
        $this->assertEquals($community->id, $content->with['community']->id);
    }

    public function test_mailable_renders_successfully(): void
    {
        $community = Community::factory()->create();
        $mailable = new AdminNewCommunityNotification($community);

        $mailable->assertSeeInHtml($community->name);
    }
}
