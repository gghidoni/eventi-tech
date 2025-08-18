<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

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
        'is_admin'
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
   

    public function getAvatarImgAttribute(): String
    {
        if ($this->avatar) {
            return Storage::url($this->avatar);
        } else {
            $name = urlencode($this->name);
            return "https://ui-avatars.com/api/?name={$name}&background=random&color=fff";
        }
    }

    
    /**
     * Relationship Community
     * 
     * @return hasMany
     */
    public function communities(): HasMany
    {
        return $this->hasMany(Community::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() == 'admin') return $this->is_admin;
        if ($panel->getId() == 'community') return $this->communities()->exists();
        return true;
    }
}
