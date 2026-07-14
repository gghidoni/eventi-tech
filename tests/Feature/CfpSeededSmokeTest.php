<?php

declare(strict_types=1);

use App\Enums\CfpMode;
use App\Enums\CfpStatus;
use App\Enums\EventType;
use App\Models\Cfp;
use App\Models\CfpSubmission;
use App\Models\CfpTemplate;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\AddressBookSeeder;
use Database\Seeders\AddressSeeder;
use Database\Seeders\CfpSeeder;
use Database\Seeders\CommunitySeeder;
use Database\Seeders\EventSeeder;
use Database\Seeders\EventTagSeeder;
use Database\Seeders\TagSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

test('seeded cfp data covers templates submissions visibility and dashboard writes', function () {
    ini_set('memory_limit', '512M');

    Mail::fake();
    $this->seed([
        UserSeeder::class,
        CommunitySeeder::class,
        TagSeeder::class,
        AddressSeeder::class,
        AddressBookSeeder::class,
        EventSeeder::class,
        CfpSeeder::class,
        EventTagSeeder::class,
    ]);

    $organizer = User::query()->where('email', 'andrea.rossi@email.it')->firstOrFail();
    $speaker = User::query()->where('email', 'anna.verdi@email.it')->firstOrFail();

    Livewire::test('pages::auth.login')
        ->set('email', 'andrea.rossi@email.it')
        ->set('password', 'password')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard.index'));

    $internalEvent = Event::query()->where('title', 'Java & Spring Boot Workshop')->firstOrFail();
    $internalCfp = $internalEvent->cfp()->with(['fields', 'submissions.answers'])->firstOrFail();

    expect($internalCfp->mode)->toBe(CfpMode::Internal);
    expect($internalCfp->status)->toBe(CfpStatus::Published);
    expect($internalCfp->external_url)->toBeNull();
    expect($internalCfp->fields)->toHaveCount(7);
    expect($internalCfp->fields->pluck('key')->all())->toContain('level', 'format', 'duration_minutes');
    expect($internalCfp->fields->pluck('key')->all())->not->toContain('attachment');

    expect(CfpTemplate::query()->where('title', 'Talk standard meetup')->exists())->toBeTrue();
    expect(CfpTemplate::query()->where('title', 'Conference proposal')->exists())->toBeTrue();

    expect($internalCfp->submissions)->toHaveCount(3);
    expect(CfpSubmission::query()
        ->where('cfp_id', $internalCfp->id)
        ->where('user_id', $speaker->id)
        ->count())->toBe(2);
    expect($internalCfp->submissions->flatMap->answers)->not->toBeEmpty();

    $publishedExternal = Event::query()->where('title', 'State Management in React con Redux')->firstOrFail();
    $this->get(route('events.show', $publishedExternal))
        ->assertOk()
        ->assertSee('CFP')
        ->assertSee('https://cfp.example.test/react-roma');

    $draftExternal = Event::query()->where('title', 'Introduzione a Gutenberg e Blocchi Personalizzati, titolo lungo per vedere se si tronca')->firstOrFail();
    $this->get(route('events.show', $draftExternal))
        ->assertNotFound();

    $archivedExternal = Event::query()
        ->where('title', 'Laravel 10: Nuove Funzionalità e Best Practices e proviamo anche un titolo più lungo direi, ottimo così.')
        ->firstOrFail();
    $this->get(route('events.show', $archivedExternal))
        ->assertOk()
        ->assertDontSee('https://cfp.example.test/archived-hidden');

    Livewire::actingAs($organizer)
        ->test('pages::dashboard.events.create')
        ->set('selectedCommunity', '1')
        ->set('title', 'Smoke CFP esterna creata da dashboard')
        ->set('description', 'Evento smoke per verificare scrittura CFP da form Livewire.')
        ->set('type', EventType::Online->value)
        ->set('start_date', now()->addMonth()->format('d-m-Y H:i'))
        ->set('end_date', now()->addMonth()->addHours(2)->format('d-m-Y H:i'))
        ->set('has_cfp', true)
        ->set('cfp_status', CfpStatus::Published->value)
        ->set('cfp_external_url', 'https://cfp.example.test/livewire-create')
        ->set('cfp_opens_at', now()->format('d-m-Y H:i'))
        ->set('cfp_closes_at', now()->addWeeks(2)->format('d-m-Y H:i'))
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard.communities.events'));

    $createdEvent = Event::query()->where('title', 'Smoke CFP esterna creata da dashboard')->firstOrFail();
    expect($createdEvent->cfp)->not->toBeNull();
    expect($createdEvent->cfp->mode)->toBe(CfpMode::External);
    expect($createdEvent->cfp->external_url)->toBe('https://cfp.example.test/livewire-create');

    Livewire::actingAs($organizer)
        ->test('pages::dashboard.events.edit', ['event' => $createdEvent])
        ->set('cfp_status', CfpStatus::Draft->value)
        ->set('cfp_external_url', 'https://cfp.example.test/livewire-edit')
        ->set('cfp_opens_at', now()->addDay()->format('d-m-Y H:i'))
        ->set('cfp_closes_at', now()->addWeeks(3)->format('d-m-Y H:i'))
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard.communities.events'));

    $createdEvent->refresh();
    expect($createdEvent->cfp->status)->toBe(CfpStatus::Draft);
    expect($createdEvent->cfp->external_url)->toBe('https://cfp.example.test/livewire-edit');

    Livewire::actingAs($organizer)
        ->test('pages::dashboard.events.edit', ['event' => $createdEvent])
        ->set('has_cfp', false)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard.communities.events'));

    expect(Cfp::query()->where('event_id', $createdEvent->id)->exists())->toBeFalse();
});
