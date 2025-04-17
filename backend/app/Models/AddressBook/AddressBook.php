<?php

namespace App\Models\AddressBook;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddressBook extends Model
{
    protected $fillable = [
        'city_id',
        'region_id',
        'province_id',
        'addrress_line'
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
