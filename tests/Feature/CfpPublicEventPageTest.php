<?php

declare(strict_types=1);

use App\Enums\CfpMode;
use App\Enums\CfpStatus;
use App\Enums\EventStatus;
use App\Models\Cfp;
use App\Models\Event;

test('event page shows published external cfp link', function () {
    $event = Event::factory()->active()->create();
    Cfp::factory()->for($event)->create([
        'mode'         => CfpMode::External,
        'status'       => CfpStatus::Published,
        'external_url' => 'https://example.com/cfp',
        'opens_at'     => now()->subDay(),
        'closes_at'    => now()->addWeek(),
    ]);

    $this->get(route('events.show', $event))
        ->assertOk()
        ->assertSee('CFP')
        ->assertSee('https://example.com/cfp');
});

test('event page hides draft external cfp link', function () {
    $event = Event::factory()->create(['status' => EventStatus::Active]);
    Cfp::factory()->for($event)->create([
        'mode'         => CfpMode::External,
        'status'       => CfpStatus::Draft,
        'external_url' => 'https://example.com/draft-cfp',
        'opens_at'     => now()->subDay(),
        'closes_at'    => now()->addWeek(),
    ]);

    $this->get(route('events.show', $event))
        ->assertOk()
        ->assertDontSee('https://example.com/draft-cfp');
});
