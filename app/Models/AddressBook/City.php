<?php

declare(strict_types=1);

namespace App\Models\AddressBook;

use Database\Factories\AddressBook\CityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

/**
 * @property Province $province
 */
class City extends Model
{
    /** @use HasFactory<CityFactory> */
    use HasFactory;

    use Searchable;

    protected $fillable = [
        'name',
        'cap',
        'province_id',
    ];

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $array = $this->toArray();
        $array['name'] = $this->name;

        return $array;
    }

    /**
     * @return BelongsTo<Province, $this>
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }
}
