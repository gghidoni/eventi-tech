<?php

declare(strict_types=1);

namespace App\Models\AddressBook;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Region extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'name',
    ];

    public function toSearchableArray()
    {
        $array = $this->toArray();
        $array['name'] = $this->name;

        return $array;
    }
}
