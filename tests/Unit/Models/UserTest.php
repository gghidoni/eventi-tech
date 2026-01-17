<?php

declare(strict_types=1);

use App\Enums\CommunityStatus;
use App\Models\Community;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

describe('relationships', function () {
    test('has many communities', function () {
        $user = User::factory()->create();
        Community::factory()->count(3)->forUser($user)->create();

        expect($user->communities)->toHaveCount(3);
        expect($user->communities->first())->toBeInstanceOf(Community::class);
    });

    test('returns empty collection when no communities', function () {
        $user = User::factory()->create();

        expect($user->communities)->toHaveCount(0);
    });

    test('has many bookmarked events through pivot table', function () {
        $user = User::factory()->create();
        $events = Event::factory()->count(3)->create();

        $user->bookmarks()->attach($events->pluck('id'));

        expect($user->bookmarks)->toHaveCount(3);
        expect($user->bookmarks->first())->toBeInstanceOf(Event::class);
    });

    test('returns empty collection when no bookmarks', function () {
        $user = User::factory()->create();

        expect($user->bookmarks)->toHaveCount(0);
    });
});

describe('accessors', function () {
    test('has_active_community returns true when user has active community', function () {
        $user = User::factory()->create();
        Community::factory()->forUser($user)->active()->create();

        expect($user->has_active_community)->toBeTrue();
    });

    test('has_active_community returns false when user has no communities', function () {
        $user = User::factory()->create();

        expect($user->has_active_community)->toBeFalse();
    });

    test('has_active_community returns false when user only has pending communities', function () {
        $user = User::factory()->create();
        Community::factory()->forUser($user)->create(['status' => CommunityStatus::Pending]);

        expect($user->has_active_community)->toBeFalse();
    });

    test('has_active_community returns false when user only has rejected communities', function () {
        $user = User::factory()->create();
        Community::factory()->forUser($user)->rejected()->create();

        expect($user->has_active_community)->toBeFalse();
    });

    test('has_active_community returns true when user has mix of community statuses including active', function () {
        $user = User::factory()->create();
        Community::factory()->forUser($user)->create(['status' => CommunityStatus::Pending]);
        Community::factory()->forUser($user)->active()->create();
        Community::factory()->forUser($user)->rejected()->create();

        expect($user->has_active_community)->toBeTrue();
    });

    test('avatar_img returns avatar URL when avatar exists', function () {
        Storage::fake('public');
        $user = User::factory()->create(['avatar' => 'avatars/test-avatar.webp']);

        $avatarUrl = $user->avatar_img;

        expect($avatarUrl)->toContain('test-avatar.webp');
    });

    test('avatar_img returns ui-avatars fallback when avatar is null', function () {
        $user = User::factory()->create([
            'name'   => 'John Doe',
            'avatar' => null,
        ]);

        $avatarUrl = $user->avatar_img;

        expect($avatarUrl)->toContain('ui-avatars.com');
        expect($avatarUrl)->toContain(urlencode('John Doe'));
    });

    test('avatar_img handles special characters in name for fallback', function () {
        $user = User::factory()->create([
            'name'   => 'John & Jane',
            'avatar' => null,
        ]);

        $avatarUrl = $user->avatar_img;

        expect($avatarUrl)->toContain('ui-avatars.com');
    });
});

describe('factory states', function () {
    test('creates verified user by default', function () {
        $user = User::factory()->create();

        expect($user->email_verified_at)->not->toBeNull();
    });

    test('unverified state removes email verification', function () {
        $user = User::factory()->unverified()->create();

        expect($user->email_verified_at)->toBeNull();
    });

    test('withoutTwoFactor state removes 2FA fields', function () {
        $user = User::factory()->withoutTwoFactor()->create();

        expect($user->two_factor_secret)->toBeNull();
        expect($user->two_factor_recovery_codes)->toBeNull();
        expect($user->two_factor_confirmed_at)->toBeNull();
    });
});

describe('casts', function () {
    test('email_verified_at is cast to datetime', function () {
        $user = User::factory()->create();

        expect($user->email_verified_at)->toBeInstanceOf(DateTime::class);
    });

    test('password is automatically hashed', function () {
        $user = User::factory()->create(['password' => 'plaintext']);

        // Password should be hashed, not plaintext
        expect($user->password)->not->toBe('plaintext');
        expect(mb_strlen($user->password))->toBeGreaterThan(20);
    });
});

describe('hidden attributes', function () {
    test('password is hidden from serialization', function () {
        $user = User::factory()->create();
        $array = $user->toArray();

        expect($array)->not->toHaveKey('password');
    });

    test('remember_token is hidden from serialization', function () {
        $user = User::factory()->create();
        $array = $user->toArray();

        expect($array)->not->toHaveKey('remember_token');
    });
});

describe('fillable attributes', function () {
    test('can mass assign fillable attributes', function () {
        $data = [
            'name'      => 'Test User',
            'email'     => 'test@example.com',
            'password'  => 'password123',
            'avatar'    => 'avatars/avatar.jpg',
            'website'   => 'https://test.com',
            'facebook'  => 'https://facebook.com/test',
            'linkedin'  => 'https://linkedin.com/test',
            'instagram' => 'testuser',
            'is_admin'  => true,
        ];

        $user = User::create($data);

        expect($user->name)->toBe('Test User');
        expect($user->email)->toBe('test@example.com');
        expect($user->website)->toBe('https://test.com');
        expect($user->is_admin)->toBeTrue();
    });
});
