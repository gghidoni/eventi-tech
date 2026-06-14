<?php

namespace App\Models;

use App\Enums\CfpFieldType;
use Database\Factories\CfpTemplateFieldFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CfpTemplateField extends Model
{
    /** @use HasFactory<CfpTemplateFieldFactory> */
    use HasFactory;

    protected $fillable = [
        'cfp_template_id',
        'key',
        'label',
        'type',
        'required',
        'placeholder',
        'help_text',
        'options',
        'validation',
        'sort_order',
    ];

    protected $casts = [
        'type'       => CfpFieldType::class,
        'required'   => 'boolean',
        'options'    => 'array',
        'validation' => 'array',
    ];

    /**
     * @return BelongsTo<CfpTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(CfpTemplate::class, 'cfp_template_id');
    }
}
