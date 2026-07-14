<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CommunityStatus;
use App\Notifications\ResetPassword;
use App\Notifications\VerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
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
        // Timestamp verifica email, valorizzato anche dai login social.
        'email_verified_at',
        // Identificativi OAuth per login social.
        'github_id',
        'google_id',
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
     *
     * @return HasMany<Community, $this>
     */
    public function communities(): HasMany
    {
        return $this->hasMany(Community::class);
    }

    /**
     * Relationship Bookmarks
     *
     * @return BelongsToMany<Event, $this>
     */
    public function bookmarks(): BelongsToMany
    {
        return $this->belongsToMany(Event::class);
    }

    /**
     * @return HasMany<CfpSubmission, $this>
     */
    public function cfpSubmissions(): HasMany
    {
        return $this->hasMany(CfpSubmission::class);
    }

    /**
     * Relationship Favorite Communities
     *
     * @return BelongsToMany<Community, $this>
     */
    public function favoriteCommunities(): BelongsToMany
    {
        return $this->belongsToMany(Community::class)->withTimestamps();
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail());
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin'
            && $this->is_admin
            && $this->hasVerifiedEmail();
    }

    public function getHasActiveCommunityAttribute(): bool
    {
        return $this->communities()->where('status', CommunityStatus::Active->value)->exists();
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
            'is_admin'          => 'boolean',
            'password'          => 'hashed',
        ];
    }

    protected function getAvatarImgAttribute(): string
    {
        if ($this->avatar) {
            return Storage::url($this->avatar);
        }
        $name = urlencode($this->name);

        return "https://ui-avatars.com/api/?name={$name}&background=random&color=fff";
    }
}
