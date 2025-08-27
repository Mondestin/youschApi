<?php

namespace App\Http\Controllers\Api\AdminAcademics;

use App\Http\Controllers\Controller;
use App\Models\AdminAcademics\Department;
use App\Models\AdminAcademics\Faculty;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments.
     * @group Admin Academics
    */
    public function index(Request $request): JsonResponse
    {
        $query = Department::with(['faculty.school', 'head', 'courses']);

        // Filter by faculty if provided
        if ($request->has('faculty_id')) {
            $query->where('faculty_id', $request->faculty_id);
        }

        // Filter by school if provided
        if ($request->has('school_id')) {
            $query->whereHas('faculty', function($q) use ($request) {
                $q->where('school_id', $request->school_id);
            });
        }

        $departments = $query->get();

        return response()->json([
            'success' => true,
            'data' => $departments,
            'message' => 'Departments retrieved successfully'
        ]);
    }

    /**
     * Store a newly created department.
     * @group Admin Academics
    */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'faculty_id' => 'required|exists:faculties,id',
                'name' => 'required|string|max:255',
                'head_id' => 'nullable|exists:users,id',
            ]);

            $department = Department::create($validated);

            return response()->json([
                'success' => true,
                'data' => $department->load(['faculty.school', 'head', 'courses']),
                'message' => 'Department created successfully'
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
                'message' => 'Failed to create department',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified department.
     * @group Admin Academics
    */
    public function show(Department $department): JsonResponse
    {
        $department->load(['faculty.school', 'head', 'courses.subjects']);

        return response()->json([
            'success' => true,
            'data' => $department,
            'message' => 'Department retrieved successfully'
        ]);
    }

    /**
     * Update the specified department.
     * @group Admin Academics
    */
    public function update(Request $request, Department $department): JsonResponse
    {
        try {
            $validated = $request->validate([
                'faculty_id' => 'sometimes|required|exists:faculties,id',
                'name' => 'sometimes|required|string|max:255',
                'head_id' => 'nullable|exists:users,id',
            ]);

            $department->update($validated);

            return response()->json([
                'success' => true,
                'data' => $department->fresh()->load(['faculty.school', 'head', 'courses']),
                'message' => 'Department updated successfully'
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
                'message' => 'Failed to update department',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified department.
     * @group Admin Academics
    */
    public function destroy(Department $department): JsonResponse
    {
        try {
            // Check if department has any related data
            if ($department->courses()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete department with related courses. Please remove related records first.'
                ], 422);
            }

            $department->delete();

            return response()->json([
                'success' => true,
                'message' => 'Department deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete department',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign a department head.
     * @group Admin Academics
    */
    public function assignHead(Request $request, Department $department): JsonResponse
    {
        try {
            $validated = $request->validate([
                'head_id' => 'required|exists:users,id',
            ]);

            $department->update(['head_id' => $validated['head_id']]);

            return response()->json([
                'success' => true,
                'data' => $department->fresh()->load(['faculty.school', 'head', 'courses']),
                'message' => 'Department head assigned successfully'
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
                'message' => 'Failed to assign department head',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get departments by faculty.
     * @group Admin Academics
    */
    public function byFaculty(Faculty $faculty): JsonResponse
    {
        $departments = $faculty->departments()
                              ->with(['head', 'courses.subjects'])
                              ->get();

        return response()->json([
            'success' => true,
            'data' => $departments,
            'message' => 'Departments retrieved successfully'
        ]);
    }

    /**
     * Get statistics for a department.
     * @group Admin Academics
    */
    public function statistics(Department $department): JsonResponse
    {
        $statistics = $department->statistics();

        return response()->json([
            'success' => true,
            'data' => $statistics,
            'message' => 'Department statistics retrieved successfully'
        ]);
    }
} 