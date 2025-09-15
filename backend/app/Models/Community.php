<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Community extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'logo',
        'website',
        'slug',
        'facebook',
        'linkedin',
        'instagram',
        'description',
        'phone',
    ];

    /**
     * Relationship User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
