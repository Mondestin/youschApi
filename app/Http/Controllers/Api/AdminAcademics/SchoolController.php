<?php

namespace App\Http\Controllers\Api\AdminAcademics;

use App\Http\Controllers\Controller;
use App\Models\AdminAcademics\School;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class SchoolController extends Controller
{
    /**
     * Display a listing of schools.
     * @group Admin Academics
    */
    public function index(): JsonResponse
    {
        $schools = School::with(['campuses', 'faculties', 'academicYears'])
                        ->where('is_active', true)
                        ->get();

        return response()->json([
            'success' => true,
            'data' => $schools,
            'message' => 'Schools retrieved successfully'
        ]);
    }

    /**
     * Store a newly created school.
     * @group Admin Academics
    */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'domain' => 'nullable|string|max:255|unique:schools',
                'contact_info' => 'nullable|string',
                'address' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'website' => 'nullable|url|max:255',
                'is_active' => 'boolean',
            ]);

            $school = School::create($validated);

            return response()->json([
                'success' => true,
                'data' => $school->load(['campuses', 'faculties', 'academicYears']),
                'message' => 'School created successfully'
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
                'message' => 'Failed to create school',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified school.
     * @group Admin Academics
    */
    public function show(School $school): JsonResponse
    {
        $school->load(['campuses', 'faculties', 'academicYears', 'gradingSchemes', 'schoolAdmins']);

        return response()->json([
            'success' => true,
            'data' => $school,
            'message' => 'School retrieved successfully'
        ]);
    }

    /**
     * Update the specified school.
     * @group Admin Academics
    */
    public function update(Request $request, School $school): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'domain' => 'nullable|string|max:255|unique:schools,domain,' . $school->id,
                'contact_info' => 'nullable|string',
                'address' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'website' => 'nullable|url|max:255',
                'is_active' => 'sometimes|boolean',
            ]);

            $school->update($validated);

            return response()->json([
                'success' => true,
                'data' => $school->fresh()->load(['campuses', 'faculties', 'academicYears']),
                'message' => 'School updated successfully'
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
                'message' => 'Failed to update school',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified school.
     * @group Admin Academics
    */
    public function destroy(School $school): JsonResponse
    {
        try {
            // Check if school has any related data
            if ($school->campuses()->exists() || $school->faculties()->exists() || $school->academicYears()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete school with related data. Please remove related records first.'
                ], 422);
            }

            $school->delete();

            return response()->json([
                'success' => true,
                'message' => 'School deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete school',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get school statistics.
     * @group Admin Academics
    */
    public function statistics(School $school): JsonResponse
    {
        $stats = [
            'total_campuses' => $school->campuses()->count(),
            'total_faculties' => $school->faculties()->count(),
            'total_departments' => $school->faculties()->withCount('departments')->get()->sum('departments_count'),
            'total_courses' => $school->faculties()->withCount(['departments' => function($query) {
                $query->withCount('courses');
            }])->get()->sum(function($faculty) {
                return $faculty->departments->sum('courses_count');
            }),
            'total_students' => $school->academicYears()->withCount('studentEnrollments')->get()->sum('student_enrollments_count'),
            'active_academic_year' => $school->academicYears()->where('is_active', true)->first(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'School statistics retrieved successfully'
        ]);
    }
} 