<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CfpSchema extends Model
{
    protected $table = 'cfp_schemas';

    protected $fillable = [
        'title',
        'community_id',
    ];

    /**
     * @return HasMany<Cfp, $this>
     */
    public function cfps(): HasMany
    {
        return $this->hasMany(Cfp::class);
    }
}
