<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_find_tags_returns_empty_array_when_no_search_and_no_selected(): void
    {
        $response = $this->getJson('/find-tags?search=');

        $response->assertStatus(200)
            ->assertJson(['data' => []]);
    }

    public function test_find_tags_searches_by_name_and_returns_expected_structure(): void
    {
        Tag::factory()->create(['name' => 'Laravel', 'icon' => 'laravel']);
        Tag::factory()->create(['name' => 'Python', 'icon' => 'python']);

        $response = $this->getJson('/find-tags?search=Lar');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['value', 'label', 'icon', 'badge_color', 'label_color'],
                ],
            ]);
    }

    public function test_find_tags_can_resolve_selected_values(): void
    {
        $tagA = Tag::factory()->create(['name' => 'Docker', 'icon' => 'docker']);
        $tagB = Tag::factory()->create(['name' => 'PHP', 'icon' => 'php']);

        $response = $this->getJson("/find-tags?selected={$tagA->id},{$tagB->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}
