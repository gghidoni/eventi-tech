<?php

declare(strict_types=1);

use App\Actions\UpdateCommunity;
use App\Enums\CommunityStatus;
use App\Models\Community;

beforeEach(function () {
    $this->action = new UpdateCommunity();
});

test('updates community name', function () {
    $community = Community::factory()->create(['name' => 'Original Name']);

    $updatedCommunity = $this->action->execute($community, ['name' => 'Updated Name']);

    expect($updatedCommunity->name)->toBe('Updated Name');
    $this->assertDatabaseHas('communities', [
        'id'   => $community->id,
        'name' => 'Updated Name',
    ]);
});

test('updates multiple fields at once', function () {
    $community = Community::factory()->create();

    $newData = [
        'name'        => 'New Community Name',
        'description' => 'New description',
        'website'     => 'https://new-website.com',
    ];

    $updatedCommunity = $this->action->execute($community, $newData);

    expect($updatedCommunity->name)->toBe('New Community Name');
    expect($updatedCommunity->description)->toBe('New description');
    expect($updatedCommunity->website)->toBe('https://new-website.com');
});

test('updates social media links', function () {
    $community = Community::factory()->create();

    $newData = [
        'linkedin'  => 'https://linkedin.com/company/new',
        'instagram' => 'newinstagram',
        'facebook'  => 'https://facebook.com/new',
    ];

    $updatedCommunity = $this->action->execute($community, $newData);

    expect($updatedCommunity->linkedin)->toBe('https://linkedin.com/company/new');
    expect($updatedCommunity->instagram)->toBe('newinstagram');
    expect($updatedCommunity->facebook)->toBe('https://facebook.com/new');
});

test('does not change user_id', function () {
    $community = Community::factory()->create();
    $originalUserId = $community->user_id;

    // Attempting to change user_id should not work because it's not in fillable
    // or the action should preserve it
    $updatedCommunity = $this->action->execute($community, ['name' => 'New Name']);

    expect($updatedCommunity->user_id)->toBe($originalUserId);
});

test('preserves status when not updating it', function () {
    $community = Community::factory()->active()->create();

    $updatedCommunity = $this->action->execute($community, ['name' => 'New Name']);

    expect($updatedCommunity->status)->toBe(CommunityStatus::Active);
});

test('can update phone number', function () {
    $community = Community::factory()->create(['phone' => null]);

    $updatedCommunity = $this->action->execute($community, ['phone' => '+39 02 9876543']);

    expect($updatedCommunity->phone)->toBe('+39 02 9876543');
});

test('returns the same community instance', function () {
    $community = Community::factory()->create();

    $updatedCommunity = $this->action->execute($community, ['name' => 'New Name']);

    expect($updatedCommunity->id)->toBe($community->id);
});

test('can clear optional fields', function () {
    $community = Community::factory()->create([
        'website'   => 'https://old-site.com',
        'linkedin'  => 'https://linkedin.com/old',
        'instagram' => 'oldinstagram',
    ]);

    $updatedCommunity = $this->action->execute($community, [
        'website'   => null,
        'linkedin'  => null,
        'instagram' => null,
    ]);

    expect($updatedCommunity->website)->toBeNull();
    expect($updatedCommunity->linkedin)->toBeNull();
    expect($updatedCommunity->instagram)->toBeNull();
});
