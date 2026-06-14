<?php

declare(strict_types=1);

use App\Actions\UpdateEvent;
use App\Enums\CfpMode;
use App\Enums\CfpStatus;
use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Models\Cfp;
use App\Models\Event;

beforeEach(function () {
    $this->action = new UpdateEvent();
});

test('updates event title', function () {
    $event = Event::factory()->create(['title' => 'Original Title']);

    $updatedEvent = $this->action->execute($event, ['title' => 'Updated Title']);

    expect($updatedEvent->title)->toBe('Updated Title');
    $this->assertDatabaseHas('events', [
        'id'    => $event->id,
        'title' => 'Updated Title',
    ]);
});

test('updates multiple fields at once', function () {
    $event = Event::factory()->create();

    $newData = [
        'title'       => 'New Conference',
        'description' => 'New description for the event',
        'website'     => 'https://new-website.com',
    ];

    $updatedEvent = $this->action->execute($event, $newData);

    expect($updatedEvent->title)->toBe('New Conference');
    expect($updatedEvent->description)->toBe('New description for the event');
    expect($updatedEvent->website)->toBe('https://new-website.com');
});

test('updates event type', function () {
    $event = Event::factory()->inPerson()->create();

    $updatedEvent = $this->action->execute($event, ['type' => EventType::Online]);

    expect($updatedEvent->type)->toBe(EventType::Online);
});

test('preserves status when not updating it', function () {
    $event = Event::factory()->active()->create();

    $updatedEvent = $this->action->execute($event, ['title' => 'New Title']);

    expect($updatedEvent->status)->toBe(EventStatus::Active);
});

test('updates event dates', function () {
    $event = Event::factory()->create();
    $newStartDate = now()->addMonth();
    $newEndDate = now()->addMonth()->addHours(4);

    $updatedEvent = $this->action->execute($event, [
        'start_date' => $newStartDate,
        'end_date'   => $newEndDate,
    ]);

    expect($updatedEvent->start_date->format('Y-m-d H:i'))
        ->toBe($newStartDate->format('Y-m-d H:i'));
    expect($updatedEvent->end_date->format('Y-m-d H:i'))
        ->toBe($newEndDate->format('Y-m-d H:i'));
});

test('returns the same event instance', function () {
    $event = Event::factory()->create();

    $updatedEvent = $this->action->execute($event, ['title' => 'New Title']);

    expect($updatedEvent->id)->toBe($event->id);
});

test('can update status', function () {
    $event = Event::factory()->create(['status' => EventStatus::Pending]);

    $updatedEvent = $this->action->execute($event, ['status' => EventStatus::Active]);

    expect($updatedEvent->status)->toBe(EventStatus::Active);
});

test('creates or updates external cfp payload', function () {
    $event = Event::factory()->create();

    $this->action->execute($event, [
        'cfp' => [
            'mode'         => CfpMode::External,
            'status'       => CfpStatus::Published,
            'title'        => 'CFP - New Conference',
            'opens_at'     => now(),
            'closes_at'    => now()->addMonth(),
            'external_url' => 'https://example.com/first-cfp',
        ],
    ]);

    expect($event->fresh()->cfp->external_url)->toBe('https://example.com/first-cfp');

    $this->action->execute($event, [
        'cfp' => [
            'mode'         => CfpMode::External,
            'status'       => CfpStatus::Draft,
            'title'        => 'CFP - New Conference',
            'opens_at'     => now(),
            'closes_at'    => now()->addMonth(),
            'external_url' => 'https://example.com/updated-cfp',
        ],
    ]);

    $event->refresh();

    expect($event->cfp->external_url)->toBe('https://example.com/updated-cfp');
    expect($event->cfp->status)->toBe(CfpStatus::Draft);
});

test('removes cfp when payload is null', function () {
    $event = Event::factory()->create();
    Cfp::factory()->for($event)->create();

    $this->action->execute($event, ['cfp' => null]);

    $this->assertDatabaseMissing('cfps', [
        'event_id' => $event->id,
    ]);
});
