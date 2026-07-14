<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\CfpFieldType;
use App\Enums\CfpMode;
use App\Enums\CfpStatus;
use App\Enums\CfpSubmissionStatus;
use App\Mail\CfpSubmissionReceived;
use App\Mail\CfpSubmissionSubmitted;
use App\Models\Cfp;
use App\Models\CfpSubmission;
use App\Models\CfpTemplateField;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SubmitCfpApplication
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<int|string, mixed>  $answers
     */
    public function execute(Cfp $cfp, User $user, array $data, array $answers): CfpSubmission
    {
        Gate::forUser($user)->authorize('apply', $cfp);

        $cfp->loadMissing('fields');
        $this->ensureSubmittable($cfp);

        $validated = Validator::make($data, [
            'title'    => ['required', 'string', 'max:255'],
            'abstract' => ['required', 'string', 'min:20'],
        ])->validate();

        $this->validateAnswers($cfp, $answers);

        $submission = DB::transaction(function () use ($cfp, $user, $validated, $answers): CfpSubmission {
            /** @var CfpSubmission $submission */
            $submission = $cfp->submissions()->create([
                'user_id'      => $user->id,
                'title'        => $validated['title'],
                'abstract'     => $validated['abstract'],
                'status'       => CfpSubmissionStatus::Submitted,
                'submitted_at' => now(),
            ]);

            foreach ($cfp->fields as $field) {
                $submission->answers()->create([
                    'cfp_template_field_id' => $field->id,
                    'value'                 => ['value' => $this->normalizeAnswer($field, $answers[$field->id] ?? null)],
                ]);
            }

            return $submission->load(['answers.field', 'cfp.event']);
        });

        $submission->loadMissing(['user', 'cfp.event.community.user']);

        $organizer = $submission->cfp?->event?->community?->user;

        if ($organizer) {
            Mail::to($organizer)->send(new CfpSubmissionReceived($submission));
        }

        Mail::to($submission->user)->send(new CfpSubmissionSubmitted($submission));

        return $submission;
    }

    private function ensureSubmittable(Cfp $cfp): void
    {
        if ($cfp->mode !== CfpMode::Internal || $cfp->status !== CfpStatus::Published) {
            abort(404);
        }

        if ($cfp->opens_at->isFuture() || $cfp->closes_at->isPast()) {
            abort(403);
        }
    }

    /**
     * @param  array<int|string, mixed>  $answers
     */
    private function validateAnswers(Cfp $cfp, array $answers): void
    {
        $rules = [];
        $messages = [];

        foreach ($cfp->fields as $field) {
            $key = 'answers.'.$field->id;
            $fieldRules = $field->required ? ['required'] : ['nullable'];

            match ($field->type) {
                CfpFieldType::Textarea, CfpFieldType::Text => $fieldRules[] = 'string',
                CfpFieldType::Url                          => $fieldRules[] = 'url',
                CfpFieldType::Email                        => $fieldRules[] = 'email',
                CfpFieldType::Number                       => $fieldRules[] = 'numeric',
                CfpFieldType::Date                         => $fieldRules[] = 'date',
                CfpFieldType::Checkbox                     => $fieldRules[] = 'boolean',
                CfpFieldType::Select                       => $fieldRules[] = 'string',
                CfpFieldType::Multiselect                  => $fieldRules[] = 'array',
            };

            if ($field->type === CfpFieldType::Number && is_array($field->validation)) {
                if (isset($field->validation['min'])) {
                    $fieldRules[] = 'min:'.(int) $field->validation['min'];
                }

                if (isset($field->validation['max'])) {
                    $fieldRules[] = 'max:'.(int) $field->validation['max'];
                }
            }

            $rules[$key] = $fieldRules;
            $messages[$key.'.required'] = 'Il campo '.$field->label.' e obbligatorio.';

            if ($field->type === CfpFieldType::Multiselect) {
                $rules[$key.'.*'] = ['string'];
            }
        }

        Validator::make(['answers' => $answers], $rules, $messages)->validate();

        foreach ($cfp->fields as $field) {
            $value = $answers[$field->id] ?? null;
            $options = $this->options($field);

            if ($field->type === CfpFieldType::Select && $value !== null && $value !== '' && !in_array($value, $options, true)) {
                throw ValidationException::withMessages([
                    'answers.'.$field->id => 'Opzione non valida per '.$field->label.'.',
                ]);
            }

            if ($field->type === CfpFieldType::Multiselect && is_array($value)) {
                $selected = collect($value)
                    ->filter(fn (mixed $option): bool => is_string($option))
                    ->values()
                    ->all();
                $invalid = array_diff($selected, $options);

                if ($invalid !== []) {
                    throw ValidationException::withMessages([
                        'answers.'.$field->id => 'Una o piu opzioni non sono valide per '.$field->label.'.',
                    ]);
                }
            }
        }
    }

    private function normalizeAnswer(CfpTemplateField $field, mixed $value): mixed
    {
        return match ($field->type) {
            CfpFieldType::Checkbox    => (bool) $value,
            CfpFieldType::Multiselect => is_array($value) ? array_values($value) : [],
            CfpFieldType::Number      => is_numeric($value) ? (float) $value : null,
            default                   => is_string($value) ? mb_trim($value) : $value,
        };
    }

    /**
     * @return array<int, string>
     */
    private function options(CfpTemplateField $field): array
    {
        if (!is_array($field->options)) {
            return [];
        }

        return collect($field->options)
            ->filter(fn (mixed $option): bool => is_string($option) && mb_trim($option) !== '')
            ->map(fn (string $option): string => mb_trim($option))
            ->values()
            ->all();
    }
}
