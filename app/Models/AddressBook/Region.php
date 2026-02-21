<?php

declare(strict_types=1);

namespace App\Models\AddressBook;

use Database\Factories\AddressBook\RegionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Region extends Model
{
    /** @use HasFactory<RegionFactory> */
    use HasFactory;

    use Searchable;

    protected $fillable = [
        'name',
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
}
