<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EventStatus;
use App\Models\AddressBook\AddressBook;
use Carbon\Carbon;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Laravel\Scout\Searchable;

class Event extends Model
{
    /** @use HasFactory<EventFactory> */
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
        'poster_mobile',
        'poster_thumb',
        'tickets_url',
        'cfp_url',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
        'type'       => \App\Enums\EventType::class,
        'status'     => EventStatus::class,
    ];

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $array = $this->toArray();
        $array['title'] = $this->title;
        $array['description'] = $this->description;

        /** @var AddressBook|null $addressBook */
        $addressBook = $this->address_book;
        $array['city_id'] = $addressBook?->city_id;
        $array['province_id'] = $addressBook?->province_id;
        $array['region_id'] = $addressBook?->region_id;

        $array['start_date'] = $this->start_date;
        $array['end_date'] = $this->end_date;

        return $array;
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', EventStatus::Active->value);
    }

    /**
     * @return BelongsTo<Community, $this>
     */
    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    /**
     * @return BelongsTo<AddressBook, $this>
     */
    public function address_book(): BelongsTo
    {
        return $this->belongsTo(AddressBook::class);
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function bookmarks(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function getPosterImgAttribute(): string
    {
        if ($this->poster) {
            return Storage::disk('posters')->url($this->poster);
        }

        $num = ($this->id % 9) + 1;

        return Storage::disk('posters')->url("placeholder-{$num}.webp");
    }

    public function getPosterMobileImgAttribute(): string
    {
        if ($this->poster_mobile) {
            return Storage::disk('posters')->url($this->poster_mobile);
        }

        $num = ($this->id % 9) + 1;

        return Storage::disk('posters')->url("mobile/placeholder-{$num}.webp");
    }

    public function getPosterThumbImgAttribute(): string
    {
        if ($this->poster_thumb) {
            return Storage::disk('posters')->url($this->poster_thumb);
        }

        $num = ($this->id % 9) + 1;

        return Storage::disk('posters')->url("thumbs/placeholder-{$num}.webp");
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
        return url('/events/'.$this->id);
    }

    public function getEditUrlAttribute(): string
    {
        return url('/dashboard/events/'.$this->id.'/edit');
    }

    public function getIsMineAttribute(): bool
    {
        /** @var Community $community */
        $community = $this->community;

        return $community->user_id === auth()->id();
    }

    // #[Scope]
    // protected function filter(Builder $builder, QueryFilter $filters)
    // {
    //     return $filters->apply($builder);
    // }
}
