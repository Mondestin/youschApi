<?php

namespace App\Http\Controllers\Api\AdminAcademics;

use App\Http\Controllers\Controller;
use App\Models\AdminAcademics\TeacherAssignment;
use App\Models\AdminAcademics\ClassRoom;
use App\Models\AdminAcademics\Subject;
use App\Models\AdminAcademics\AcademicYear;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class TeacherAssignmentController extends Controller
{
    /**
     * Display a listing of teacher assignments.
     * @group Admin Academics
    */
    public function index(Request $request): JsonResponse
    {
        $query = TeacherAssignment::with([
            'teacher',
            'classRoom.campus.school',
            'classRoom.course.department.faculty',
            'subject.course',
            'academicYear'
        ]);

        // Filter by teacher if provided
        if ($request->has('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        // Filter by class if provided
        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by subject if provided
        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Filter by academic year if provided
        if ($request->has('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        // Filter by role if provided
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Filter by active status if provided
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by school if provided
        if ($request->has('school_id')) {
            $query->whereHas('classRoom.campus', function($q) use ($request) {
                $q->where('school_id', $request->school_id);
            });
        }

        $assignments = $query->orderBy('assignment_date', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $assignments,
            'message' => 'Teacher assignments retrieved successfully'
        ]);
    }

    /**
     * Store a newly created teacher assignment.
     * @group Admin Academics
    */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'teacher_id' => 'required|exists:users,id',
                'class_id' => 'required|exists:classes,id',
                'subject_id' => 'required|exists:subjects,id',
                'academic_year_id' => 'required|exists:academic_years,id',
                'role' => 'required|in:primary_teacher,assistant_teacher,substitute_teacher',
                'assignment_date' => 'required|date',
                'end_date' => 'nullable|date|after:assignment_date',
                'weekly_hours' => 'integer|min:0',
                'notes' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            // Check if teacher is already assigned to this class and subject for this academic year
            $existingAssignment = TeacherAssignment::where('teacher_id', $validated['teacher_id'])
                ->where('class_id', $validated['class_id'])
                ->where('subject_id', $validated['subject_id'])
                ->where('academic_year_id', $validated['academic_year_id'])
                ->where('is_active', true)
                ->exists();

            if ($existingAssignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher is already assigned to this class and subject for this academic year'
                ], 422);
            }

            // Check if there's already a primary teacher for this class and subject
            if ($validated['role'] === 'primary_teacher') {
                $primaryTeacherExists = TeacherAssignment::where('class_id', $validated['class_id'])
                    ->where('subject_id', $validated['subject_id'])
                    ->where('academic_year_id', $validated['academic_year_id'])
                    ->where('role', 'primary_teacher')
                    ->where('is_active', true)
                    ->exists();

                if ($primaryTeacherExists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A primary teacher is already assigned to this class and subject'
                    ], 422);
                }
            }

            $assignment = TeacherAssignment::create($validated);

            return response()->json([
                'success' => true,
                'data' => $assignment->load([
                    'teacher',
                    'classRoom.campus.school',
                    'classRoom.course.department.faculty',
                    'subject.course',
                    'academicYear'
                ]),
                'message' => 'Teacher assignment created successfully'
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
                'message' => 'Failed to create teacher assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified teacher assignment.
     * @group Admin Academics
    */
    public function show(TeacherAssignment $assignment): JsonResponse
    {
        $assignment->load([
            'teacher',
            'classRoom.campus.school',
            'classRoom.course.department.faculty',
            'subject.course',
            'academicYear'
        ]);

        return response()->json([
            'success' => true,
            'data' => $assignment,
            'message' => 'Teacher assignment retrieved successfully'
        ]);
    }

    /**
     * Update the specified teacher assignment.
     * @group Admin Academics
    */
    public function update(Request $request, TeacherAssignment $assignment): JsonResponse
    {
        try {
            $validated = $request->validate([
                'teacher_id' => 'sometimes|required|exists:users,id',
                'class_id' => 'sometimes|required|exists:classes,id',
                'subject_id' => 'sometimes|required|exists:subjects,id',
                'academic_year_id' => 'sometimes|required|exists:academic_years,id',
                'role' => 'sometimes|required|in:primary_teacher,assistant_teacher,substitute_teacher',
                'assignment_date' => 'sometimes|required|date',
                'end_date' => 'nullable|date|after:assignment_date',
                'weekly_hours' => 'integer|min:0',
                'notes' => 'nullable|string',
                'is_active' => 'sometimes|boolean',
            ]);

            // Check for conflicts if key fields are being changed
            if (isset($validated['teacher_id']) || isset($validated['class_id']) || 
                isset($validated['subject_id']) || isset($validated['academic_year_id'])) {
                
                $teacherId = $validated['teacher_id'] ?? $assignment->teacher_id;
                $classId = $validated['class_id'] ?? $assignment->class_id;
                $subjectId = $validated['subject_id'] ?? $assignment->subject_id;
                $academicYearId = $validated['academic_year_id'] ?? $assignment->academic_year_id;

                $existingAssignment = TeacherAssignment::where('teacher_id', $teacherId)
                    ->where('class_id', $classId)
                    ->where('subject_id', $subjectId)
                    ->where('academic_year_id', $academicYearId)
                    ->where('id', '!=', $assignment->id)
                    ->where('is_active', true)
                    ->exists();

                if ($existingAssignment) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Teacher is already assigned to this class and subject for this academic year'
                    ], 422);
                }
            }

            // Check for primary teacher conflicts if role is being changed to primary
            if (isset($validated['role']) && $validated['role'] === 'primary_teacher') {
                $classId = $validated['class_id'] ?? $assignment->class_id;
                $subjectId = $validated['subject_id'] ?? $assignment->subject_id;
                $academicYearId = $validated['academic_year_id'] ?? $assignment->academic_year_id;

                $primaryTeacherExists = TeacherAssignment::where('class_id', $classId)
                    ->where('subject_id', $subjectId)
                    ->where('academic_year_id', $academicYearId)
                    ->where('role', 'primary_teacher')
                    ->where('id', '!=', $assignment->id)
                    ->where('is_active', true)
                    ->exists();

                if ($primaryTeacherExists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A primary teacher is already assigned to this class and subject'
                    ], 422);
                }
            }

            $assignment->update($validated);

            return response()->json([
                'success' => true,
                'data' => $assignment->fresh()->load([
                    'teacher',
                    'classRoom.campus.school',
                    'classRoom.course.department.faculty',
                    'subject.course',
                    'academicYear'
                ]),
                'message' => 'Teacher assignment updated successfully'
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
                'message' => 'Failed to update teacher assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified teacher assignment.
     * @group Admin Academics
    */
    public function destroy(TeacherAssignment $assignment): JsonResponse
    {
        try {
            $assignment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Teacher assignment deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete teacher assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deactivate a teacher assignment.
     * @group Admin Academics
    */
    public function deactivate(TeacherAssignment $assignment): JsonResponse
    {
        try {
            $assignment->update([
                'is_active' => false,
                'end_date' => now()->toDateString()
            ]);

            return response()->json([
                'success' => true,
                'data' => $assignment->fresh()->load([
                    'teacher',
                    'classRoom.campus.school',
                    'classRoom.course.department.faculty',
                    'subject.course',
                    'academicYear'
                ]),
                'message' => 'Teacher assignment deactivated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate teacher assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get teacher assignments by teacher.
     * @group Admin Academics
    */
    public function byTeacher(User $teacher, Request $request): JsonResponse
    {
        $query = TeacherAssignment::where('teacher_id', $teacher->id)
                                ->with([
                                    'classRoom.campus.school',
                                    'classRoom.course.department.faculty',
                                    'subject.course',
                                    'academicYear'
                                ]);

        // Filter by academic year if provided
        if ($request->has('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        // Filter by role if provided
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Filter by active status if provided
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $assignments = $query->orderBy('assignment_date', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $assignments,
            'message' => 'Teacher assignments retrieved successfully'
        ]);
    }

    /**
     * Get teacher assignments by class.
     * @group Admin Academics
    */
    public function byClass(ClassRoom $class, Request $request): JsonResponse
    {
        $query = $class->teacherAssignments()
                      ->with([
                          'teacher',
                          'subject.course',
                          'academicYear'
                      ]);

        // Filter by academic year if provided
        if ($request->has('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        // Filter by role if provided
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Filter by active status if provided
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $assignments = $query->orderBy('assignment_date', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $assignments,
            'message' => 'Class teacher assignments retrieved successfully'
        ]);
    }

    /**
     * Get teacher workload summary.
     * @group Admin Academics
    */
    public function workloadSummary(User $teacher, Request $request): JsonResponse
    {
        $query = TeacherAssignment::where('teacher_id', $teacher->id)
                                ->where('is_active', true);

        // Filter by academic year if provided
        if ($request->has('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        $assignments = $query->with(['classRoom', 'subject', 'academicYear'])->get();

        $workload = [
            'total_classes' => $assignments->unique('class_id')->count(),
            'total_subjects' => $assignments->unique('subject_id')->count(),
            'total_weekly_hours' => $assignments->sum('weekly_hours'),
            'primary_teacher_assignments' => $assignments->where('role', 'primary_teacher')->count(),
            'assistant_teacher_assignments' => $assignments->where('role', 'assistant_teacher')->count(),
            'substitute_teacher_assignments' => $assignments->where('role', 'substitute_teacher')->count(),
            'assignments_by_academic_year' => $assignments->groupBy('academic_year_id')
                ->map(function($yearAssignments) {
                    return [
                        'total_classes' => $yearAssignments->unique('class_id')->count(),
                        'total_subjects' => $yearAssignments->unique('subject_id')->count(),
                        'total_weekly_hours' => $yearAssignments->sum('weekly_hours'),
                    ];
                }),
        ];

        return response()->json([
            'success' => true,
            'data' => $workload,
            'message' => 'Teacher workload summary retrieved successfully'
        ]);
    }
} 