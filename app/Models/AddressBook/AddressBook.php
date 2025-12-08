<?php

declare(strict_types=1);

namespace App\Models\AddressBook;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property City $city
 * @property Province $province
 * @property Region $region
 */
class AddressBook extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_id',
        'region_id',
        'province_id',
        'addrress_line',
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
