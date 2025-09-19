<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated roles with filters.
     */
    public function getPaginatedRoles(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['users']);

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (isset($filters['permission'])) {
            $query->whereJsonContains('permissions', $filters['permission']);
        }

        return $query->orderBy('name')->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get all roles with filters.
     */
    public function getAllRoles(array $filters): Collection
    {
        $query = $this->model->with(['users']);

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Get role by ID with relationships.
     */
    public function getRoleById(int $id): ?Role
    {
        return $this->model->with(['users'])->find($id);
    }

    /**
     * Create a new role.
     */
    public function createRole(array $data): Role
    {
        return $this->model->create($data);
    }

    /**
     * Update role.
     */
    public function updateRole(Role $role, array $data): bool
    {
        return $role->update($data);
    }

    /**
     * Delete role.
     */
    public function deleteRole(Role $role): bool
    {
        // Check if role is assigned to any users
        if ($role->users()->count() > 0) {
            throw new \Exception('Cannot delete role that is assigned to users');
        }

        return $role->delete();
    }

    /**
     * Get roles by slug.
     */
    public function getRolesBySlug(string $slug): Collection
    {
        return $this->model->where('slug', $slug)->get();
    }

    /**
     * Get active roles.
     */
    public function getActiveRoles(): Collection
    {
        return $this->model->active()->orderBy('name')->get();
    }

    /**
     * Assign role to user.
     */
    public function assignRoleToUser(int $userId, int $roleId, ?int $assignedBy = null): bool
    {
        $user = User::find($userId);
        $role = $this->model->find($roleId);

        if (!$user || !$role) {
            return false;
        }

        // Check if user already has this role
        if ($user->roles()->where('role_id', $roleId)->exists()) {
            return false;
        }

        $user->roles()->attach($roleId, [
            'assigned_by' => $assignedBy,
            'assigned_at' => now(),
            'is_active' => true,
        ]);

        return true;
    }

    /**
     * Remove role from user.
     */
    public function removeRoleFromUser(int $userId, int $roleId): bool
    {
        $user = User::find($userId);
        
        if (!$user) {
            return false;
        }

        $user->roles()->detach($roleId);
        return true;
    }

    /**
     * Get user roles.
     */
    public function getUserRoles(int $userId): Collection
    {
        $user = User::find($userId);
        
        if (!$user) {
            return collect();
        }

        return $user->roles()->wherePivot('is_active', true)->get();
    }

    /**
     * Get user role IDs.
     */
    public function getUserRoleIds(int $userId): array
    {
        return $this->getUserRoles($userId)->pluck('id')->toArray();
    }

    /**
     * Check if user has specific role.
     */
    public function hasRole(int $userId, string $roleSlug): bool
    {
        $user = User::find($userId);
        
        if (!$user) {
            return false;
        }

        return $user->roles()
                   ->where('slug', $roleSlug)
                   ->wherePivot('is_active', true)
                   ->exists();
    }

    /**
     * Check if user has any of the specified roles.
     */
    public function hasAnyRole(int $userId, array $roleSlugs): bool
    {
        $user = User::find($userId);
        
        if (!$user) {
            return false;
        }

        return $user->roles()
                   ->whereIn('slug', $roleSlugs)
                   ->wherePivot('is_active', true)
                   ->exists();
    }

    /**
     * Check if user has all of the specified roles.
     */
    public function hasAllRoles(int $userId, array $roleSlugs): bool
    {
        $user = User::find($userId);
        
        if (!$user) {
            return false;
        }

        $userRoleSlugs = $user->roles()
                             ->wherePivot('is_active', true)
                             ->pluck('slug')
                             ->toArray();

        return count(array_intersect($roleSlugs, $userRoleSlugs)) === count($roleSlugs);
    }

    /**
     * Get role statistics.
     */
    public function getRoleStatistics(): array
    {
        $totalRoles = $this->model->count();
        $activeRoles = $this->model->active()->count();
        $inactiveRoles = $totalRoles - $activeRoles;

        $roleUserCounts = $this->model->withCount('users')->get();
        $mostUsedRole = $roleUserCounts->sortByDesc('users_count')->first();

        return [
            'total_roles' => $totalRoles,
            'active_roles' => $activeRoles,
            'inactive_roles' => $inactiveRoles,
            'most_used_role' => $mostUsedRole ? [
                'name' => $mostUsedRole->name,
                'user_count' => $mostUsedRole->users_count
            ] : null,
            'roles_with_users' => $roleUserCounts->where('users_count', '>', 0)->count(),
        ];
    }

    /**
     * Search roles.
     */
    public function searchRoles(string $query, array $filters = []): LengthAwarePaginator
    {
        $searchQuery = $this->model->where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('slug', 'like', "%{$query}%")
              ->orWhere('description', 'like', "%{$query}%");
        });

        // Apply additional filters
        foreach ($filters as $key => $value) {
            if ($value !== null) {
                $searchQuery->where($key, $value);
            }
        }

        return $searchQuery->with(['users'])
                          ->orderBy('name')
                          ->paginate($filters['per_page'] ?? 15);
    }
}