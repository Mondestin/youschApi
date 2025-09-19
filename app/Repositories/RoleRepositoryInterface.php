<?php

namespace App\Repositories;

use App\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface RoleRepositoryInterface
{
    public function getPaginatedRoles(array $filters): LengthAwarePaginator;
    public function getAllRoles(array $filters): Collection;
    public function getRoleById(int $id): ?Role;
    public function createRole(array $data): Role;
    public function updateRole(Role $role, array $data): bool;
    public function deleteRole(Role $role): bool;
    public function getRolesBySlug(string $slug): Collection;
    public function getActiveRoles(): Collection;
    public function assignRoleToUser(int $userId, int $roleId, ?int $assignedBy = null): bool;
    public function removeRoleFromUser(int $userId, int $roleId): bool;
    public function getUserRoles(int $userId): Collection;
    public function getUserRoleIds(int $userId): array;
    public function hasRole(int $userId, string $roleSlug): bool;
    public function hasAnyRole(int $userId, array $roleSlugs): bool;
    public function hasAllRoles(int $userId, array $roleSlugs): bool;
    public function getRoleStatistics(): array;
    public function searchRoles(string $query, array $filters = []): LengthAwarePaginator;
}