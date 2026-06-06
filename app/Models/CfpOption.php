<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CfpOption extends Model
{
    protected $table = 'cfp_options';

    protected $fillable = [
        'label',
        'options',
        'cfp_schema_id',
    ];

    /**
     * @return BelongsTo<CfpSchema, $this>
     */
    public function schema(): BelongsTo
    {
        return $this->belongsTo(CfpSchema::class);
    }
}
