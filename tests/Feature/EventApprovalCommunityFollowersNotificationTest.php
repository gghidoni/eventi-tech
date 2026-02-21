<?php

declare(strict_types=1);

use App\Enums\EventStatus;
use App\Mail\CommunityFavoriteEventApproved;
use App\Models\Community;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('sends email to followers when event becomes active', function () {
    Mail::fake();

    $community = Community::factory()->create();
    $event = Event::factory()->forCommunity($community)->create([
        'status' => EventStatus::Pending,
    ]);

    $firstFollower = User::factory()->create();
    $secondFollower = User::factory()->create();
    $notFollower = User::factory()->create();

    $firstFollower->favoriteCommunities()->attach($community->id);
    $secondFollower->favoriteCommunities()->attach($community->id);

    $event->update(['status' => EventStatus::Active]);

    Mail::assertSent(CommunityFavoriteEventApproved::class, 2);

    Mail::assertSent(CommunityFavoriteEventApproved::class, function (CommunityFavoriteEventApproved $mail) use ($firstFollower, $event): bool {
        return $mail->hasTo($firstFollower->email) && $mail->event->is($event);
    });

    Mail::assertSent(CommunityFavoriteEventApproved::class, function (CommunityFavoriteEventApproved $mail) use ($secondFollower, $event): bool {
        return $mail->hasTo($secondFollower->email) && $mail->event->is($event);
    });

    Mail::assertNotSent(CommunityFavoriteEventApproved::class, function (CommunityFavoriteEventApproved $mail) use ($notFollower): bool {
        return $mail->hasTo($notFollower->email);
    });
});

test('does not send email when event status remains active', function () {
    Mail::fake();

    $community = Community::factory()->create();
    $event = Event::factory()->forCommunity($community)->create([
        'status' => EventStatus::Active,
    ]);

    $follower = User::factory()->create();
    $follower->favoriteCommunities()->attach($community->id);

    $event->update(['title' => 'Titolo aggiornato']);

    Mail::assertNothingSent();
});
