<?php

namespace App\Models;

use App\Enums\CfpMode;
use App\Enums\CfpStatus;
use Database\Factories\CfpFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cfp extends Model
{
    /** @use HasFactory<CfpFactory> */
    use HasFactory;

    protected $fillable = [
        'event_id',
        'cfp_template_id',
        'mode',
        'status',
        'title',
        'description',
        'opens_at',
        'closes_at',
        'external_url',
    ];

    protected $casts = [
        'mode'      => CfpMode::class,
        'status'    => CfpStatus::class,
        'opens_at'  => 'datetime',
        'closes_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return BelongsTo<CfpTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(CfpTemplate::class, 'cfp_template_id');
    }

    /**
     * @return HasMany<CfpTemplateField, $this>
     */
    public function fields(): HasMany
    {
        return $this->hasMany(CfpTemplateField::class, 'cfp_template_id', 'cfp_template_id')->orderBy('sort_order');
    }

    /**
     * @return HasMany<CfpSubmission, $this>
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(CfpSubmission::class);
    }

    public function isPublished(): bool
    {
        return $this->status === CfpStatus::Published;
    }

    public function isInternal(): bool
    {
        return $this->mode === CfpMode::Internal;
    }

    public function isExternal(): bool
    {
        return $this->mode === CfpMode::External;
    }

    public function isOpen(): bool
    {
        return $this->opens_at->isPast() && $this->closes_at->isFuture();
    }
}
