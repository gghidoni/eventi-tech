<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CommunityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $slug
 * @property string $status
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

    public function getPublicUrlAttribute(): string
    {
        return url('/communities/'.$this->id);
    }

    public function getEditUrlAttribute(): string
    {
        return url('/dashboard/communities/'.$this->id.'/edit');
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
