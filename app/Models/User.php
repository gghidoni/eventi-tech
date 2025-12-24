<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    // use HasApiTokens, HasFactory, Notifiable;
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'website',
        'facebook',
        'linkedin',
        'instagram',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Relationship Community
     */
    public function communities(): HasMany
    {
        return $this->hasMany(Community::class);
    }

    /**
     * Relationship Bookmarks
     */
    public function bookmarks(): BelongsToMany
    {
        return $this->belongsToMany(Event::class);
    }

    // public function canAccessPanel(Panel $panel): bool
    // {
    //     if ($panel->getId() === 'admin') {
    //         return $this->is_admin;
    //     }
    //     if ($panel->getId() === 'community') {
    //         return $this->communities()->exists();
    //     }

    //     return true;
    // }

    protected function getAvatarImgAttribute(): string
    {
        if ($this->avatar) {
            return Storage::url($this->avatar);
        }
        $name = urlencode($this->name);

        return "https://ui-avatars.com/api/?name={$name}&background=random&color=fff";
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }
}
