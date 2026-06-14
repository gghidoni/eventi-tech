<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CfpFieldType;
use App\Enums\CfpMode;
use App\Enums\CfpStatus;
use App\Enums\CfpSubmissionStatus;
use App\Models\Cfp;
use App\Models\CfpSubmission;
use App\Models\CfpTemplate;
use App\Models\CfpTemplateField;
use App\Models\Community;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class CfpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $javaCommunity = Community::query()->where('slug', 'java-ancona')->firstOrFail();
        $laravelCommunity = Community::query()->where('slug', 'laravel-pordenone')->firstOrFail();

        $javaTemplate = $this->createTemplate($javaCommunity, 'Talk standard meetup', 'Template per CFP meetup con talk singoli e campi logistici.', [
            [
                'key'        => 'level',
                'label'      => 'Livello del talk',
                'type'       => CfpFieldType::Select,
                'required'   => true,
                'options'    => ['beginner', 'intermediate', 'advanced'],
                'sort_order' => 10,
            ],
            [
                'key'        => 'format',
                'label'      => 'Formato preferito',
                'type'       => CfpFieldType::Multiselect,
                'required'   => true,
                'options'    => ['talk', 'workshop', 'lightning'],
                'sort_order' => 20,
            ],
            [
                'key'         => 'logistics',
                'label'       => 'Note logistiche',
                'type'        => CfpFieldType::Textarea,
                'required'    => false,
                'placeholder' => 'Es. microfono, adattatori, setup live coding',
                'sort_order'  => 30,
            ],
            [
                'key'        => 'slides_url',
                'label'      => 'URL slide o materiale',
                'type'       => CfpFieldType::Url,
                'required'   => false,
                'sort_order' => 40,
            ],
            [
                'key'        => 'first_time_speaker',
                'label'      => 'Prima volta come speaker?',
                'type'       => CfpFieldType::Checkbox,
                'required'   => false,
                'sort_order' => 50,
            ],
            [
                'key'        => 'duration_minutes',
                'label'      => 'Durata proposta in minuti',
                'type'       => CfpFieldType::Number,
                'required'   => true,
                'validation' => ['min' => 10, 'max' => 120],
                'sort_order' => 60,
            ],
            [
                'key'        => 'available_from',
                'label'      => 'Disponibile da',
                'type'       => CfpFieldType::Date,
                'required'   => false,
                'sort_order' => 70,
            ],
        ]);

        $this->createTemplate($laravelCommunity, 'Conference proposal', 'Template per proposte conference con track e requisiti tecnici.', [
            [
                'key'        => 'track',
                'label'      => 'Track',
                'type'       => CfpFieldType::Select,
                'required'   => true,
                'options'    => ['backend', 'frontend', 'devops', 'community'],
                'sort_order' => 10,
            ],
            [
                'key'        => 'audience',
                'label'      => 'Audience target',
                'type'       => CfpFieldType::Text,
                'required'   => true,
                'sort_order' => 20,
            ],
            [
                'key'        => 'speaker_email',
                'label'      => 'Email contatto speaker',
                'type'       => CfpFieldType::Email,
                'required'   => true,
                'sort_order' => 30,
            ],
        ]);

        $internalEvent = Event::query()->where('title', 'Java & Spring Boot Workshop')->firstOrFail();
        $internalCfp = $internalEvent->cfp()->updateOrCreate(
            ['event_id' => $internalEvent->id],
            [
                'cfp_template_id' => $javaTemplate->id,
                'mode'            => CfpMode::Internal,
                'status'          => CfpStatus::Published,
                'title'           => 'CFP interna - '.$internalEvent->title,
                'description'     => 'CFP interna seedata per testare campi custom e candidature multiple.',
                'opens_at'        => now()->subDays(7),
                'closes_at'       => $internalEvent->start_date->copy()->subDay(),
                'external_url'    => null,
            ],
        );
        $this->createSubmissions($internalCfp);

        $publishedExternalEvent = Event::query()->where('title', 'State Management in React con Redux')->firstOrFail();
        $publishedExternalEvent->cfp()->updateOrCreate(
            ['event_id' => $publishedExternalEvent->id],
            [
                'mode'         => CfpMode::External,
                'status'       => CfpStatus::Published,
                'title'        => 'CFP esterna pubblicata - '.$publishedExternalEvent->title,
                'description'  => 'CFP esterna pubblicata con deadline visibile e link esterno.',
                'opens_at'     => now()->subDays(3),
                'closes_at'    => $publishedExternalEvent->start_date->copy()->subDays(2),
                'external_url' => 'https://cfp.example.test/react-roma',
            ],
        );

        $draftExternalEvent = Event::query()->where('title', 'Introduzione a Gutenberg e Blocchi Personalizzati, titolo lungo per vedere se si tronca')->firstOrFail();
        $draftExternalEvent->cfp()->updateOrCreate(
            ['event_id' => $draftExternalEvent->id],
            [
                'mode'         => CfpMode::External,
                'status'       => CfpStatus::Draft,
                'title'        => 'CFP esterna bozza - '.$draftExternalEvent->title,
                'description'  => 'CFP seedata in bozza: non deve essere visibile sul frontend pubblico.',
                'opens_at'     => now()->addDays(2),
                'closes_at'    => $draftExternalEvent->start_date->copy()->subDays(3),
                'external_url' => 'https://cfp.example.test/draft-hidden',
            ],
        );

        $archivedExternalEvent = Event::query()
            ->where('title', 'Laravel 10: Nuove Funzionalità e Best Practices e proviamo anche un titolo più lungo direi, ottimo così.')
            ->firstOrFail();
        $archivedExternalEvent->cfp()->updateOrCreate(
            ['event_id' => $archivedExternalEvent->id],
            [
                'mode'         => CfpMode::External,
                'status'       => CfpStatus::Archived,
                'title'        => 'CFP esterna archiviata - '.$archivedExternalEvent->title,
                'description'  => 'CFP seedata come archiviata: resta nel DB ma non appare sul frontend pubblico.',
                'opens_at'     => now()->subDays(30),
                'closes_at'    => now()->subDays(7),
                'external_url' => 'https://cfp.example.test/archived-hidden',
            ],
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     */
    private function createTemplate(Community $community, string $title, string $description, array $fields): CfpTemplate
    {
        $template = CfpTemplate::query()->updateOrCreate(
            [
                'community_id' => $community->id,
                'title'        => $title,
            ],
            [
                'description' => $description,
            ],
        );

        foreach ($fields as $field) {
            CfpTemplateField::query()->updateOrCreate(
                [
                    'cfp_template_id' => $template->id,
                    'key'             => $field['key'],
                ],
                [
                    'label'       => $field['label'],
                    'type'        => $field['type'],
                    'required'    => $field['required'] ?? false,
                    'placeholder' => $field['placeholder'] ?? null,
                    'help_text'   => $field['help_text'] ?? null,
                    'options'     => $field['options'] ?? null,
                    'validation'  => $field['validation'] ?? null,
                    'sort_order'  => $field['sort_order'] ?? 0,
                ],
            );
        }

        return $template;
    }

    private function createSubmissions(Cfp $cfp): void
    {
        $speaker = User::query()->where('email', 'anna.verdi@email.it')->firstOrFail();
        $secondSpeaker = User::query()->where('email', 'andrea.rossi@email.it')->firstOrFail();

        $firstSubmission = CfpSubmission::query()->updateOrCreate(
            [
                'cfp_id'  => $cfp->id,
                'user_id' => $speaker->id,
                'title'   => 'Spring Boot senza sorprese in produzione',
            ],
            [
                'abstract'     => 'Strategie pratiche per osservabilità, configurazione e deployment sicuro di applicazioni Spring Boot.',
                'status'       => CfpSubmissionStatus::Submitted,
                'submitted_at' => now()->subDays(2),
            ],
        );

        $secondSubmission = CfpSubmission::query()->updateOrCreate(
            [
                'cfp_id'  => $cfp->id,
                'user_id' => $speaker->id,
                'title'   => 'Testing efficace per microservizi Java',
            ],
            [
                'abstract'     => 'Un percorso pragmatico tra unit test, contract test e test di integrazione per microservizi Java.',
                'status'       => CfpSubmissionStatus::UnderReview,
                'submitted_at' => now()->subDay(),
            ],
        );

        $draftSubmission = CfpSubmission::query()->updateOrCreate(
            [
                'cfp_id'  => $cfp->id,
                'user_id' => $secondSpeaker->id,
                'title'   => 'Live coding con Spring Modulith',
            ],
            [
                'abstract'     => 'Bozza seedata per verificare lo stato draft e le risposte parziali.',
                'status'       => CfpSubmissionStatus::Draft,
                'submitted_at' => null,
            ],
        );

        $this->answer($firstSubmission, 'level', ['value' => 'advanced']);
        $this->answer($firstSubmission, 'format', ['value' => ['talk']]);
        $this->answer($firstSubmission, 'logistics', ['value' => 'Serve HDMI e connessione stabile per demo live.']);
        $this->answer($firstSubmission, 'slides_url', ['value' => 'https://speaker.example.test/spring-boot-prod']);
        $this->answer($firstSubmission, 'first_time_speaker', ['value' => false]);
        $this->answer($firstSubmission, 'duration_minutes', ['value' => 45]);
        $this->answer($firstSubmission, 'available_from', ['value' => now()->addDays(2)->toDateString()]);
        $this->answer($secondSubmission, 'level', ['value' => 'intermediate']);
        $this->answer($secondSubmission, 'format', ['value' => ['workshop', 'talk']]);
        $this->answer($secondSubmission, 'duration_minutes', ['value' => 90]);

        $this->answer($draftSubmission, 'level', ['value' => 'beginner']);
        $this->answer($draftSubmission, 'format', ['value' => ['lightning']]);
    }

    /**
     * @param  array<string, mixed>  $value
     */
    private function answer(CfpSubmission $submission, string $fieldKey, array $value): void
    {
        $field = $submission->cfp->fields()->where('key', $fieldKey)->firstOrFail();

        $submission->answers()->updateOrCreate(
            [
                'cfp_template_field_id' => $field->id,
            ],
            [
                'value' => $value,
            ],
        );
    }
}
