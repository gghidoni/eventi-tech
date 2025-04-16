<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Role extends Model
{
    public const USER = 1;
    public const ADMIN = 2;
    public const ORGANIZER = 3;
    public const SPEAKER = 4;


    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
