<?php

declare(strict_types=1);

use App\Enums\CommunityStatus;
use App\Models\Community;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

describe('relationships', function () {
    test('belongs to a user', function () {
        $user = User::factory()->create();
        $community = Community::factory()->forUser($user)->create();

        expect($community->user)->toBeInstanceOf(User::class);
        expect($community->user->id)->toBe($user->id);
    });

    test('has many events', function () {
        $community = Community::factory()->create();
        Event::factory()->count(3)->forCommunity($community)->create();

        expect($community->events)->toHaveCount(3);
        expect($community->events->first())->toBeInstanceOf(Event::class);
    });

    test('returns empty collection when no events', function () {
        $community = Community::factory()->create();

        expect($community->events)->toHaveCount(0);
    });

    test('belongs to many users that favorited it', function () {
        $community = Community::factory()->create();
        $users = User::factory()->count(2)->create();

        $community->favoritedByUsers()->attach($users->pluck('id'));

        expect($community->favoritedByUsers)->toHaveCount(2);
        expect($community->favoritedByUsers->first())->toBeInstanceOf(User::class);
    });
});

describe('accessors', function () {
    test('public_url returns correct URL', function () {
        $community = Community::factory()->create();

        expect($community->public_url)->toBe(url('/communities/'.$community->id));
    });

    test('edit_url returns correct URL', function () {
        $community = Community::factory()->create();

        expect($community->edit_url)->toBe(url('/dashboard/communities/'.$community->id.'/edit'));
    });

    test('is_mine returns true when authenticated user owns the community', function () {
        $user = User::factory()->create();
        $community = Community::factory()->forUser($user)->create();

        $this->actingAs($user);

        expect($community->is_mine)->toBeTrue();
    });

    test('is_mine returns false when authenticated user does not own the community', function () {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $community = Community::factory()->forUser($owner)->create();

        $this->actingAs($otherUser);

        expect($community->is_mine)->toBeFalse();
    });

    test('logo_img returns logo URL when logo exists', function () {
        Storage::fake('logos');
        $community = Community::factory()->create(['logo' => 'test-logo.webp']);

        $logoUrl = $community->logo_img;

        expect($logoUrl)->toContain('test-logo.webp');
    });

    test('logo_img returns ui-avatars fallback when logo is null', function () {
        $community = Community::factory()->create([
            'name' => 'Test Community',
            'logo' => null,
        ]);

        $logoUrl = $community->logo_img;

        expect($logoUrl)->toContain('ui-avatars.com');
        expect($logoUrl)->toContain(urlencode('Test Community'));
    });

    test('logo_img handles special characters in name for fallback', function () {
        $community = Community::factory()->create([
            'name' => 'Test & Community',
            'logo' => null,
        ]);

        $logoUrl = $community->logo_img;

        expect($logoUrl)->toContain('ui-avatars.com');
    });
});

describe('factory states', function () {
    test('creates pending community by default', function () {
        $community = Community::factory()->create();

        expect($community->status)->toBe(CommunityStatus::Pending);
    });

    test('active state sets status to active', function () {
        $community = Community::factory()->active()->create();

        expect($community->status)->toBe(CommunityStatus::Active);
    });

    test('rejected state sets status to rejected', function () {
        $community = Community::factory()->rejected()->create();

        expect($community->status)->toBe(CommunityStatus::Rejected);
    });

    test('forUser state associates with specific user', function () {
        $user = User::factory()->create();
        $community = Community::factory()->forUser($user)->create();

        expect($community->user_id)->toBe($user->id);
    });
});

describe('fillable attributes', function () {
    test('can mass assign all fillable attributes', function () {
        $user = User::factory()->create();
        $data = [
            'user_id'     => $user->id,
            'name'        => 'Test Community',
            'slug'        => 'test-community',
            'description' => 'Test description',
            'website'     => 'https://test.com',
            'logo'        => 'logo.webp',
            'linkedin'    => 'https://linkedin.com/test',
            'instagram'   => 'testinstagram',
            'facebook'    => 'https://facebook.com/test',
            'phone'       => '+39 02 1234567',
        ];

        $community = Community::create($data);

        expect($community->name)->toBe('Test Community');
        expect($community->slug)->toBe('test-community');
        expect($community->description)->toBe('Test description');
        expect($community->website)->toBe('https://test.com');
        expect($community->linkedin)->toBe('https://linkedin.com/test');
    });
});
