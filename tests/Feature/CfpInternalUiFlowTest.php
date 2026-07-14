<?php

declare(strict_types=1);

use App\Enums\CfpFieldType;
use App\Enums\CfpMode;
use App\Enums\CfpStatus;
use App\Enums\CfpSubmissionStatus;
use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Mail\CfpSubmissionReceived;
use App\Mail\CfpSubmissionStatusUpdated;
use App\Mail\CfpSubmissionSubmitted;
use App\Models\CfpTemplate;
use App\Models\CfpTemplateField;
use App\Models\Community;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

test('organizer updates unused template in place from inline internal cfp', function () {
    $organizer = User::factory()->create();
    $community = Community::factory()->active()->forUser($organizer)->create();
    $template = CfpTemplate::factory()->create([
        'community_id' => $community->id,
        'title'        => 'Meetup talk template',
    ]);
    CfpTemplateField::factory()->create([
        'cfp_template_id' => $template->id,
        'key'             => 'level',
        'label'           => 'Livello originale',
        'type'            => CfpFieldType::Select,
        'required'        => true,
        'options'         => ['base', 'avanzato'],
        'sort_order'      => 10,
    ]);
    CfpTemplateField::factory()->create([
        'cfp_template_id' => $template->id,
        'key'             => 'notes',
        'label'           => 'Note',
        'type'            => CfpFieldType::Textarea,
        'required'        => false,
        'sort_order'      => 20,
    ]);

    Livewire::actingAs($organizer)
        ->test('pages::dashboard.events.create')
        ->set('selectedCommunity', (string) $community->id)
        ->set('title', 'Evento con CFP inline')
        ->set('description', 'Evento per verificare creazione inline della CFP interna.')
        ->set('type', EventType::Online->value)
        ->set('start_date', now()->addMonth()->format('d-m-Y H:i'))
        ->set('end_date', now()->addMonth()->addHours(2)->format('d-m-Y H:i'))
        ->set('has_cfp', true)
        ->set('cfp_mode', CfpMode::Internal->value)
        ->set('cfp_status', CfpStatus::Published->value)
        ->set('cfp_title', 'CFP evento inline')
        ->set('cfp_opens_at', now()->subDay()->format('d-m-Y H:i'))
        ->set('cfp_closes_at', now()->addWeeks(2)->format('d-m-Y H:i'))
        ->set('cfp_template_id', (string) $template->id)
        ->call('applyCfpTemplate')
        ->set('cfp_fields.0.label', 'Livello modificato nel template derivato')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard.communities.events'));

    $event = Event::query()->where('title', 'Evento con CFP inline')->firstOrFail();
    $event->load('cfp.fields');

    expect($event->cfp->mode)->toBe(CfpMode::Internal);
    expect($event->cfp->status)->toBe(CfpStatus::Published);
    expect($event->cfp->cfp_template_id)->toBe($template->id);
    expect($event->cfp->fields)->toHaveCount(2);
    expect($event->cfp->fields->firstWhere('key', 'level')->label)->toBe('Livello modificato nel template derivato');
    expect($template->fields()->where('key', 'level')->firstOrFail()->label)->toBe('Livello modificato nel template derivato');
    expect(CfpTemplate::query()->where('title', 'Meetup talk template - Evento con CFP inline')->exists())->toBeFalse();
});

test('organizer duplicates template when inline changes would affect another cfp', function () {
    $organizer = User::factory()->create();
    $community = Community::factory()->active()->forUser($organizer)->create();
    $template = CfpTemplate::factory()->create([
        'community_id' => $community->id,
        'title'        => 'Template condiviso',
    ]);
    CfpTemplateField::factory()->create([
        'cfp_template_id' => $template->id,
        'key'             => 'level',
        'label'           => 'Livello originale',
        'type'            => CfpFieldType::Text,
        'sort_order'      => 10,
    ]);
    $sharedEvent = Event::factory()->forCommunity($community)->create();
    $sharedEvent->cfp()->create([
        'cfp_template_id' => $template->id,
        'mode'            => CfpMode::Internal,
        'status'          => CfpStatus::Published,
        'title'           => 'CFP condivisa',
        'opens_at'        => now()->subDay(),
        'closes_at'       => now()->addWeek(),
    ]);

    Livewire::actingAs($organizer)
        ->test('pages::dashboard.events.create')
        ->set('selectedCommunity', (string) $community->id)
        ->set('title', 'Evento con copia template')
        ->set('description', 'Evento per verificare duplicazione template condiviso.')
        ->set('type', EventType::Online->value)
        ->set('start_date', now()->addMonth()->format('d-m-Y H:i'))
        ->set('end_date', now()->addMonth()->addHours(2)->format('d-m-Y H:i'))
        ->set('has_cfp', true)
        ->set('cfp_mode', CfpMode::Internal->value)
        ->set('cfp_status', CfpStatus::Published->value)
        ->set('cfp_title', 'CFP con copia')
        ->set('cfp_opens_at', now()->subDay()->format('d-m-Y H:i'))
        ->set('cfp_closes_at', now()->addWeeks(2)->format('d-m-Y H:i'))
        ->set('cfp_template_id', (string) $template->id)
        ->call('applyCfpTemplate')
        ->set('cfp_fields.0.label', 'Livello copiato')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard.communities.events'));

    $event = Event::query()->where('title', 'Evento con copia template')->firstOrFail();
    $newTemplate = CfpTemplate::query()->where('title', 'Template condiviso - Evento con copia template')->firstOrFail();

    expect($event->cfp->cfp_template_id)->toBe($newTemplate->id);
    expect($template->fields()->where('key', 'level')->firstOrFail()->label)->toBe('Livello originale');
    expect($newTemplate->fields()->where('key', 'level')->firstOrFail()->label)->toBe('Livello copiato');
});

test('organizer edits event and adds custom internal cfp inline', function () {
    $organizer = User::factory()->create();
    $community = Community::factory()->active()->forUser($organizer)->create();
    $event = Event::factory()
        ->online()
        ->forCommunity($community)
        ->create([
            'title'      => 'Evento da modificare',
            'start_date' => now()->addMonth(),
            'end_date'   => now()->addMonth()->addHours(2),
        ]);

    Livewire::actingAs($organizer)
        ->test('pages::dashboard.events.edit', ['event' => $event])
        ->set('has_cfp', true)
        ->set('cfp_mode', CfpMode::Internal->value)
        ->set('cfp_status', CfpStatus::Draft->value)
        ->set('cfp_title', 'CFP aggiunta in edit')
        ->set('cfp_opens_at', now()->format('d-m-Y H:i'))
        ->set('cfp_closes_at', now()->addWeek()->format('d-m-Y H:i'))
        ->set('cfp_fields', [
            [
                'cfp_template_field_id' => null,
                'key'                   => 'audience',
                'label'                 => 'Audience',
                'type'                  => CfpFieldType::Text->value,
                'required'              => true,
                'placeholder'           => '',
                'help_text'             => '',
                'options_text'          => '',
                'sort_order'            => 10,
            ],
        ])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard.communities.events'));

    $event->refresh()->load('cfp.fields');

    expect($event->cfp->mode)->toBe(CfpMode::Internal);
    expect($event->cfp->status)->toBe(CfpStatus::Draft);
    expect($event->cfp->template)->not->toBeNull();
    expect($event->cfp->template->title)->toBe('CFP - Evento da modificare');
    expect($event->cfp->fields)->toHaveCount(1);
    expect($event->cfp->fields->first()->key)->toBe('audience');
});

test('review marks template fields added after submission', function () {
    $organizer = User::factory()->create();
    $speaker = User::factory()->create();
    $community = Community::factory()->active()->forUser($organizer)->create();
    $template = CfpTemplate::factory()->create(['community_id' => $community->id]);
    $field = CfpTemplateField::factory()->create([
        'cfp_template_id' => $template->id,
        'key'             => 'level',
        'label'           => 'Livello',
        'type'            => CfpFieldType::Text,
        'created_at'      => now()->subDay(),
    ]);
    $event = Event::factory()
        ->active()
        ->forCommunity($community)
        ->create([
            'start_date' => now()->addMonth(),
            'end_date'   => now()->addMonth()->addHours(2),
        ]);
    $cfp = $event->cfp()->create([
        'cfp_template_id' => $template->id,
        'mode'            => CfpMode::Internal,
        'status'          => CfpStatus::Published,
        'title'           => 'CFP live template',
        'opens_at'        => now()->subDay(),
        'closes_at'       => now()->addWeek(),
    ]);
    $submission = $cfp->submissions()->create([
        'user_id'      => $speaker->id,
        'title'        => 'Talk inviato',
        'abstract'     => 'Abstract sufficientemente lungo per una submission valida.',
        'status'       => CfpSubmissionStatus::Submitted,
        'submitted_at' => now()->subHour(),
    ]);
    $submission->answers()->create([
        'cfp_template_field_id' => $field->id,
        'value'                 => ['value' => 'advanced'],
    ]);
    CfpTemplateField::factory()->create([
        'cfp_template_id' => $template->id,
        'key'             => 'budget',
        'label'           => 'Budget viaggio',
        'type'            => CfpFieldType::Number,
        'created_at'      => now(),
    ]);

    Livewire::actingAs($organizer)
        ->test('pages::dashboard.cfps.submission', ['cfp' => $cfp, 'submission' => $submission])
        ->assertSee('Budget viaggio')
        ->assertSee("Campo aggiunto dopo l'invio della candidatura.", false);
});

test('review does not mark answered fields as added after submission', function () {
    $organizer = User::factory()->create();
    $speaker = User::factory()->create();
    $community = Community::factory()->active()->forUser($organizer)->create();
    $template = CfpTemplate::factory()->create(['community_id' => $community->id]);
    $field = CfpTemplateField::factory()->create([
        'cfp_template_id' => $template->id,
        'key'             => 'level',
        'label'           => 'Livello',
        'type'            => CfpFieldType::Text,
        'created_at'      => now(),
    ]);
    $event = Event::factory()
        ->active()
        ->forCommunity($community)
        ->create([
            'start_date' => now()->addMonth(),
            'end_date'   => now()->addMonth()->addHours(2),
        ]);
    $cfp = $event->cfp()->create([
        'cfp_template_id' => $template->id,
        'mode'            => CfpMode::Internal,
        'status'          => CfpStatus::Published,
        'title'           => 'CFP live template',
        'opens_at'        => now()->subDay(),
        'closes_at'       => now()->addWeek(),
    ]);
    $submission = $cfp->submissions()->create([
        'user_id'      => $speaker->id,
        'title'        => 'Talk inviato',
        'abstract'     => 'Abstract sufficientemente lungo per una submission valida.',
        'status'       => CfpSubmissionStatus::Submitted,
        'submitted_at' => now()->subHour(),
    ]);
    $submission->answers()->create([
        'cfp_template_field_id' => $field->id,
        'value'                 => ['value' => 'advanced'],
    ]);

    Livewire::actingAs($organizer)
        ->test('pages::dashboard.cfps.submission', ['cfp' => $cfp, 'submission' => $submission])
        ->assertSee('Livello')
        ->assertSee('advanced')
        ->assertDontSee("Campo aggiunto dopo l'invio della candidatura.", false);
});

test('organizer publishes internal cfp speaker applies and organizer reviews submission', function () {
    Mail::fake();

    $organizer = User::factory()->create();
    $speaker = User::factory()->create();
    $community = Community::factory()->active()->forUser($organizer)->create();
    $event = Event::factory()
        ->active()
        ->forCommunity($community)
        ->create([
            'title'      => 'Mobile CFP Flow',
            'start_date' => now()->addMonth(),
            'end_date'   => now()->addMonth()->addHours(2),
        ]);

    Livewire::actingAs($organizer)
        ->test('pages::dashboard.events.edit', ['event' => $event])
        ->set('has_cfp', true)
        ->set('cfp_mode', CfpMode::Internal->value)
        ->set('cfp_status', CfpStatus::Published->value)
        ->set('cfp_title', 'CFP - Mobile CFP Flow')
        ->set('cfp_description', 'CFP interna custom per smoke test.')
        ->set('cfp_opens_at', now()->subDay()->format('d-m-Y H:i'))
        ->set('cfp_closes_at', now()->addWeeks(2)->format('d-m-Y H:i'))
        ->set('cfp_fields', [
            [
                'cfp_template_field_id' => null,
                'key'                   => 'level',
                'label'                 => 'Livello',
                'type'                  => 'select',
                'required'              => true,
                'placeholder'           => '',
                'help_text'             => '',
                'options_text'          => "base\navanzato",
                'sort_order'            => 10,
            ],
            [
                'cfp_template_field_id' => null,
                'key'                   => 'notes',
                'label'                 => 'Note speaker',
                'type'                  => 'textarea',
                'required'              => false,
                'placeholder'           => '',
                'help_text'             => '',
                'options_text'          => '',
                'sort_order'            => 20,
            ],
        ])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard.communities.events'));

    $event->refresh()->load('cfp.fields');
    expect($event->cfp)->not->toBeNull();
    expect($event->cfp->mode)->toBe(CfpMode::Internal);
    expect($event->cfp->status)->toBe(CfpStatus::Published);
    expect($event->cfp->fields)->toHaveCount(2);
    expect($event->cfp->fields->firstWhere('key', 'level')->options)->toBe(['base', 'avanzato']);

    $this->get(route('events.show', $event))
        ->assertOk()
        ->assertSee('CFP')
        ->assertDontSee(route('events.cfp.apply', $event));

    $this->actingAs($speaker)->get(route('events.show', $event))
        ->assertOk()
        ->assertSee('CFP')
        ->assertSee(route('events.cfp.apply', $event));

    $levelField = $event->cfp->fields->firstWhere('key', 'level');
    $notesField = $event->cfp->fields->firstWhere('key', 'notes');

    Livewire::actingAs($speaker)
        ->test('pages::events.cfp.apply', ['event' => $event])
        ->set('title', 'Architetture Laravel mobile-first')
        ->set('abstract', 'Una proposta concreta per progettare interfacce Laravel e Livewire mobile-first senza perdere manutenibilita.')
        ->set('answers.'.$levelField->id, 'avanzato')
        ->set('answers.'.$notesField->id, 'Serve solo connessione stabile.')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertRedirect(route('events.show', $event));

    Mail::assertSent(CfpSubmissionReceived::class, fn (CfpSubmissionReceived $mail): bool => $mail->hasTo($organizer->email));
    Mail::assertSent(CfpSubmissionSubmitted::class, fn (CfpSubmissionSubmitted $mail): bool => $mail->hasTo($speaker->email));

    $submission = $event->cfp->submissions()->with('answers')->firstOrFail();
    expect($submission->status)->toBe(CfpSubmissionStatus::Submitted);
    expect($submission->answers)->toHaveCount(2);

    Livewire::actingAs($organizer)
        ->test('pages::dashboard.cfps.submission', ['cfp' => $event->cfp, 'submission' => $submission])
        ->set('status', CfpSubmissionStatus::Accepted->value)
        ->call('saveStatus')
        ->assertHasNoErrors();

    expect($submission->refresh()->status)->toBe(CfpSubmissionStatus::Accepted);
    Mail::assertSent(CfpSubmissionStatusUpdated::class, fn (CfpSubmissionStatusUpdated $mail): bool => $mail->hasTo($speaker->email));
});

test('non owner cannot edit event cfp', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $community = Community::factory()->active()->forUser($owner)->create();
    $event = Event::factory()->forCommunity($community)->create(['status' => EventStatus::Active]);

    Livewire::actingAs($other)
        ->test('pages::dashboard.events.edit', ['event' => $event])
        ->assertForbidden();
});

test('organizer sees active submissions filtered by preselected event', function () {
    $organizer = User::factory()->create();
    $speaker = User::factory()->create();
    $community = Community::factory()->active()->forUser($organizer)->create();
    $template = CfpTemplate::factory()->create(['community_id' => $community->id]);
    $event = Event::factory()->forCommunity($community)->create(['title' => 'Evento con candidature']);
    $otherEvent = Event::factory()->forCommunity($community)->create(['title' => 'Evento senza filtro']);
    CfpTemplateField::factory()->create(['cfp_template_id' => $template->id]);

    $cfp = $event->cfp()->create([
        'cfp_template_id' => $template->id,
        'mode'            => CfpMode::Internal,
        'status'          => CfpStatus::Published,
        'title'           => 'CFP evento',
        'opens_at'        => now()->subDay(),
        'closes_at'       => now()->addWeek(),
    ]);
    $cfp->submissions()->create([
        'user_id'      => $speaker->id,
        'title'        => 'Talk visibile',
        'abstract'     => 'Abstract sufficientemente lungo per la candidatura.',
        'status'       => CfpSubmissionStatus::Submitted,
        'submitted_at' => now(),
    ]);

    $otherCfp = $otherEvent->cfp()->create([
        'cfp_template_id' => $template->id,
        'mode'            => CfpMode::Internal,
        'status'          => CfpStatus::Published,
        'title'           => 'CFP altro evento',
        'opens_at'        => now()->subDay(),
        'closes_at'       => now()->addWeek(),
    ]);
    $otherCfp->submissions()->create([
        'user_id'      => $speaker->id,
        'title'        => 'Talk non selezionato',
        'abstract'     => 'Abstract sufficientemente lungo per la candidatura.',
        'status'       => CfpSubmissionStatus::Submitted,
        'submitted_at' => now(),
    ]);

    $this->actingAs($organizer)
        ->get(route('dashboard.communities.submissions', ['event' => $event->id]))
        ->assertOk()
        ->assertSee('Talk visibile')
        ->assertDontSee('Talk non selezionato');
});

test('organizer filters submissions by status', function () {
    $organizer = User::factory()->create();
    $speaker = User::factory()->create();
    $community = Community::factory()->active()->forUser($organizer)->create();
    $template = CfpTemplate::factory()->create(['community_id' => $community->id]);
    $event = Event::factory()->forCommunity($community)->create(['title' => 'Evento con stati']);
    CfpTemplateField::factory()->create(['cfp_template_id' => $template->id]);

    $cfp = $event->cfp()->create([
        'cfp_template_id' => $template->id,
        'mode'            => CfpMode::Internal,
        'status'          => CfpStatus::Published,
        'title'           => 'CFP stati',
        'opens_at'        => now()->subDay(),
        'closes_at'       => now()->addWeek(),
    ]);
    $cfp->submissions()->create([
        'user_id'      => $speaker->id,
        'title'        => 'Talk da valutare',
        'abstract'     => 'Abstract sufficientemente lungo per la candidatura.',
        'status'       => CfpSubmissionStatus::Submitted,
        'submitted_at' => now(),
    ]);
    $cfp->submissions()->create([
        'user_id'      => $speaker->id,
        'title'        => 'Talk accettato',
        'abstract'     => 'Abstract sufficientemente lungo per la candidatura.',
        'status'       => CfpSubmissionStatus::Accepted,
        'submitted_at' => now()->subMinute(),
    ]);

    Livewire::actingAs($organizer)
        ->test('pages::dashboard.communities.submissions')
        ->assertSet('selectedStatus', CfpSubmissionStatus::Submitted->value)
        ->assertSee('Talk da valutare')
        ->assertDontSee('Talk accettato')
        ->set('selectedStatus', CfpSubmissionStatus::Accepted->value)
        ->assertSee('Talk accettato')
        ->assertDontSee('Talk da valutare')
        ->set('selectedStatus', '')
        ->assertSee('Talk da valutare')
        ->assertSee('Talk accettato');
});

test('speaker sees own active submissions', function () {
    $organizer = User::factory()->create();
    $speaker = User::factory()->create();
    $otherSpeaker = User::factory()->create();
    $community = Community::factory()->active()->forUser($organizer)->create();
    $template = CfpTemplate::factory()->create(['community_id' => $community->id]);
    $event = Event::factory()->forCommunity($community)->create(['title' => 'Evento speaker']);
    $cfp = $event->cfp()->create([
        'cfp_template_id' => $template->id,
        'mode'            => CfpMode::Internal,
        'status'          => CfpStatus::Published,
        'title'           => 'CFP speaker',
        'opens_at'        => now()->subDay(),
        'closes_at'       => now()->addWeek(),
    ]);
    $cfp->submissions()->create([
        'user_id'      => $speaker->id,
        'title'        => 'Mia proposta',
        'abstract'     => 'Abstract sufficientemente lungo per la candidatura.',
        'status'       => CfpSubmissionStatus::UnderReview,
        'submitted_at' => now(),
    ]);
    $cfp->submissions()->create([
        'user_id'      => $otherSpeaker->id,
        'title'        => 'Proposta altrui',
        'abstract'     => 'Abstract sufficientemente lungo per la candidatura.',
        'status'       => CfpSubmissionStatus::Submitted,
        'submitted_at' => now(),
    ]);

    Livewire::actingAs($speaker)
        ->test('pages::dashboard.cfp-submissions')
        ->assertSee('Mia proposta')
        ->assertSee('In review')
        ->assertDontSee('Proposta altrui');
});
