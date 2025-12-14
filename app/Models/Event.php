<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EventStatus;
use App\Models\AddressBook\AddressBook;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Event extends Model
{
    use HasFactory;
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'community_id',
        'title',
        'status',
        'description',
        'type',
        'address_book_id',
        'start_date',
        'end_date',
        'website',
        'poster',
        'tickets_url',
        'cfp_url',
    ];

    public function toSearchableArray()
    {
        $array = $this->toArray();
        $array['title'] = $this->title;
        $array['description'] = $this->description;
        $array['city_id'] = $this->address_book->city_id;
        $array['province_id'] = $this->address_book->province_id;
        $array['region_id'] = $this->address_book->region_id;
        $array['start_date'] = $this->start_date;
        $array['end_date'] = $this->end_date;

        return $array;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', EventStatus::Active->value);
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function address_book(): BelongsTo
    {
        return $this->belongsTo(AddressBook::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function getPosterImgAttribute(): string
    {
        if ($this->poster) {
            return asset('storage/' . $this->poster);
        }
        return 'https://robohash.org/' . $this->id;
    }

    public function getFormattedStartDateAttribute(): string
    {
        return Carbon::parse($this->start_date)->format('d/m/Y');
    }

    // #[Scope]
    // protected function filter(Builder $builder, QueryFilter $filters)
    // {
    //     return $filters->apply($builder);
    // }
}
