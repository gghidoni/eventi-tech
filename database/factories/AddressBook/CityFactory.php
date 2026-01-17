<?php

declare(strict_types=1);

namespace Database\Factories\AddressBook;

use App\Models\AddressBook\City;
use App\Models\AddressBook\Province;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<City>
 */
class CityFactory extends Factory
{
    protected $model = City::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'        => fake()->city(),
            'cap'         => fake()->postcode(),
            'province_id' => Province::factory(),
        ];
    }

    /**
     * Set the province for the city.
     */
    public function forProvince(Province $province): static
    {
        return $this->state(fn (array $attributes) => [
            'province_id' => $province->id,
        ]);
    }
}
