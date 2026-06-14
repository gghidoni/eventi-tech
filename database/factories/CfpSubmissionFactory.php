<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CfpSubmissionStatus;
use App\Models\Cfp;
use App\Models\CfpSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CfpSubmission>
 */
class CfpSubmissionFactory extends Factory
{
    protected $model = CfpSubmission::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cfp_id'       => Cfp::factory()->internal(),
            'user_id'      => User::factory(),
            'title'        => fake()->sentence(6),
            'abstract'     => fake()->paragraphs(2, true),
            'status'       => CfpSubmissionStatus::Draft,
            'submitted_at' => null,
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'       => CfpSubmissionStatus::Submitted,
            'submitted_at' => now(),
        ]);
    }
}
