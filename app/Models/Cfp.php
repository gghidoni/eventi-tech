<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cfp extends Model
{
    protected $fillable = [
        'event_id',
        'closes_at',
        'opens_at',
        'cfp_url',
        'cfp_schema_id',
    ];

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return BelongsTo<CfpSchema, $this>
     */
    public function schema(): BelongsTo
    {
        return $this->belongsTo(CfpSchema::class);
    }
}
