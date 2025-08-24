<?php

namespace App\Http\Controllers\Api\AdminAcademics;

use App\Http\Controllers\Controller;
use App\Models\AdminAcademics\ClassRoom;
use App\Models\AdminAcademics\Campus;
use App\Models\AdminAcademics\Course;
use App\Models\AdminAcademics\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class ClassController extends Controller
{
    /**
     * Display a listing of classes.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ClassRoom::with(['campus.school', 'course.department.faculty', 'subjects', 'teachers']);

        // Filter by campus if provided
        if ($request->has('campus_id')) {
            $query->where('campus_id', $request->campus_id);
        }

        // Filter by course if provided
        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Filter by school if provided
        if ($request->has('school_id')) {
            $query->whereHas('campus', function($q) use ($request) {
                $q->where('school_id', $request->school_id);
            });
        }

        $classes = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $classes,
            'message' => 'Classes retrieved successfully'
        ]);
    }

    /**
     * Store a newly created class.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'campus_id' => 'required|exists:campuses,id',
                'course_id' => 'required|exists:courses,id',
                'name' => 'required|string|max:255',
                'capacity' => 'required|integer|min:1',
            ]);

            $class = ClassRoom::create($validated);

            return response()->json([
                'success' => true,
                'data' => $class->load(['campus.school', 'course.department.faculty', 'subjects', 'teachers']),
                'message' => 'Class created successfully'
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
                'message' => 'Failed to create class',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified class.
     */
    public function show(ClassRoom $class): JsonResponse
    {
        $class->load([
            'campus.school',
            'course.department.faculty',
            'subjects.coordinator',
            'teachers',
            'timetables.subject',
            'exams',
            'studentEnrollments.student',
            'teacherAssignments.teacher'
        ]);

        return response()->json([
            'success' => true,
            'data' => $class,
            'message' => 'Class retrieved successfully'
        ]);
    }

    /**
     * Update the specified class.
     */
    public function update(Request $request, ClassRoom $class): JsonResponse
    {
        try {
            $validated = $request->validate([
                'campus_id' => 'sometimes|required|exists:campuses,id',
                'course_id' => 'sometimes|required|exists:courses,id',
                'name' => 'sometimes|required|string|max:255',
                'capacity' => 'sometimes|required|integer|min:1',
            ]);

            $class->update($validated);

            return response()->json([
                'success' => true,
                'data' => $class->fresh()->load(['campus.school', 'course.department.faculty', 'subjects', 'teachers']),
                'message' => 'Class updated successfully'
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
                'message' => 'Failed to update class',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified class.
     */
    public function destroy(ClassRoom $class): JsonResponse
    {
        try {
            // Check if class has any related data
            if ($class->timetables()->exists() || $class->exams()->exists() || 
                $class->studentEnrollments()->exists() || $class->teacherAssignments()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete class with related data. Please remove related records first.'
                ], 422);
            }

            $class->delete();

            return response()->json([
                'success' => true,
                'message' => 'Class deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete class',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign a subject to a class.
     */
    public function assignSubject(Request $request, ClassRoom $class): JsonResponse
    {
        try {
            $validated = $request->validate([
                'subject_id' => 'required|exists:subjects,id',
                'teacher_id' => 'nullable|exists:users,id',
            ]);

            // Check if subject is already assigned
            if ($class->subjects()->where('subject_id', $validated['subject_id'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subject is already assigned to this class'
                ], 422);
            }

            $class->subjects()->attach($validated['subject_id'], [
                'teacher_id' => $validated['teacher_id'] ?? null
            ]);

            return response()->json([
                'success' => true,
                'data' => $class->fresh()->load(['subjects.coordinator']),
                'message' => 'Subject assigned to class successfully'
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
                'message' => 'Failed to assign subject to class',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a subject from a class.
     */
    public function removeSubject(ClassRoom $class, Subject $subject): JsonResponse
    {
        try {
            $class->subjects()->detach($subject->id);

            return response()->json([
                'success' => true,
                'message' => 'Subject removed from class successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove subject from class',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign a teacher to a class.
     */
    public function assignTeacher(Request $request, ClassRoom $class): JsonResponse
    {
        try {
            $validated = $request->validate([
                'subject_id' => 'required|exists:subjects,id',
                'teacher_id' => 'required|exists:users,id',
            ]);

            // Check if subject is assigned to class
            if (!$class->subjects()->where('subject_id', $validated['subject_id'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subject is not assigned to this class'
                ], 422);
            }

            // Update the pivot table
            $class->subjects()->updateExistingPivot($validated['subject_id'], [
                'teacher_id' => $validated['teacher_id']
            ]);

            return response()->json([
                'success' => true,
                'data' => $class->fresh()->load(['subjects.coordinator']),
                'message' => 'Teacher assigned to class successfully'
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
                'message' => 'Failed to assign teacher to class',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get class statistics.
     */
    public function statistics(ClassRoom $class): JsonResponse
    {
        $stats = [
            'total_subjects' => $class->subjects()->count(),
            'total_teachers' => $class->teachers()->count(),
            'total_students' => $class->studentEnrollments()->count(),
            'total_timetables' => $class->timetables()->count(),
            'total_exams' => $class->exams()->count(),
            'enrollment_rate' => $class->studentEnrollments()->count() / $class->capacity * 100,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Class statistics retrieved successfully'
        ]);
    }
} 