<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => $name,
            'slug' => str()->slug($name),
            // Default coerenti con i vincoli della tabella tags.
            'icon'        => 'code',
            'badge_color' => fake()->hexColor(),
            'label_color' => '#FFFFFF',
        ];
    }
}
