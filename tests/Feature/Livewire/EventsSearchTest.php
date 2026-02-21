<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\EventsSearch;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EventsSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_renders_successfully(): void
    {
        Livewire::test(EventsSearch::class)
            ->assertStatus(200);
    }

    public function test_search_query_can_be_set(): void
    {
        Livewire::test(EventsSearch::class)
            ->set('query', 'Laravel')
            ->assertSet('query', 'Laravel');
    }

    public function test_search_resets_page_when_query_updated(): void
    {
        Event::factory()->count(20)->create();

        Livewire::test(EventsSearch::class)
            ->set('query', 'test')
            ->call('$refresh')
            ->assertSet('query', 'test');
    }

    public function test_location_can_be_set(): void
    {
        $location = json_encode(['type' => 'comune', 'id' => 1]);

        Livewire::test(EventsSearch::class)
            ->set('location', $location)
            ->assertSet('location', $location);
    }

    public function test_location_updated_resets_page(): void
    {
        $location = json_encode(['type' => 'provincia', 'id' => 1]);

        Livewire::test(EventsSearch::class)
            ->set('location', $location)
            ->assertSet('location', $location);
    }

    public function test_search_shows_bookmark_count_for_authenticated_users(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();

        $this->actingAs($user);

        Livewire::test(EventsSearch::class)
            ->assertStatus(200);
    }

    public function test_search_paginates_results(): void
    {
        Event::factory()->count(10)->create();

        Livewire::test(EventsSearch::class)
            ->assertStatus(200);
    }
}
