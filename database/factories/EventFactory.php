<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Models\AddressBook\AddressBook;
use App\Models\Community;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('+1 week', '+3 months');
        $endDate = (clone $startDate)->modify('+'.fake()->numberBetween(1, 8).' hours');

        return [
            'community_id'    => Community::factory(),
            'title'           => fake()->sentence(4),
            'status'          => EventStatus::Pending,
            'description'     => fake()->paragraphs(3, true),
            'type'            => fake()->randomElement(EventType::cases()),
            'address_book_id' => null,
            'start_date'      => $startDate,
            'end_date'        => $endDate,
            'website'         => fake()->optional()->url(),
            'poster'          => null,
            'poster_mobile'   => null,
            'poster_thumb'    => null,
            'tickets_url'     => fake()->optional()->url(),
        ];
    }

    /**
     * Set the event status to active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::Active,
        ]);
    }

    /**
     * Set the event status to terminated.
     */
    public function terminated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::Terminate,
        ]);
    }

    /**
     * Set the event status to rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::Reject,
        ]);
    }

    /**
     * Set the event type to in-person.
     */
    public function inPerson(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => EventType::InPerson,
        ]);
    }

    /**
     * Set the event type to online.
     */
    public function online(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => EventType::Online,
        ]);
    }

    /**
     * Set the event type to hybrid.
     */
    public function hybrid(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => EventType::Hybrid,
        ]);
    }

    /**
     * Set the community for the event.
     */
    public function forCommunity(Community $community): static
    {
        return $this->state(fn (array $attributes) => [
            'community_id' => $community->id,
        ]);
    }

    /**
     * Set the address book for the event.
     */
    public function withAddressBook(?AddressBook $addressBook = null): static
    {
        return $this->state(fn (array $attributes) => [
            'address_book_id' => $addressBook?->id ?? AddressBook::factory(),
        ]);
    }

    /**
     * Set a past event.
     */
    public function past(): static
    {
        $startDate = fake()->dateTimeBetween('-3 months', '-1 week');
        $endDate = (clone $startDate)->modify('+'.fake()->numberBetween(1, 8).' hours');

        return $this->state(fn (array $attributes) => [
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ]);
    }
}
