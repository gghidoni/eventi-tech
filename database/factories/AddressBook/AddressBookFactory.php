<?php

declare(strict_types=1);

namespace Database\Factories\AddressBook;

use App\Models\AddressBook\AddressBook;
use App\Models\AddressBook\City;
use App\Models\AddressBook\Province;
use App\Models\AddressBook\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AddressBook>
 */
class AddressBookFactory extends Factory
{
    protected $model = AddressBook::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $region = Region::factory()->create();
        $province = Province::factory()->forRegion($region)->create();
        $city = City::factory()->forProvince($province)->create();

        return [
            'address_line' => fake()->streetAddress(),
            'city_id'      => $city->id,
            'province_id'  => $province->id,
            'region_id'    => $region->id,
        ];
    }

    /**
     * Create an address book with a specific city (auto-populates province and region).
     */
    public function forCity(City $city): static
    {
        return $this->state(fn (array $attributes) => [
            'city_id'     => $city->id,
            'province_id' => $city->province->id,
            'region_id'   => $city->province->region->id,
        ]);
    }
}
