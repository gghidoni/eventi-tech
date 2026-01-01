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
use Illuminate\Support\Facades\Storage;
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
        $array['city_id'] = $this->address_book?->city_id;
        $array['province_id'] = $this->address_book?->province_id;
        $array['region_id'] = $this->address_book?->region_id;
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
            return Storage::disk('posters')->url($this->poster);
        }

        return 'https://robohash.org/'.$this->id.'?set=set1&size=1000x1000';
    }

    public function getPosterMobileImgAttribute(): string
    {
        if ($this->poster_mobile) {
            return Storage::disk('posters')->url($this->poster_mobile);
        }

        return $this->poster ? $this->getPosterImgAttribute() : 'https://robohash.org/'.$this->id.'?set=set1&size=400x400';
    }

    public function getPosterThumbImgAttribute(): string
    {
        if ($this->poster_thumb) {
            return Storage::disk('posters')->url($this->poster_thumb);
        }

        return $this->poster ? $this->getPosterImgAttribute() : 'https://robohash.org/'.$this->id.'?set=set1&size=150x150';
    }

    public function getFormattedStartDateAttribute(): string
    {
        return Carbon::parse($this->start_date)->format('d/m/Y');
    }

    public function getFormattedDatetimeStartAttribute(): string
    {
        return Carbon::parse($this->start_date)->format('d/m/Y H:i');
    }

    public function getFormattedDatetimeEndAttribute(): string
    {
        return Carbon::parse($this->end_date)->format('d/m/Y H:i');
    }

    public function getPublicUrlAttribute(): string
    {
        return '/events/'.$this->id;
    }

    // #[Scope]
    // protected function filter(Builder $builder, QueryFilter $filters)
    // {
    //     return $filters->apply($builder);
    // }
}
