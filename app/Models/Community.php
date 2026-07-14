<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CommunityStatus;
use Database\Factories\CommunityFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $slug
 * @property CommunityStatus $status
 * @property string $description
 * @property string|null $website
 * @property string|null $logo
 * @property string|null $linkedin
 * @property string|null $instagram
 * @property string|null $facebook
 * @property string|null $phone
 * @property User $user
 */
class Community extends Model
{
    /** @use HasFactory<CommunityFactory> */
    use HasFactory;

    protected $casts = [
        'status' => CommunityStatus::class,
    ];

    protected $fillable = [
        'user_id',
        'name',
        'logo',
        'website',
        'slug',
        'facebook',
        'linkedin',
        'instagram',
        'description',
        'phone',
    ];

    /**
     * Relationship User
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Event, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * @return HasMany<CfpTemplate, $this>
     */
    public function cfpTemplates(): HasMany
    {
        return $this->hasMany(CfpTemplate::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function favoritedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('status', CommunityStatus::Active->value);
    }

    public function isPubliclyVisible(): bool
    {
        return $this->status === CommunityStatus::Active;
    }

    public function getPublicUrlAttribute(): string
    {
        return url('/communities/'.$this->id);
    }

    public function getEditUrlAttribute(): string
    {
        return url('/dashboard/communities/'.$this->id.'/edit');
    }

    public function getIsMineAttribute(): bool
    {
        return $this->user_id === auth()->id();
    }

    protected function getLogoImgAttribute(): string
    {
        if ($this->logo) {
            return Storage::disk('logos')->url($this->logo);
        }
        $name = urlencode($this->name);

        return "https://ui-avatars.com/api/?name={$name}&background=random&color=fff";
    }
}
