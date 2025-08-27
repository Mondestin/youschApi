<?php

namespace App\Http\Controllers\Api\AdminAcademics;

use App\Http\Controllers\Controller;
use App\Models\AdminAcademics\Campus;
use App\Models\AdminAcademics\School;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CampusController extends Controller
{
    /**
     * Display a listing of campuses.
     * @group Admin Academics
     */
    public function index(Request $request): JsonResponse
    {
        $query = Campus::with(['school', 'classes']);

        // Filter by school if provided
        if ($request->has('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $campuses = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $campuses,
            'message' => 'Campuses retrieved successfully'
        ]);
    }

    /**
     * Store a newly created campus.
     * @group Admin Academics
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'school_id' => 'required|exists:schools,id',
                'name' => 'required|string|max:255',
                'address' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'is_active' => 'boolean',
            ]);

            $campus = Campus::create($validated);

            return response()->json([
                'success' => true,
                'data' => $campus->load(['school', 'classes']),
                'message' => 'Campus created successfully'
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
                'message' => 'Failed to create campus',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified campus.
     * @group Admin Academics
     */
    public function show(Campus $campus): JsonResponse
    {
        $campus->load(['school', 'classes.course', 'schoolAdmins.user']);

        return response()->json([
            'success' => true,
            'data' => $campus,
            'message' => 'Campus retrieved successfully'
        ]);
    }

    /**
     * Update the specified campus.
     * @group Admin Academics
     */
    public function update(Request $request, Campus $campus): JsonResponse
    {
        try {
            $validated = $request->validate([
                'school_id' => 'sometimes|required|exists:schools,id',
                'name' => 'sometimes|required|string|max:255',
                'address' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'is_active' => 'sometimes|boolean',
            ]);

            $campus->update($validated);

            return response()->json([
                'success' => true,
                'data' => $campus->fresh()->load(['school', 'classes']),
                'message' => 'Campus updated successfully'
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
                'message' => 'Failed to update campus',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified campus.
     * @group Admin Academics
     */
    public function destroy(Campus $campus): JsonResponse
    {
        try {
            // Check if campus has any related data
            if ($campus->classes()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete campus with related classes. Please remove related records first.'
                ], 422);
            }

            $campus->delete();

            return response()->json([
                'success' => true,
                'message' => 'Campus deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete campus',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get campus statistics.
     * @group Admin Academics
     */
    public function statistics(Campus $campus): JsonResponse
    {
        $stats = [
            'total_classes' => $campus->classes()->count(),
            'total_students' => $campus->classes()->withCount('studentEnrollments')->get()->sum('student_enrollments_count'),
            'total_teachers' => $campus->classes()->withCount('teacherAssignments')->get()->sum('teacher_assignments_count'),
            'total_subjects' => $campus->classes()->withCount('subjects')->get()->sum('subjects_count'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Campus statistics retrieved successfully'
        ]);
    }

    /**
     * Get campuses by school.
     * @group Admin Academics
    */
    public function bySchool(School $school): JsonResponse
    {
        $campuses = $school->campuses()
                          ->with(['classes.course'])
                          ->where('is_active', true)
                          ->get();

        return response()->json([
            'success' => true,
            'data' => $campuses,
            'message' => 'Campuses retrieved successfully'
        ]);
    }
} 