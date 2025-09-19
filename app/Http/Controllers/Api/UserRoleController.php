<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Repositories\RoleRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class UserRoleController extends Controller
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository
    ) {}

    /**
     * Assign role to user.
     * @group User Role Management
     */
    public function assignRole(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'role_id' => 'required|exists:roles,id',
                'assigned_by' => 'nullable|exists:users,id'
            ]);

            $success = $this->roleRepository->assignRoleToUser(
                $validated['user_id'],
                $validated['role_id'],
                $validated['assigned_by'] ?? null
            );

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to assign role. User or role not found, or user already has this role.'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Role assigned successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove role from user.
     * @group User Role Management
     */
    public function removeRole(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'role_id' => 'required|exists:roles,id'
            ]);

            $success = $this->roleRepository->removeRoleFromUser(
                $validated['user_id'],
                $validated['role_id']
            );

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to remove role. User not found.'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Role removed successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user roles.
     * @group User Role Management
     */
    public function getUserRoles(int $userId): JsonResponse
    {
        try {
            $roles = $this->roleRepository->getUserRoles($userId);

            return response()->json([
                'success' => true,
                'data' => $roles,
                'message' => 'User roles retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if user has specific role.
     * @group User Role Management
     */
    public function hasRole(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'role_slug' => 'required|string'
            ]);

            $hasRole = $this->roleRepository->hasRole(
                $validated['user_id'],
                $validated['role_slug']
            );

            return response()->json([
                'success' => true,
                'data' => ['has_role' => $hasRole],
                'message' => 'Role check completed'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if user has any of the specified roles.
     * @group User Role Management
     */
    public function hasAnyRole(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'role_slugs' => 'required|array',
                'role_slugs.*' => 'string'
            ]);

            $hasAnyRole = $this->roleRepository->hasAnyRole(
                $validated['user_id'],
                $validated['role_slugs']
            );

            return response()->json([
                'success' => true,
                'data' => ['has_any_role' => $hasAnyRole],
                'message' => 'Role check completed'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync user roles (replace all user roles with provided ones).
     * @group User Role Management
     */
    public function syncRoles(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'role_ids' => 'required|array',
                'role_ids.*' => 'exists:roles,id',
                'assigned_by' => 'nullable|exists:users,id'
            ]);

            $user = User::find($validated['user_id']);
            $assignedBy = $validated['assigned_by'] ?? null;

            // Remove all existing roles
            $user->roles()->detach();

            // Attach new roles
            $syncData = [];
            foreach ($validated['role_ids'] as $roleId) {
                $syncData[$roleId] = [
                    'assigned_by' => $assignedBy,
                    'assigned_at' => now(),
                    'is_active' => true,
                ];
            }

            $user->roles()->attach($syncData);

            return response()->json([
                'success' => true,
                'message' => 'User roles synchronized successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}