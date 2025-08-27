<?php

namespace App\Http\Controllers\Api\AdminAcademics;

use App\Http\Controllers\Controller;
use App\Models\AdminAcademics\Subject;
use App\Models\AdminAcademics\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{
    /**
     * Display a listing of subjects.
     * @group Admin Academics
    */
    public function index(Request $request): JsonResponse
    {
        $query = Subject::with(['course.department.faculty.school', 'coordinator', 'labs']);

        // Filter by course if provided
        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Filter by department if provided
        if ($request->has('department_id')) {
            $query->whereHas('course', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        // Filter by faculty if provided
        if ($request->has('faculty_id')) {
            $query->whereHas('course.department', function($q) use ($request) {
                $q->where('faculty_id', $request->faculty_id);
            });
        }

        // Filter by school if provided
        if ($request->has('school_id')) {
            $query->whereHas('course.department.faculty', function($q) use ($request) {
                $q->where('school_id', $request->school_id);
            });
        }

        $subjects = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $subjects,
            'message' => 'Subjects retrieved successfully'
        ]);
    }

    /**
     * Store a newly created subject.
     * @group Admin Academics
    */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'course_id' => 'required|exists:courses,id',
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:subjects',
                'description' => 'nullable|string',
                'coordinator_id' => 'nullable|exists:users,id',
            ]);

            $subject = Subject::create($validated);

            return response()->json([
                'success' => true,
                'data' => $subject->load(['course.department.faculty.school', 'coordinator', 'labs']),
                'message' => 'Subject created successfully'
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
                'message' => 'Failed to create subject',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified subject.
     * @group Admin Academics
    */
    public function show(Subject $subject): JsonResponse
    {
        $subject->load([
            'course.department.faculty.school',
            'coordinator',
            'labs',
            'prerequisites',
            'requiredBy',
            'classes.campus',
            'exams',
            'studentGrades'
        ]);

        return response()->json([
            'success' => true,
            'data' => $subject,
            'message' => 'Subject retrieved successfully'
        ]);
    }

    /**
     * Update the specified subject.
     * @group Admin Academics
    */
    public function update(Request $request, Subject $subject): JsonResponse
    {
        try {
            $validated = $request->validate([
                'course_id' => 'sometimes|required|exists:courses,id',
                'name' => 'sometimes|required|string|max:255',
                'code' => 'sometimes|required|string|max:50|unique:subjects,code,' . $subject->id,
                'description' => 'nullable|string',
                'coordinator_id' => 'nullable|exists:users,id',
            ]);

            $subject->update($validated);

            return response()->json([
                'success' => true,
                'data' => $subject->fresh()->load(['course.department.faculty.school', 'coordinator', 'labs']),
                'message' => 'Subject updated successfully'
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
                'message' => 'Failed to update subject',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified subject.
     * @group Admin Academics
    */
    public function destroy(Subject $subject): JsonResponse
    {
        try {
            // Check if subject has any related data
            if ($subject->labs()->exists() || $subject->exams()->exists() || $subject->studentGrades()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete subject with related data. Please remove related records first.'
                ], 422);
            }

            $subject->delete();

            return response()->json([
                'success' => true,
                'message' => 'Subject deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete subject',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign a subject coordinator.
     * @group Admin Academics
    */
    public function assignCoordinator(Request $request, Subject $subject): JsonResponse
    {
        try {
            $validated = $request->validate([
                'coordinator_id' => 'required|exists:users,id',
            ]);

            $subject->update(['coordinator_id' => $validated['coordinator_id']]);

            return response()->json([
                'success' => true,
                'data' => $subject->fresh()->load(['course.department.faculty.school', 'coordinator', 'labs']),
                'message' => 'Subject coordinator assigned successfully'
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
                'message' => 'Failed to assign subject coordinator',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add prerequisites to a subject.
     * @group Admin Academics
    */
    public function addPrerequisites(Request $request, Subject $subject): JsonResponse
    {
        try {
            $validated = $request->validate([
                'prerequisite_ids' => 'required|array',
                'prerequisite_ids.*' => 'exists:subjects,id',
            ]);

            // Prevent circular dependencies
            foreach ($validated['prerequisite_ids'] as $prerequisiteId) {
                if ($prerequisiteId == $subject->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Subject cannot be its own prerequisite'
                    ], 422);
                }
            }

            $subject->prerequisites()->attach($validated['prerequisite_ids']);

            return response()->json([
                'success' => true,
                'data' => $subject->fresh()->load(['prerequisites']),
                'message' => 'Prerequisites added successfully'
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
                'message' => 'Failed to add prerequisites',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a prerequisite from a subject.
     * @group Admin Academics
    */
    public function removePrerequisite(Subject $subject, Subject $prerequisite): JsonResponse
    {
        try {
            $subject->prerequisites()->detach($prerequisite->id);

            return response()->json([
                'success' => true,
                'message' => 'Prerequisite removed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove prerequisite',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subjects by course.
     * @group Admin Academics
    */
    public function byCourse(Course $course): JsonResponse
    {
        $subjects = $course->subjects()
                          ->with(['coordinator', 'labs', 'prerequisites'])
                          ->get();

        return response()->json([
            'success' => true,
            'data' => $subjects,
            'message' => 'Subjects retrieved successfully'
        ]);
    }

    /**
     * Get subject statistics.
     * @group Admin Academics
    */
    public function statistics(Subject $subject): JsonResponse
    {
        $stats = [
            'total_labs' => $subject->labs()->count(),
            'total_exams' => $subject->exams()->count(),
            'total_classes' => $subject->classes()->count(),
            'total_students' => $subject->studentGrades()->distinct('student_id')->count(),
            'prerequisites_count' => $subject->prerequisites()->count(),
            'required_by_count' => $subject->requiredBy()->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Subject statistics retrieved successfully'
        ]);
    }
} 