<?php

declare(strict_types=1);

use App\Enums\CfpMode;
use App\Enums\CfpStatus;
use App\Models\Cfp;
use App\Models\CfpSubmission;
use App\Models\Community;
use App\Models\Event;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Livewire;

test('only active events of active communities are publicly visible', function () {
    $activeCommunity = Community::factory()->active()->create();
    $activeEvent = Event::factory()->active()->forCommunity($activeCommunity)->create();
    $pendingEvent = Event::factory()->forCommunity($activeCommunity)->create();
    $rejectedEvent = Event::factory()->rejected()->forCommunity($activeCommunity)->create();
    $terminatedEvent = Event::factory()->terminated()->forCommunity($activeCommunity)->create();
    $pendingCommunity = Community::factory()->create();
    $eventOfPendingCommunity = Event::factory()->active()->forCommunity($pendingCommunity)->create();

    $this->get(route('events.show', $activeEvent))->assertOk();
    $this->get(route('events.show', $pendingEvent))->assertNotFound();
    $this->get(route('events.show', $rejectedEvent))->assertNotFound();
    $this->get(route('events.show', $terminatedEvent))->assertNotFound();
    $this->get(route('events.show', $eventOfPendingCommunity))->assertNotFound();
});

test('owner and admin still receive not found for non public event route', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create(['is_admin' => true]);
    $community = Community::factory()->forUser($owner)->create();
    $event = Event::factory()->forCommunity($community)->create();

    $this->actingAs($owner)->get(route('events.show', $event))->assertNotFound();
    $this->actingAs($admin)->get(route('events.show', $event))->assertNotFound();
});

test('only active communities are publicly visible', function () {
    $active = Community::factory()->active()->create();
    $pending = Community::factory()->create();
    $rejected = Community::factory()->rejected()->create();

    $this->get(route('communities.show', $active))->assertOk();
    $this->get(route('communities.show', $pending))->assertNotFound();
    $this->get(route('communities.show', $rejected))->assertNotFound();
});

test('public community page hides events that are not public', function () {
    $community = Community::factory()->active()->create();
    $publicEvent = Event::factory()->active()->forCommunity($community)->create(['title' => 'Evento pubblico visibile']);
    $pendingEvent = Event::factory()->forCommunity($community)->create(['title' => 'Evento pending nascosto']);

    $this->get(route('communities.show', $community))
        ->assertOk()
        ->assertSee($publicEvent->title)
        ->assertDontSee($pendingEvent->title);
});

test('owner can edit active or pending resources but rejected resources are read only', function () {
    $owner = User::factory()->create();
    $activeCommunity = Community::factory()->active()->forUser($owner)->create([
        'website'   => 'https://example.com',
        'linkedin'  => 'https://linkedin.com/company/example',
        'instagram' => 'https://instagram.com/example',
        'facebook'  => 'https://facebook.com/example',
        'phone'     => '+3900000000',
    ]);
    $rejectedCommunity = Community::factory()->rejected()->forUser($owner)->create();
    $pendingEvent = Event::factory()->forCommunity($activeCommunity)->create();
    $rejectedEvent = Event::factory()->rejected()->forCommunity($activeCommunity)->create();
    $terminatedEvent = Event::factory()->terminated()->forCommunity($activeCommunity)->create();

    $this->actingAs($owner)->get(route('dashboard.communities.edit', $activeCommunity))->assertOk();
    $this->actingAs($owner)->get(route('dashboard.communities.edit', $rejectedCommunity))->assertForbidden();
    $this->actingAs($owner)->get(route('dashboard.events.edit', $pendingEvent))->assertOk();
    $this->actingAs($owner)->get(route('dashboard.events.edit', $rejectedEvent))->assertForbidden();
    $this->actingAs($owner)->get(route('dashboard.events.edit', $terminatedEvent))->assertForbidden();
});

test('verified user can create events only when owning an active community', function () {
    $activeOwner = User::factory()->create();
    Community::factory()->active()->forUser($activeOwner)->create();
    $pendingOwner = User::factory()->create();
    Community::factory()->forUser($pendingOwner)->create();

    $this->actingAs($activeOwner)->get(route('dashboard.events.create'))->assertOk();
    $this->actingAs($pendingOwner)->get(route('dashboard.events.create'))->assertForbidden();
});

test('tampered Livewire community selection is rejected before event validation', function () {
    $owner = User::factory()->create();
    Community::factory()->active()->forUser($owner)->create();
    $otherCommunity = Community::factory()->active()->create();

    Livewire::actingAs($owner)
        ->test('pages::dashboard.events.create')
        ->set('selectedCommunity', (string) $otherCommunity->id)
        ->call('save')
        ->assertForbidden();
});

test('tampered dashboard community selection cannot expose events of another owner', function () {
    $owner = User::factory()->create();
    Community::factory()->active()->forUser($owner)->create();
    $otherCommunity = Community::factory()->active()->create();
    Event::factory()->active()->forCommunity($otherCommunity)->create();

    Livewire::actingAs($owner)
        ->test('pages::dashboard.communities.events')
        ->set('selectedCommunity', (string) $otherCommunity->id)
        ->assertForbidden();
});

test('only verified admins can access Filament panel', function () {
    $panel = Filament::getPanel('admin');
    $user = User::factory()->create();
    $unverifiedAdmin = User::factory()->unverified()->create(['is_admin' => true]);
    $verifiedAdmin = User::factory()->create(['is_admin' => true]);

    expect($user->canAccessPanel($panel))->toBeFalse()
        ->and($unverifiedAdmin->canAccessPanel($panel))->toBeFalse()
        ->and($verifiedAdmin->canAccessPanel($panel))->toBeTrue();

    $this->actingAs($user)->get('/admin')->assertForbidden();
    $this->actingAs($unverifiedAdmin)->get('/admin')->assertForbidden();
    $this->actingAs($verifiedAdmin)->get('/admin')->assertOk();
});

test('CFP apply requires a public event and rejects its owner', function () {
    $owner = User::factory()->create();
    $speaker = User::factory()->create();
    $community = Community::factory()->active()->forUser($owner)->create();
    $event = Event::factory()->active()->forCommunity($community)->create();
    $cfp = Cfp::factory()->for($event)->create([
        'mode'      => CfpMode::Internal,
        'status'    => CfpStatus::Published,
        'opens_at'  => now()->subDay(),
        'closes_at' => now()->addDay(),
    ]);

    $this->actingAs($speaker)->get(route('events.cfp.apply', $event))->assertOk();
    $this->actingAs($owner)->get(route('events.cfp.apply', $event))->assertForbidden();

    $community->forceFill(['status' => 'pending'])->save();
    $this->actingAs($speaker)->get(route('events.cfp.apply', $event))->assertForbidden();
});

test('CFP submission route rejects aggregate mismatch before review authorization', function () {
    $owner = User::factory()->create();
    $community = Community::factory()->active()->forUser($owner)->create();
    $firstEvent = Event::factory()->active()->forCommunity($community)->create();
    $secondEvent = Event::factory()->active()->forCommunity($community)->create();
    $firstCfp = Cfp::factory()->for($firstEvent)->create();
    $secondCfp = Cfp::factory()->for($secondEvent)->create();
    $submission = CfpSubmission::factory()->for($secondCfp)->create();

    Livewire::actingAs($owner)
        ->test('pages::dashboard.cfps.submission', [
            'cfp'        => $firstCfp,
            'submission' => $submission,
        ])
        ->assertNotFound();
});

test('bookmark and favorite actions reject non public or owned resources', function () {
    $owner = User::factory()->create();
    $community = Community::factory()->active()->forUser($owner)->create();
    $event = Event::factory()->active()->forCommunity($community)->create();

    expect(fn () => app(App\Actions\ToggleBookmark::class)->execute($owner, $event->id))
        ->toThrow(AuthorizationException::class);
    expect(fn () => app(App\Actions\ToggleCommunityFavorite::class)->execute($owner, $community->id))
        ->toThrow(AuthorizationException::class);
});
