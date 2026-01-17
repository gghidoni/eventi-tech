<?php

declare(strict_types=1);

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Models\AddressBook\AddressBook;
use App\Models\Community;
use App\Models\Event;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

describe('relationships', function () {
    test('belongs to a community', function () {
        $community = Community::factory()->create();
        $event = Event::factory()->forCommunity($community)->create();

        expect($event->community)->toBeInstanceOf(Community::class);
        expect($event->community->id)->toBe($community->id);
    });

    test('belongs to an address book', function () {
        $event = Event::factory()->withAddressBook()->create();

        expect($event->address_book)->toBeInstanceOf(AddressBook::class);
    });

    test('has many tags through pivot table', function () {
        $event = Event::factory()->create();
        $tags = Tag::factory()->count(3)->create();

        $event->tags()->attach($tags->pluck('id'));

        expect($event->tags)->toHaveCount(3);
        expect($event->tags->first())->toBeInstanceOf(Tag::class);
    });

    test('has many bookmarks through pivot table', function () {
        $event = Event::factory()->create();
        $users = User::factory()->count(2)->create();

        $event->bookmarks()->attach($users->pluck('id'));

        expect($event->bookmarks)->toHaveCount(2);
        expect($event->bookmarks->first())->toBeInstanceOf(User::class);
    });
});

describe('scopes', function () {
    test('active scope returns only active events', function () {
        Event::factory()->create(['status' => EventStatus::Pending]);
        Event::factory()->create(['status' => EventStatus::Active]);
        Event::factory()->create(['status' => EventStatus::Active]);
        Event::factory()->create(['status' => EventStatus::Terminate]);

        $activeEvents = Event::active()->get();

        expect($activeEvents)->toHaveCount(2);
        $activeEvents->each(fn ($event) => expect($event->status)->toBe(EventStatus::Active));
    });
});

describe('accessors', function () {
    test('formatted_start_date returns date in d/m/Y format', function () {
        $event = Event::factory()->create([
            'start_date' => '2025-06-15 10:00:00',
        ]);

        expect($event->formatted_start_date)->toBe('15/06/2025');
    });

    test('formatted_datetime_start returns date in d/m/Y H:i format', function () {
        $event = Event::factory()->create([
            'start_date' => '2025-06-15 14:30:00',
        ]);

        expect($event->formatted_datetime_start)->toBe('15/06/2025 14:30');
    });

    test('formatted_datetime_end returns date in d/m/Y H:i format', function () {
        $event = Event::factory()->create([
            'end_date' => '2025-06-15 18:00:00',
        ]);

        expect($event->formatted_datetime_end)->toBe('15/06/2025 18:00');
    });

    test('public_url returns correct URL', function () {
        $event = Event::factory()->create();

        expect($event->public_url)->toBe(url('/events/'.$event->id));
    });

    test('edit_url returns correct URL', function () {
        $event = Event::factory()->create();

        expect($event->edit_url)->toBe(url('/dashboard/events/'.$event->id.'/edit'));
    });

    test('is_mine returns true when event belongs to authenticated user', function () {
        $user = User::factory()->create();
        $community = Community::factory()->forUser($user)->create();
        $event = Event::factory()->forCommunity($community)->create();

        $this->actingAs($user);

        expect($event->is_mine)->toBeTrue();
    });

    test('is_mine returns false when event belongs to different user', function () {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $community = Community::factory()->forUser($owner)->create();
        $event = Event::factory()->forCommunity($community)->create();

        $this->actingAs($otherUser);

        expect($event->is_mine)->toBeFalse();
    });

    test('poster_img returns poster URL when poster exists', function () {
        Storage::fake('posters');
        $event = Event::factory()->create(['poster' => 'test-poster.webp']);

        $posterUrl = $event->poster_img;

        expect($posterUrl)->toContain('test-poster.webp');
    });

    test('poster_img returns placeholder when poster is null', function () {
        Storage::fake('posters');
        $event = Event::factory()->create(['poster' => null]);

        $posterUrl = $event->poster_img;

        expect($posterUrl)->toContain('placeholder-');
    });

    test('poster_mobile_img returns mobile poster URL when exists', function () {
        Storage::fake('posters');
        $event = Event::factory()->create(['poster_mobile' => 'test-mobile.webp']);

        $posterUrl = $event->poster_mobile_img;

        expect($posterUrl)->toContain('test-mobile.webp');
    });

    test('poster_thumb_img returns thumbnail URL when exists', function () {
        Storage::fake('posters');
        $event = Event::factory()->create(['poster_thumb' => 'test-thumb.webp']);

        $posterUrl = $event->poster_thumb_img;

        expect($posterUrl)->toContain('test-thumb.webp');
    });
});

describe('casts', function () {
    test('type is cast to EventType enum', function () {
        $event = Event::factory()->create(['type' => EventType::InPerson]);

        expect($event->type)->toBeInstanceOf(EventType::class);
        expect($event->type)->toBe(EventType::InPerson);
    });

    test('status is cast to EventStatus enum', function () {
        $event = Event::factory()->create(['status' => EventStatus::Active]);

        expect($event->status)->toBeInstanceOf(EventStatus::class);
        expect($event->status)->toBe(EventStatus::Active);
    });

    test('start_date is cast to datetime', function () {
        $event = Event::factory()->create(['start_date' => '2025-06-15 10:00:00']);

        expect($event->start_date)->toBeInstanceOf(DateTime::class);
    });

    test('end_date is cast to datetime', function () {
        $event = Event::factory()->create(['end_date' => '2025-06-15 18:00:00']);

        expect($event->end_date)->toBeInstanceOf(DateTime::class);
    });
});
