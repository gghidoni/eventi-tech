<?php

declare(strict_types=1);

use App\Actions\CreateCommunity;
use App\Enums\CommunityStatus;
use App\Models\Community;
use App\Models\User;

beforeEach(function () {
    $this->action = new CreateCommunity();
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('creates community with valid data', function () {
    $data = [
        'name'        => 'Laravel Italia',
        'description' => 'Community italiana di Laravel',
        'website'     => 'https://laravel-italia.it',
    ];

    $community = $this->action->execute($data);

    expect($community)->toBeInstanceOf(Community::class);
    expect($community->name)->toBe('Laravel Italia');
    expect($community->description)->toBe('Community italiana di Laravel');
    expect($community->website)->toBe('https://laravel-italia.it');
});

test('sets status to pending by default', function () {
    $data = [
        'name'        => 'New Community',
        'description' => 'A new tech community',
    ];

    $community = $this->action->execute($data);

    // Note: status is set via database default since it's not in $fillable
    // We verify it's saved correctly in the database
    $this->assertDatabaseHas('communities', [
        'id'     => $community->id,
        'status' => CommunityStatus::Pending->value,
    ]);
});

test('generates slug automatically from name', function () {
    $data = [
        'name'        => 'PHP User Group Milano',
        'description' => 'PHP community in Milan',
    ];

    $community = $this->action->execute($data);

    expect($community->slug)->toBe('php-user-group-milano');
});

test('associates community with authenticated user', function () {
    $data = [
        'name'        => 'Test Community',
        'description' => 'Test description',
    ];

    $community = $this->action->execute($data);

    expect($community->user_id)->toBe($this->user->id);
    expect($community->user->id)->toBe($this->user->id);
});

test('creates community with all optional fields', function () {
    $data = [
        'name'        => 'Full Community',
        'description' => 'Description',
        'website'     => 'https://example.com',
        'linkedin'    => 'https://linkedin.com/company/full',
        'instagram'   => 'fullcommunity',
        'facebook'    => 'https://facebook.com/full',
        'phone'       => '+39 02 1234567',
    ];

    $community = $this->action->execute($data);

    expect($community->website)->toBe('https://example.com');
    expect($community->linkedin)->toBe('https://linkedin.com/company/full');
    expect($community->instagram)->toBe('fullcommunity');
    expect($community->facebook)->toBe('https://facebook.com/full');
    expect($community->phone)->toBe('+39 02 1234567');
});

test('saves community to database', function () {
    $data = [
        'name'        => 'Saved Community',
        'description' => 'Should be saved',
    ];

    $community = $this->action->execute($data);

    $this->assertDatabaseHas('communities', [
        'id'          => $community->id,
        'name'        => 'Saved Community',
        'slug'        => 'saved-community',
        'status'      => CommunityStatus::Pending->value,
        'user_id'     => $this->user->id,
    ]);
});

test('handles special characters in name for slug', function () {
    $data = [
        'name'        => 'Test & Community (2024)',
        'description' => 'Test',
    ];

    $community = $this->action->execute($data);

    expect($community->slug)->toBe('test-community-2024');
});
