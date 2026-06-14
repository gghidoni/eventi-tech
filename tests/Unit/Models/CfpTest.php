<?php

declare(strict_types=1);

use App\Enums\CfpFieldType;
use App\Enums\CfpMode;
use App\Enums\CfpStatus;
use App\Enums\CfpSubmissionStatus;
use App\Models\Cfp;
use App\Models\CfpSubmission;
use App\Models\CfpSubmissionAnswer;
use App\Models\CfpTemplate;
use App\Models\CfpTemplateField;
use App\Models\Community;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

describe('cfp schema', function () {
    test('events table no longer exposes legacy cfp_url column', function () {
        expect(Schema::hasColumn('events', 'cfp_url'))->toBeFalse();
    });
});

describe('relationships', function () {
    test('cfp belongs to event and template', function () {
        $template = CfpTemplate::factory()->create();
        $cfp = Cfp::factory()->forTemplate($template)->create();

        expect($cfp->event)->toBeInstanceOf(Event::class);
        expect($cfp->template)->toBeInstanceOf(CfpTemplate::class);
        expect($cfp->template->id)->toBe($template->id);
    });

    test('community has many cfp templates', function () {
        $community = Community::factory()->create();
        CfpTemplate::factory()->count(2)->create(['community_id' => $community->id]);

        expect($community->cfpTemplates)->toHaveCount(2);
        expect($community->cfpTemplates->first())->toBeInstanceOf(CfpTemplate::class);
    });

    test('template and cfp expose ordered fields', function () {
        $template = CfpTemplate::factory()->create();
        $templateField = CfpTemplateField::factory()->create([
            'cfp_template_id' => $template->id,
            'sort_order'      => 2,
        ]);
        CfpTemplateField::factory()->create([
            'cfp_template_id' => $template->id,
            'sort_order'      => 1,
        ]);

        $cfp = Cfp::factory()->forTemplate($template)->internal()->create();

        expect($template->fields->pluck('sort_order')->all())->toBe([1, 2]);
        expect($cfp->fields->first()->id)->not->toBe($templateField->id);
        expect($cfp->fields->pluck('sort_order')->all())->toBe([1, 2]);
    });

    test('submission belongs to cfp and user and has answers', function () {
        $user = User::factory()->create();
        $template = CfpTemplate::factory()->create();
        $cfp = Cfp::factory()->forTemplate($template)->internal()->create();
        $field = CfpTemplateField::factory()->create(['cfp_template_id' => $template->id]);
        $submission = CfpSubmission::factory()->create([
            'cfp_id'  => $cfp->id,
            'user_id' => $user->id,
        ]);
        CfpSubmissionAnswer::factory()->create([
            'cfp_submission_id'      => $submission->id,
            'cfp_template_field_id'  => $field->id,
            'value'                  => ['value' => 'Laravel internals'],
        ]);

        expect($submission->cfp)->toBeInstanceOf(Cfp::class);
        expect($submission->user)->toBeInstanceOf(User::class);
        expect($submission->answers)->toHaveCount(1);
        expect($user->cfpSubmissions)->toHaveCount(1);
    });
});

describe('casts', function () {
    test('cfp casts mode status and dates', function () {
        $cfp = Cfp::factory()->create([
            'mode'      => CfpMode::External,
            'status'    => CfpStatus::Published,
            'opens_at'  => '2026-07-01 10:00:00',
            'closes_at' => '2026-07-31 23:59:00',
        ]);

        expect($cfp->mode)->toBe(CfpMode::External);
        expect($cfp->status)->toBe(CfpStatus::Published);
        expect($cfp->opens_at)->toBeInstanceOf(DateTime::class);
        expect($cfp->closes_at)->toBeInstanceOf(DateTime::class);
    });

    test('field and answer json values are cast to arrays', function () {
        $field = CfpTemplateField::factory()->create([
            'type'       => CfpFieldType::Select,
            'options'    => ['beginner', 'advanced'],
            'validation' => ['max' => 120],
        ]);
        $answer = CfpSubmissionAnswer::factory()->create([
            'cfp_template_field_id' => $field->id,
            'value'                 => ['value' => 'advanced'],
        ]);

        expect($field->type)->toBe(CfpFieldType::Select);
        expect($field->options)->toBe(['beginner', 'advanced']);
        expect($field->validation)->toBe(['max' => 120]);
        expect($answer->value)->toBe(['value' => 'advanced']);
    });

    test('submission casts status and submitted_at', function () {
        $submission = CfpSubmission::factory()->submitted()->create([
            'submitted_at' => '2026-07-02 12:00:00',
        ]);

        expect($submission->status)->toBe(CfpSubmissionStatus::Submitted);
        expect($submission->submitted_at)->toBeInstanceOf(DateTime::class);
    });
});
