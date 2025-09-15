<?php

declare(strict_types=1);

namespace App\Models\AddressBook;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

/**
 * @property \App\Models\AddressBook\Province $province
 */
class City extends Model
{
    use Searchable;

    protected $fillable = [
        'name',
        'cap',
        'province_id',
    ];

    public function toSearchableArray()
    {
        $array = $this->toArray();
        $array['name'] = $this->name;

        return $array;
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }
}
