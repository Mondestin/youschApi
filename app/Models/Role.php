<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'permissions',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'permissions' => 'array',
    ];

    // Role constants
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_SCHOOL_ADMIN = 'school_admin';
    const ROLE_CAMPUS_ADMIN = 'campus_admin';
    const ROLE_TEACHER = 'teacher';
    const ROLE_STUDENT = 'student';
    const ROLE_PARENT = 'parent';
    const ROLE_ACCOUNTANT = 'accountant';
    const ROLE_LIBRARIAN = 'librarian';

    /**
     * Get the users that have this role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles')
                    ->withPivot('assigned_by', 'assigned_at', 'is_active')
                    ->withTimestamps();
    }

    /**
     * Scope to get active roles.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get roles by slug.
     */
    public function scopeBySlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Check if role has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions) || in_array('*', $permissions);
    }

    /**
     * Get role display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return ucwords(str_replace('_', ' ', $this->name));
    }
}