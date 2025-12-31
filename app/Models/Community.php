<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Community extends Model
{
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
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    protected function getLogoImgAttribute(): string
    {
        if ($this->logo) {
            return Storage::url($this->logo);
        }
        $name = urlencode($this->name);

        return "https://ui-avatars.com/api/?name={$name}&background=random&color=fff";
    }

    public function getPublicUrlAttribute(): string
    {
        return url('/communities/' . $this->id);
    }

    public function getEditUrlAttribute(): string
    {
        return url('/dashboard/communities/edit/' . $this->id);
    }
}
