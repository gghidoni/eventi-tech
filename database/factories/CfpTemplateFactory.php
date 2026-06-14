<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CfpTemplate;
use App\Models\Community;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CfpTemplate>
 */
class CfpTemplateFactory extends Factory
{
    protected $model = CfpTemplate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'community_id' => Community::factory(),
            'title'        => fake()->sentence(3),
            'description'  => fake()->optional()->paragraph(),
        ];
    }
}
