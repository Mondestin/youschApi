<?php

namespace App\Http\Controllers\Api\AdminAcademics;

use App\Http\Controllers\Controller;
use App\Models\AdminAcademics\Course;
use App\Models\AdminAcademics\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CourseController extends Controller
{
    /**
     * Display a listing of courses.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Course::with(['department.faculty.school', 'subjects', 'classes']);

        // Filter by department if provided
        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Filter by faculty if provided
        if ($request->has('faculty_id')) {
            $query->whereHas('department', function($q) use ($request) {
                $q->where('faculty_id', $request->faculty_id);
            });
        }

        // Filter by school if provided
        if ($request->has('school_id')) {
            $query->whereHas('department.faculty', function($q) use ($request) {
                $q->where('school_id', $request->school_id);
            });
        }

        $courses = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $courses,
            'message' => 'Courses retrieved successfully'
        ]);
    }

    /**
     * Store a newly created course.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'department_id' => 'required|exists:departments,id',
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:courses',
                'description' => 'nullable|string',
            ]);

            $course = Course::create($validated);

            return response()->json([
                'success' => true,
                'data' => $course->load(['department.faculty.school', 'subjects', 'classes']),
                'message' => 'Course created successfully'
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
                'message' => 'Failed to create course',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified course.
     */
    public function show(Course $course): JsonResponse
    {
        $course->load([
            'department.faculty.school',
            'subjects.coordinator',
            'subjects.prerequisites',
            'classes.campus',
            'classes.studentEnrollments'
        ]);

        return response()->json([
            'success' => true,
            'data' => $course,
            'message' => 'Course retrieved successfully'
        ]);
    }

    /**
     * Update the specified course.
     */
    public function update(Request $request, Course $course): JsonResponse
    {
        try {
            $validated = $request->validate([
                'department_id' => 'sometimes|required|exists:departments,id',
                'name' => 'sometimes|required|string|max:255',
                'code' => 'sometimes|required|string|max:50|unique:courses,code,' . $course->id,
                'description' => 'nullable|string',
            ]);

            $course->update($validated);

            return response()->json([
                'success' => true,
                'data' => $course->fresh()->load(['department.faculty.school', 'subjects', 'classes']),
                'message' => 'Course updated successfully'
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
                'message' => 'Failed to update course',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified course.
     */
    public function destroy(Course $course): JsonResponse
    {
        try {
            // Check if course has any related data
            if ($course->subjects()->exists() || $course->classes()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete course with related subjects or classes. Please remove related records first.'
                ], 422);
            }

            $course->delete();

            return response()->json([
                'success' => true,
                'message' => 'Course deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete course',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get courses by department.
     */
    public function byDepartment(Department $department): JsonResponse
    {
        $courses = $department->courses()
                            ->with(['subjects', 'classes.campus'])
                            ->get();

        return response()->json([
            'success' => true,
            'data' => $courses,
            'message' => 'Courses retrieved successfully'
        ]);
    }

    /**
     * Get course statistics.
     */
    public function statistics(Course $course): JsonResponse
    {
        $stats = [
            'total_subjects' => $course->subjects()->count(),
            'total_classes' => $course->classes()->count(),
            'total_students' => $course->classes()->withCount('studentEnrollments')->get()->sum('student_enrollments_count'),
            'total_teachers' => $course->classes()->withCount('teacherAssignments')->get()->sum('teacher_assignments_count'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Course statistics retrieved successfully'
        ]);
    }
} 