<?php

namespace App\Models\AddressBook;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class City extends Model
{
    protected $fillable = [
        'name',
        'cap',
        'province_id'
    ];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }
}
