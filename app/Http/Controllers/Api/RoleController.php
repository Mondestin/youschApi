<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Repositories\RoleRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RoleController extends Controller
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository
    ) {}

    /**
     * Display a listing of roles.
     * @group Role Management
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $roles = $this->roleRepository->getPaginatedRoles($request->all());

            return response()->json([
                'success' => true,
                'data' => $roles,
                'message' => 'Roles retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created role.
     * @group Role Management
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:roles,name',
                'slug' => 'required|string|max:255|unique:roles,slug|regex:/^[a-z0-9_]+$/',
                'description' => 'nullable|string',
                'permissions' => 'nullable|array',
                'permissions.*' => 'string',
                'is_active' => 'boolean'
            ]);

            $role = $this->roleRepository->createRole($validated);

            return response()->json([
                'success' => true,
                'data' => $role,
                'message' => 'Role created successfully'
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified role.
     * @group Role Management
     */
    public function show(int $id): JsonResponse
    {
        try {
            $role = $this->roleRepository->getRoleById($id);

            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $role,
                'message' => 'Role retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified role.
     * @group Role Management
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $role = $this->roleRepository->getRoleById($id);

            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role not found'
                ], 404);
            }

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:roles,name,' . $id,
                'slug' => 'sometimes|required|string|max:255|unique:roles,slug,' . $id . '|regex:/^[a-z0-9_]+$/',
                'description' => 'nullable|string',
                'permissions' => 'nullable|array',
                'permissions.*' => 'string',
                'is_active' => 'boolean'
            ]);

            $this->roleRepository->updateRole($role, $validated);

            return response()->json([
                'success' => true,
                'data' => $role->fresh(),
                'message' => 'Role updated successfully'
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
                'message' => 'Failed to update role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified role.
     * @group Role Management
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $role = $this->roleRepository->getRoleById($id);

            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role not found'
                ], 404);
            }

            $this->roleRepository->deleteRole($role);

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all active roles.
     * @group Role Management
     */
    public function active(): JsonResponse
    {
        try {
            $roles = $this->roleRepository->getActiveRoles();

            return response()->json([
                'success' => true,
                'data' => $roles,
                'message' => 'Active roles retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve active roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get role statistics.
     * @group Role Management
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->roleRepository->getRoleStatistics();

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Role statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve role statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search roles.
     * @group Role Management
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q', '');
            $filters = $request->except(['q']);

            $roles = $this->roleRepository->searchRoles($query, $filters);

            return response()->json([
                'success' => true,
                'data' => $roles,
                'message' => 'Search results retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}