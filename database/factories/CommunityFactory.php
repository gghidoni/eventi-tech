<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CommunityStatus;
use App\Models\Community;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Community>
 */
class CommunityFactory extends Factory
{
    protected $model = Community::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'user_id'     => User::factory(),
            'name'        => $name,
            'slug'        => str()->slug($name),
            'status'      => CommunityStatus::Pending,
            'description' => fake()->paragraph(),
            'website'     => fake()->optional()->url(),
            'logo'        => null,
            'linkedin'    => fake()->optional()->url(),
            'instagram'   => fake()->optional()->userName(),
            'facebook'    => fake()->optional()->url(),
            'phone'       => fake()->optional()->phoneNumber(),
        ];
    }

    /**
     * Set the community status to active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CommunityStatus::Active,
        ]);
    }

    /**
     * Set the community status to rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CommunityStatus::Rejected,
        ]);
    }

    /**
     * Set the owner user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
