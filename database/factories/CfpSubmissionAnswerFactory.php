<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CfpSubmission;
use App\Models\CfpSubmissionAnswer;
use App\Models\CfpTemplateField;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CfpSubmissionAnswer>
 */
class CfpSubmissionAnswerFactory extends Factory
{
    protected $model = CfpSubmissionAnswer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cfp_submission_id'     => CfpSubmission::factory(),
            'cfp_template_field_id' => CfpTemplateField::factory(),
            'value'                 => ['value' => fake()->sentence()],
        ];
    }
}
