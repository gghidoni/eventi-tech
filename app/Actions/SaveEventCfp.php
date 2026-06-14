<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\CfpMode;
use App\Models\Cfp;
use App\Models\CfpTemplate;
use App\Models\Event;
use BackedEnum;
use Illuminate\Support\Facades\DB;

class SaveEventCfp
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Event $event, array $data): Cfp
    {
        return DB::transaction(function () use ($event, $data): Cfp {
            $mode = $data['mode'] instanceof CfpMode ? $data['mode'] : CfpMode::from(is_string($data['mode'] ?? null) ? $data['mode'] : CfpMode::External->value);
            $fields = $data['fields'] ?? [];
            $originalTemplateId = $data['cfp_template_id'] ?? null;

            unset($data['fields']);

            if ($mode === CfpMode::External) {
                $data['cfp_template_id'] = null;
                $data['external_url'] = $this->nullableString($data['external_url'] ?? null);
            } else {
                $data['external_url'] = null;

                if (is_array($fields)) {
                    /** @var array<int, array<string, mixed>> $fields */
                    $data['cfp_template_id'] = $this->resolveTemplateId($event, $originalTemplateId, $fields);
                }
            }

            $data['mode'] = $mode;

            /** @var Cfp $cfp */
            $cfp = $event->cfp()->updateOrCreate(
                ['event_id' => $event->id],
                $data,
            );

            return $cfp->refresh();
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     */
    private function resolveTemplateId(Event $event, mixed $originalTemplateId, array $fields): int
    {
        $template = null;

        if (is_numeric($originalTemplateId)) {
            $template = CfpTemplate::query()
                ->where('community_id', $event->community_id)
                ->with('fields')
                ->find((int) $originalTemplateId);
        }

        if ($template && $this->fieldsMatchTemplate($template, $fields)) {
            return $template->id;
        }

        if ($template && !$this->templateIsUsedByOtherCfps($template, $event)) {
            $this->updateTemplateFromFields($template, $fields);

            return $template->id;
        }

        $title = $template
            ? $template->title.' - '.$event->title
            : 'CFP - '.$event->title;

        return $this->createTemplateFromFields($event, $title, $template?->description, $fields)->id;
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     */
    private function fieldsMatchTemplate(CfpTemplate $template, array $fields): bool
    {
        $templateFields = $template->fields->values();
        $fields = array_values($fields);

        if ($templateFields->count() !== count($fields)) {
            return false;
        }

        foreach ($templateFields as $index => $templateField) {
            $field = $fields[$index];
            $fieldTemplateId = $field['cfp_template_field_id'] ?? 0;

            if (!is_int($fieldTemplateId) && !is_string($fieldTemplateId)) {
                return false;
            }

            if ((int) $fieldTemplateId !== $templateField->id) {
                return false;
            }

            if (($field['key'] ?? null) !== $templateField->key) {
                return false;
            }

            if (($field['label'] ?? null) !== $templateField->label) {
                return false;
            }

            $fieldType = $field['type'] ?? '';
            $type = $fieldType instanceof BackedEnum ? $fieldType->value : $fieldType;

            if (!is_string($type)) {
                return false;
            }

            if ($type !== $templateField->type->value) {
                return false;
            }

            if ((bool) ($field['required'] ?? false) !== $templateField->required) {
                return false;
            }

            if ($this->nullableString($field['placeholder'] ?? null) !== $templateField->placeholder) {
                return false;
            }

            if ($this->nullableString($field['help_text'] ?? null) !== $templateField->help_text) {
                return false;
            }

            if (($field['options'] ?? null) !== $templateField->options) {
                return false;
            }

            if (($field['validation'] ?? null) !== $templateField->validation) {
                return false;
            }
        }

        return true;
    }

    private function templateIsUsedByOtherCfps(CfpTemplate $template, Event $event): bool
    {
        return $template->cfps()
            ->where('event_id', '!=', $event->id)
            ->exists();
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     */
    private function updateTemplateFromFields(CfpTemplate $template, array $fields): void
    {
        $keptFieldIds = [];

        foreach (array_values($fields) as $index => $field) {
            $fieldTemplateId = $field['cfp_template_field_id'] ?? null;
            $attributes = $this->fieldAttributes($field, $index);

            if (is_numeric($fieldTemplateId)) {
                $templateField = $template->fields()
                    ->whereKey((int) $fieldTemplateId)
                    ->first();

                if ($templateField) {
                    $templateField->update($attributes);
                    $keptFieldIds[] = $templateField->id;

                    continue;
                }
            }

            $createdField = $template->fields()->create($attributes);
            $keptFieldIds[] = $createdField->id;
        }

        $template->fields()
            ->when($keptFieldIds !== [], fn ($query) => $query->whereNotIn('id', $keptFieldIds))
            ->delete();
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     */
    private function createTemplateFromFields(Event $event, mixed $title, mixed $description, array $fields): CfpTemplate
    {
        /** @var CfpTemplate $template */
        $template = $event->community()->firstOrFail()->cfpTemplates()->create([
            'title'       => $this->nullableString($title) ?? 'Template CFP - '.$event->title,
            'description' => $this->nullableString($description),
        ]);

        foreach (array_values($fields) as $index => $field) {
            $template->fields()->create($this->fieldAttributes($field, $index));
        }

        return $template;
    }

    /**
     * @param  array<string, mixed>  $field
     * @return array<string, mixed>
     */
    private function fieldAttributes(array $field, int $index): array
    {
        return [
            'key'         => is_string($field['key'] ?? null) ? $field['key'] : 'campo_'.($index + 1),
            'label'       => $field['label'],
            'type'        => $field['type'],
            'required'    => (bool) ($field['required'] ?? false),
            'placeholder' => $this->nullableString($field['placeholder'] ?? null),
            'help_text'   => $this->nullableString($field['help_text'] ?? null),
            'options'     => $field['options'] ?? null,
            'validation'  => $field['validation'] ?? null,
            'sort_order'  => $field['sort_order'] ?? (($index + 1) * 10),
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = mb_trim($value);

        return $value === '' ? null : $value;
    }
}
