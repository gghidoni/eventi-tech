<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Enums\CfpFieldType;
use App\Enums\CfpMode;
use App\Enums\CfpStatus;
use App\Models\CfpTemplate;
use App\Models\Event;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** @phpstan-ignore trait.unused */
trait ManagesEventCfpForm
{
    public function updatedSelectedCommunity(): void
    {
        $this->cfp_template_id = '';
        $this->cfp_fields = [];
        $this->addCfpField();
    }

    public function updatedCfpTemplateId(): void
    {
        $this->applyCfpTemplate();
    }

    /**
     * @return Collection<int, CfpTemplate>
     */
    public function cfpTemplates(): Collection
    {
        $communityId = $this->cfpCommunityId();

        if ($communityId === null) {
            return collect();
        }

        /** @var EloquentCollection<int, CfpTemplate> $templates */
        $templates = CfpTemplate::query()
            ->where('community_id', $communityId)
            ->with('fields')
            ->orderBy('title')
            ->get();

        return $templates->toBase();
    }

    public function applyCfpTemplate(): void
    {
        if ($this->cfp_template_id === '') {
            return;
        }

        $communityId = $this->cfpCommunityId();

        if ($communityId === null) {
            return;
        }

        $template = CfpTemplate::query()
            ->where('community_id', $communityId)
            ->with('fields')
            ->findOrFail((int) $this->cfp_template_id);

        $this->cfp_fields = $template->fields
            ->map(fn ($field): array => [
                'cfp_template_field_id' => $field->id,
                'key'                   => $field->key,
                'label'                 => $field->label,
                'type'                  => $field->type->value,
                'required'              => $field->required,
                'placeholder'           => $field->placeholder ?? '',
                'help_text'             => $field->help_text ?? '',
                'options_text'          => is_array($field->options) ? implode("\n", $field->options) : '',
                'sort_order'            => $field->sort_order,
            ])
            ->values()
            ->all();
    }

    public function addCfpField(): void
    {
        $this->cfp_fields[] = [
            'cfp_template_field_id' => null,
            'key'                   => '',
            'label'                 => '',
            'type'                  => CfpFieldType::Text->value,
            'required'              => false,
            'placeholder'           => '',
            'help_text'             => '',
            'options_text'          => '',
            'sort_order'            => (count($this->cfp_fields) + 1) * 10,
        ];
    }

    public function removeCfpField(int $index): void
    {
        unset($this->cfp_fields[$index]);
        $this->cfp_fields = array_values($this->cfp_fields);
    }

    protected function initializeCfpForm(?Event $event = null): void
    {
        $this->cfpFieldTypes = collect(CfpFieldType::cases())
            ->mapWithKeys(fn (CfpFieldType $type): array => [$type->value => $this->cfpFieldTypeLabel($type)])
            ->all();

        $this->cfpStatuses = collect(CfpStatus::cases())
            ->mapWithKeys(fn (CfpStatus $status): array => [$status->value => $this->cfpStatusLabel($status)])
            ->all();

        if ($event !== null) {
            $this->fillCfpFromEvent($event);

            return;
        }

        $this->resetCfpFormDefaults();
    }

    protected function resetCfpFormDefaults(): void
    {
        $this->has_cfp = false;
        $this->cfp_mode = CfpMode::External->value;
        $this->cfp_status = CfpStatus::Published->value;
        $this->cfp_title = '';
        $this->cfp_description = '';
        $this->cfp_external_url = '';
        $this->cfp_opens_at = now()->format('d-m-Y H:i');
        $this->cfp_closes_at = '';
        $this->cfp_template_id = '';
        $this->cfp_fields = [];
        $this->addCfpField();
    }

    protected function fillCfpFromEvent(Event $event): void
    {
        $event->loadMissing(['cfp.fields']);
        $cfp = $event->cfp;

        if (!$cfp) {
            $this->resetCfpFormDefaults();
            $this->cfp_title = 'CFP - '.$event->title;
            $this->cfp_closes_at = $event->start_date?->copy()->subDay()->format('d-m-Y H:i') ?? '';

            return;
        }

        $this->has_cfp = true;
        $this->cfp_mode = $cfp->mode->value;
        $this->cfp_status = $cfp->status->value;
        $this->cfp_title = $cfp->title;
        $this->cfp_description = $cfp->description ?? '';
        $this->cfp_external_url = $cfp->external_url ?? '';
        $this->cfp_opens_at = $cfp->opens_at->format('d-m-Y H:i');
        $this->cfp_closes_at = $cfp->closes_at->format('d-m-Y H:i');
        $this->cfp_template_id = $cfp->cfp_template_id ? (string) $cfp->cfp_template_id : '';
        $this->cfp_fields = $cfp->fields
            ->map(fn ($field): array => [
                'cfp_template_field_id' => $field->id,
                'key'                   => $field->key,
                'label'                 => $field->label,
                'type'                  => $field->type->value,
                'required'              => $field->required,
                'placeholder'           => $field->placeholder ?? '',
                'help_text'             => $field->help_text ?? '',
                'options_text'          => is_array($field->options) ? implode("\n", $field->options) : '',
                'sort_order'            => $field->sort_order,
            ])
            ->values()
            ->all();

        if ($this->cfp_fields === []) {
            $this->addCfpField();
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function buildCfpPayload(string $eventTitle): ?array
    {
        if (!$this->has_cfp) {
            return null;
        }

        $this->validateCfpBase();

        $opensAt = $this->parseCfpDate((string) $this->cfp_opens_at, 'cfp_opens_at');
        $closesAt = $this->parseCfpDate((string) $this->cfp_closes_at, 'cfp_closes_at');

        if ($closesAt->lessThanOrEqualTo($opensAt)) {
            throw ValidationException::withMessages([
                'cfp_closes_at' => 'La chiusura CFP deve essere successiva all\'apertura.',
            ]);
        }

        $mode = CfpMode::from($this->cfp_mode);
        $title = mb_trim($this->cfp_title) !== '' ? $this->cfp_title : 'CFP - '.$eventTitle;

        return [
            'mode'                 => $mode,
            'status'               => CfpStatus::from($this->cfp_status),
            'title'                => $title,
            'description'          => mb_trim($this->cfp_description) === '' ? null : $this->cfp_description,
            'opens_at'             => $opensAt,
            'closes_at'            => $closesAt,
            'external_url'         => $mode === CfpMode::External ? $this->cfp_external_url : null,
            'cfp_template_id'      => $mode === CfpMode::Internal && $this->cfp_template_id !== '' ? (int) $this->cfp_template_id : null,
            'fields'               => $mode === CfpMode::Internal ? $this->normalizedCfpFields() : [],
        ];
    }

    protected function validateCfpBase(): void
    {
        validator(
            [
                'cfp_mode'               => $this->cfp_mode,
                'cfp_status'             => $this->cfp_status,
                'cfp_title'              => $this->cfp_title,
                'cfp_description'        => $this->cfp_description,
                'cfp_external_url'       => $this->cfp_external_url,
                'cfp_opens_at'           => $this->cfp_opens_at,
                'cfp_closes_at'          => $this->cfp_closes_at,
            ],
            [
                'cfp_mode'               => ['required', 'in:internal,external'],
                'cfp_status'             => ['required', 'in:draft,published,archived'],
                'cfp_title'              => ['nullable', 'string', 'max:255'],
                'cfp_description'        => ['nullable', 'string'],
                'cfp_external_url'       => $this->cfp_mode === CfpMode::External->value ? ['required', 'url'] : ['nullable'],
                'cfp_opens_at'           => ['required', 'string'],
                'cfp_closes_at'          => ['required', 'string'],
            ],
        )->validate();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function normalizedCfpFields(): array
    {
        $fields = [];
        $seen = [];

        foreach ($this->cfp_fields as $index => $field) {
            $label = mb_trim((string) ($field['label'] ?? ''));

            if ($label === '') {
                throw ValidationException::withMessages([
                    'cfp_fields.'.$index.'.label' => 'La label del campo e obbligatoria.',
                ]);
            }

            $type = CfpFieldType::from((string) ($field['type'] ?? CfpFieldType::Text->value));

            $options = $this->cfpOptionsFromText((string) ($field['options_text'] ?? ''));

            if (in_array($type, [CfpFieldType::Select, CfpFieldType::Multiselect], true) && $options === []) {
                throw ValidationException::withMessages([
                    'cfp_fields.'.$index.'.options_text' => 'Aggiungi almeno una opzione.',
                ]);
            }

            $key = $this->uniqueCfpFieldKey((string) ($field['key'] ?? ''), $label, $seen);
            $seen[] = $key;

            $fields[] = [
                'cfp_template_field_id' => $field['cfp_template_field_id'] ?? null,
                'key'                   => $key,
                'label'                 => $label,
                'type'                  => $type,
                'required'              => (bool) ($field['required'] ?? false),
                'placeholder'           => mb_trim((string) ($field['placeholder'] ?? '')),
                'help_text'             => mb_trim((string) ($field['help_text'] ?? '')),
                'options'               => in_array($type, [CfpFieldType::Select, CfpFieldType::Multiselect], true) ? $options : null,
                'validation'            => null,
                'sort_order'            => ($index + 1) * 10,
            ];
        }

        return $fields;
    }

    protected function cfpCommunityId(): ?int
    {
        if (property_exists($this, 'selectedCommunity') && $this->selectedCommunity) {
            return (int) $this->selectedCommunity;
        }

        if (property_exists($this, 'event') && $this->event instanceof Event) {
            return (int) $this->event->community_id;
        }

        return null;
    }

    protected function parseCfpDate(string $value, string $field): Carbon
    {
        try {
            return Carbon::createFromFormat('d-m-Y H:i', $value);
        } catch (Exception) {
            throw ValidationException::withMessages([
                $field => __('validation.date', ['attribute' => $field]),
            ]);
        }
    }

    /**
     * @return array<int, string>
     */
    protected function cfpOptionsFromText(string $text): array
    {
        return collect(preg_split('/\r\n|\r|\n|,/', $text) ?: [])
            ->map(fn (string $option): string => mb_trim($option))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $seen
     */
    protected function uniqueCfpFieldKey(string $key, string $label, array $seen): string
    {
        $base = Str::slug(mb_trim($key) !== '' ? $key : $label, '_');
        $base = $base !== '' ? $base : 'campo';
        $candidate = $base;
        $counter = 2;

        while (in_array($candidate, $seen, true)) {
            $candidate = $base.'_'.$counter;
            $counter++;
        }

        return $candidate;
    }

    protected function cfpFieldTypeLabel(CfpFieldType $type): string
    {
        return match ($type) {
            CfpFieldType::Text        => 'Testo',
            CfpFieldType::Textarea    => 'Textarea',
            CfpFieldType::Select      => 'Select',
            CfpFieldType::Multiselect => 'Multiselect',
            CfpFieldType::Checkbox    => 'Checkbox',
            CfpFieldType::Url         => 'URL',
            CfpFieldType::Email       => 'Email',
            CfpFieldType::Number      => 'Numero',
            CfpFieldType::Date        => 'Data',
        };
    }

    protected function cfpStatusLabel(CfpStatus $status): string
    {
        return match ($status) {
            CfpStatus::Draft     => 'Bozza',
            CfpStatus::Published => 'Pubblicata',
            CfpStatus::Archived  => 'Archiviata',
        };
    }
}
