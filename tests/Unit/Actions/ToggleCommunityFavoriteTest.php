<?php

declare(strict_types=1);

use App\Actions\ToggleCommunityFavorite;
use App\Models\Community;
use App\Models\User;

beforeEach(function () {
    $this->action = new ToggleCommunityFavorite();
    $this->user = User::factory()->create();
});

test('adds favorite to community', function () {
    $community = Community::factory()->create();

    $result = $this->action->execute($this->user, $community->id);

    expect($result)->toBeTrue();
    expect($this->user->favoriteCommunities)->toHaveCount(1);
    expect($this->user->favoriteCommunities->first()->id)->toBe($community->id);
});

test('removes existing favorite from community', function () {
    $community = Community::factory()->create();

    $this->user->favoriteCommunities()->attach($community->id);
    expect($this->user->favoriteCommunities)->toHaveCount(1);

    $result = $this->action->execute($this->user, $community->id);

    expect($result)->toBeFalse();
    $this->user->refresh();
    expect($this->user->favoriteCommunities)->toHaveCount(0);
});

test('user can favorite multiple communities', function () {
    $communities = Community::factory()->count(3)->create();

    foreach ($communities as $community) {
        $this->action->execute($this->user, $community->id);
    }

    $this->user->refresh();
    expect($this->user->favoriteCommunities)->toHaveCount(3);
});

test('throws exception for non-existent community', function () {
    $this->action->execute($this->user, 99999);
})->throws(InvalidArgumentException::class, 'Community not found');
