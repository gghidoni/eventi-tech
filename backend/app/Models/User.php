<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
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

    /**
     * Get roles
     * @return HasOne
     */
    public function user_metas(): HasOne
    {
        return $this->hasOne(UserMeta::class);
    }

    /**
     * Get roles
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * check if users has at least one og a given array of roles
     *
     * @return bool
     */
    public function hasRoles($roles)
    {
        return $this->whereAs('roles', function($role) use ($roles) {
            $role->whereIn('slug', $roles);
        })->exists();




        // foreach ($roles as $role) {
        //     if ($this->hasRole($role)) {
        //         return true;
        //     }
        // }

        // return false;
    }

    /**
     * Check if user has role
     *
     * @param  string  $role
     * @return bool
     */
    public function hasRole($role)
    {
        $roles = [];
        foreach ($this->roles->toArray() as $userRole) {
            array_push($roles, $userRole['slug']);
        }

        return in_array($role, $roles);
    }

    public function getAvatarAttribute(): String
    {
        if ($this->user_metas->avatar) {
            return Storage::url($this->avatar);
        } else {
            $name = urlencode($this->name);
            return "https://ui-avatars.com/api/?name={$name}&background=random&color=fff";
        }
    }
}
