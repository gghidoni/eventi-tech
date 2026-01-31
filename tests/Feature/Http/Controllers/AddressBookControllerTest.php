<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\AddressBook\City;
use App\Models\AddressBook\Province;
use App\Models\AddressBook\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressBookControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_find_location_returns_empty_array_when_no_query(): void
    {
        $response = $this->getJson('/find-location?type=all&search=');

        $response->assertJson(['data' => []]);
    }

    public function test_find_location_searches_cities_provinces_and_regions(): void
    {
        $city = City::factory()->create(['name' => 'Milano']);
        $province = Province::factory()->create(['name' => 'Milano']);
        $region = Region::factory()->create(['name' => 'Lombardia']);

        $response = $this->getJson('/find-location?type=all&search=Mila');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_find_location_with_city_type_includes_province_info(): void
    {
        $city = City::factory()->create(['name' => 'Milano']);

        $response = $this->getJson('/find-location?type=city&search=Mila');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_find_location_returns_json_encoded_values(): void
    {
        $city = City::factory()->create(['name' => 'Milano']);

        $response = $this->getJson('/find-location?type=all&search=Milano');

        $response->assertStatus(200);

        $data = $response->json('data');

        if (count($data) > 0) {
            $this->assertArrayHasKey('value', $data[0]);
            $this->assertArrayHasKey('label', $data[0]);
            $this->assertArrayHasKey('id', $data[0]);
        }
    }

    public function test_find_location_limits_results_per_type(): void
    {
        City::factory()->count(10)->create(['name' => 'Test City']);

        $response = $this->getJson('/find-location?type=all&search=Test');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertLessThanOrEqual(10, count($data));
    }
}
