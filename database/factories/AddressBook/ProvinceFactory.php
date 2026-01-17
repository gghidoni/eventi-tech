<?php

declare(strict_types=1);

namespace Database\Factories\AddressBook;

use App\Models\AddressBook\Province;
use App\Models\AddressBook\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Province>
 */
class ProvinceFactory extends Factory
{
    protected $model = Province::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'      => fake()->city(),
            'code'      => fake()->unique()->lexify('??'),
            'region_id' => Region::factory(),
        ];
    }

    /**
     * Set the region for the province.
     */
    public function forRegion(Region $region): static
    {
        return $this->state(fn (array $attributes) => [
            'region_id' => $region->id,
        ]);
    }
}
