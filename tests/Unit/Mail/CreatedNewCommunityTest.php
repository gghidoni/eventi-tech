<?php

declare(strict_types=1);

namespace Tests\Unit\Mail;

use App\Mail\CreatedNewCommunity;
use App\Models\Community;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreatedNewCommunityTest extends TestCase
{
    use RefreshDatabase;

    public function test_mailable_has_correct_subject(): void
    {
        $community = Community::factory()->create();
        $mailable = new CreatedNewCommunity($community);

        $envelope = $mailable->envelope();

        $this->assertStringContainsString('Complimenti', $envelope->subject);
        $this->assertStringContainsString('community', $envelope->subject);
        $this->assertStringContainsString('creata', $envelope->subject);
    }

    public function test_mailable_uses_correct_view(): void
    {
        $community = Community::factory()->create();
        $mailable = new CreatedNewCommunity($community);

        $content = $mailable->content();

        $this->assertEquals('emails.communities.created', $content->view);
    }

    public function test_mailable_passes_community_to_view(): void
    {
        $community = Community::factory()->create(['name' => 'Test Community']);
        $mailable = new CreatedNewCommunity($community);

        $content = $mailable->content();

        $this->assertArrayHasKey('community', $content->with);
        $this->assertEquals($community->id, $content->with['community']->id);
    }

    public function test_mailable_renders_successfully(): void
    {
        $community = Community::factory()->create();
        $mailable = new CreatedNewCommunity($community);

        $mailable->assertSeeInHtml($community->name);
    }
}
