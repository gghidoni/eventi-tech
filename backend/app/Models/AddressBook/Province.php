<?php

namespace App\Models\AddressBook;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Province extends Model
{
    protected $fillable = [
        'name',
        'code',
        'region_id'
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
