<?php

namespace App\Models;

use Database\Factories\CfpSubmissionAnswerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CfpSubmissionAnswer extends Model
{
    /** @use HasFactory<CfpSubmissionAnswerFactory> */
    use HasFactory;

    protected $fillable = [
        'cfp_submission_id',
        'cfp_template_field_id',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    /**
     * @return BelongsTo<CfpSubmission, $this>
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(CfpSubmission::class, 'cfp_submission_id');
    }

    /**
     * @return BelongsTo<CfpTemplateField, $this>
     */
    public function field(): BelongsTo
    {
        return $this->belongsTo(CfpTemplateField::class, 'cfp_template_field_id');
    }
}
