<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
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
     * Get the roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
                    ->withPivot('assigned_by', 'assigned_at', 'is_active')
                    ->withTimestamps();
    }

    /**
     * Get the user's active roles.
     */
    public function activeRoles(): BelongsToMany
    {
        return $this->roles()->wherePivot('is_active', true);
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $roleSlug): bool
    {
        return $this->activeRoles()->where('slug', $roleSlug)->exists();
    }

    /**
     * Check if user has any of the specified roles.
     */
    public function hasAnyRole(array $roleSlugs): bool
    {
        return $this->activeRoles()->whereIn('slug', $roleSlugs)->exists();
    }

    /**
     * Check if user has all of the specified roles.
     */
    public function hasAllRoles(array $roleSlugs): bool
    {
        $userRoleSlugs = $this->activeRoles()->pluck('slug')->toArray();
        return count(array_intersect($roleSlugs, $userRoleSlugs)) === count($roleSlugs);
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->activeRoles()->get()->some(function ($role) use ($permission) {
            return $role->hasPermission($permission);
        });
    }

    /**
     * Get user's role names.
     */
    public function getRoleNames(): array
    {
        return $this->activeRoles()->pluck('name')->toArray();
    }

    /**
     * Get user's role slugs.
     */
    public function getRoleSlugs(): array
    {
        return $this->activeRoles()->pluck('slug')->toArray();
    }
}
