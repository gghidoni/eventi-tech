<?php

namespace App\Models;

use Database\Factories\CfpTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CfpTemplate extends Model
{
    /** @use HasFactory<CfpTemplateFactory> */
    use HasFactory;

    protected $fillable = [
        'community_id',
        'title',
        'description',
    ];

    /**
     * @return BelongsTo<Community, $this>
     */
    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    /**
     * @return HasMany<CfpTemplateField, $this>
     */
    public function fields(): HasMany
    {
        return $this->hasMany(CfpTemplateField::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<Cfp, $this>
     */
    public function cfps(): HasMany
    {
        return $this->hasMany(Cfp::class);
    }
}
