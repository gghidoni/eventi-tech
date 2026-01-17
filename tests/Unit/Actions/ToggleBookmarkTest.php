<?php

declare(strict_types=1);

use App\Actions\ToggleBookmark;
use App\Models\Event;
use App\Models\User;

beforeEach(function () {
    $this->action = new ToggleBookmark();
    $this->user = User::factory()->create();
});

test('adds bookmark to event', function () {
    $event = Event::factory()->create();

    $result = $this->action->execute($this->user, $event->id);

    expect($result)->toBeTrue();
    expect($this->user->bookmarks)->toHaveCount(1);
    expect($this->user->bookmarks->first()->id)->toBe($event->id);
});

test('removes existing bookmark from event', function () {
    $event = Event::factory()->create();

    // First add the bookmark
    $this->user->bookmarks()->attach($event->id);
    expect($this->user->bookmarks)->toHaveCount(1);

    // Now toggle it off
    $result = $this->action->execute($this->user, $event->id);

    expect($result)->toBeFalse();
    $this->user->refresh();
    expect($this->user->bookmarks)->toHaveCount(0);
});

test('toggles bookmark multiple times correctly', function () {
    $event = Event::factory()->create();

    // Toggle on
    $result1 = $this->action->execute($this->user, $event->id);
    expect($result1)->toBeTrue();

    // Toggle off
    $result2 = $this->action->execute($this->user, $event->id);
    expect($result2)->toBeFalse();

    // Toggle on again
    $result3 = $this->action->execute($this->user, $event->id);
    expect($result3)->toBeTrue();

    $this->user->refresh();
    expect($this->user->bookmarks)->toHaveCount(1);
});

test('throws exception for non-existent event', function () {
    $this->action->execute($this->user, 99999);
})->throws(InvalidArgumentException::class, 'Event not found');

test('user can bookmark multiple events', function () {
    $events = Event::factory()->count(3)->create();

    foreach ($events as $event) {
        $this->action->execute($this->user, $event->id);
    }

    $this->user->refresh();
    expect($this->user->bookmarks)->toHaveCount(3);
});
