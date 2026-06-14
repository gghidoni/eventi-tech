<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CfpMode;
use App\Enums\CfpStatus;
use App\Models\Cfp;
use App\Models\CfpTemplate;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cfp>
 */
class CfpFactory extends Factory
{
    protected $model = Cfp::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $opensAt = fake()->dateTimeBetween('+1 day', '+2 weeks');
        $closesAt = (clone $opensAt)->modify('+'.fake()->numberBetween(7, 45).' days');

        return [
            'event_id'        => Event::factory(),
            'cfp_template_id' => null,
            'mode'            => CfpMode::External,
            'status'          => CfpStatus::Draft,
            'title'           => fake()->sentence(4),
            'description'     => fake()->optional()->paragraph(),
            'opens_at'        => $opensAt,
            'closes_at'       => $closesAt,
            'external_url'    => fake()->url(),
        ];
    }

    public function internal(): static
    {
        return $this->state(fn (array $attributes) => [
            'mode'         => CfpMode::Internal,
            'external_url' => null,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CfpStatus::Published,
        ]);
    }

    public function forTemplate(CfpTemplate $template): static
    {
        return $this->state(fn (array $attributes) => [
            'cfp_template_id' => $template->id,
        ]);
    }
}
