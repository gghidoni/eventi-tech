<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Select\Location;
use App\Models\AddressBook\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LocationSelectTest extends TestCase
{
    use RefreshDatabase;

    public function test_city_select_loads_options_from_route_endpoint(): void
    {
        $city = City::factory()->create(['name' => 'Milano']);

        $component = Livewire::test(Location::class, [
            'endpoint'    => route('find'),
            'extraParams' => ['type' => 'city'],
        ])->set('search', 'Mila');

        $label = $city->name.' ('.$city->province->code.'), '.$city->province->region->name;
        $option = collect($component->get('displayOptions'))->firstWhere('label', $label);

        expect($option)->not->toBeNull()
            ->and(json_decode($option['value'], true))->toMatchArray([
                'type' => 'comune',
                'id'   => $city->id,
                'name' => $label,
            ]);
    }
}
