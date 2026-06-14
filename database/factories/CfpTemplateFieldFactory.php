<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CfpFieldType;
use App\Models\CfpTemplate;
use App\Models\CfpTemplateField;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CfpTemplateField>
 */
class CfpTemplateFieldFactory extends Factory
{
    protected $model = CfpTemplateField::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $label = fake()->words(2, true);

        return [
            'cfp_template_id' => CfpTemplate::factory(),
            'key'             => Str::slug($label, '_').'_'.fake()->unique()->numberBetween(1, 9999),
            'label'           => ucfirst($label),
            'type'            => CfpFieldType::Text,
            'required'        => fake()->boolean(),
            'placeholder'     => fake()->optional()->sentence(3),
            'help_text'       => fake()->optional()->sentence(),
            'options'         => null,
            'validation'      => null,
            'sort_order'      => fake()->numberBetween(0, 10),
        ];
    }
}
