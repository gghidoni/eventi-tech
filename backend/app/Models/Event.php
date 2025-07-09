<?php

namespace App\Models;

use App\Models\AddressBook\AddressBook;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Http\Filters\V1\QueryFilter;
use Illuminate\Database\Eloquent\Builder;

class Event extends Model
{
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
        'cfp_url'
    ];

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

    public function scopeFilter(Builder $builder, QueryFilter $filters)
    {
        return $filters->apply($builder);
    }
}
