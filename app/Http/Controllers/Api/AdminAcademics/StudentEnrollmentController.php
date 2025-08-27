<?php

namespace App\Http\Controllers\Api\AdminAcademics;

use App\Http\Controllers\Controller;
use App\Models\AdminAcademics\StudentEnrollment;
use App\Models\AdminAcademics\ClassRoom;
use App\Models\AdminAcademics\AcademicYear;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class StudentEnrollmentController extends Controller
{
    /**
     * Display a listing of student enrollments.
     * @group Admin Academics
    */
    public function index(Request $request): JsonResponse
    {
        $query = StudentEnrollment::with([
            'student',
            'classRoom.campus.school',
            'classRoom.course.department.faculty',
            'academicYear'
        ]);

        // Filter by student if provided
        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Filter by class if provided
        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by academic year if provided
        if ($request->has('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by school if provided
        if ($request->has('school_id')) {
            $query->whereHas('classRoom.campus', function($q) use ($request) {
                $q->where('school_id', $request->school_id);
            });
        }

        $enrollments = $query->orderBy('enrollment_date', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $enrollments,
            'message' => 'Student enrollments retrieved successfully'
        ]);
    }

    /**
     * Store a newly created student enrollment.
     * @group Admin Academics
    */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'student_id' => 'required|exists:users,id',
                'class_id' => 'required|exists:classes,id',
                'academic_year_id' => 'required|exists:academic_years,id',
                'status' => 'required|in:enrolled,completed,dropped,transferred',
                'enrollment_date' => 'required|date',
                'completion_date' => 'nullable|date|after:enrollment_date',
                'notes' => 'nullable|string',
            ]);

            // Check if student is already enrolled in this class for this academic year
            $existingEnrollment = StudentEnrollment::where('student_id', $validated['student_id'])
                ->where('class_id', $validated['class_id'])
                ->where('academic_year_id', $validated['academic_year_id'])
                ->where('status', '!=', 'dropped')
                ->exists();

            if ($existingEnrollment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student is already enrolled in this class for this academic year'
                ], 422);
            }

            // Check if class has capacity
            $class = ClassRoom::findOrFail($validated['class_id']);
            $currentEnrollments = StudentEnrollment::where('class_id', $validated['class_id'])
                ->where('academic_year_id', $validated['academic_year_id'])
                ->where('status', 'enrolled')
                ->count();

            if ($currentEnrollments >= $class->capacity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Class has reached maximum capacity'
                ], 422);
            }

            $enrollment = StudentEnrollment::create($validated);

            return response()->json([
                'success' => true,
                'data' => $enrollment->load([
                    'student',
                    'classRoom.campus.school',
                    'classRoom.course.department.faculty',
                    'academicYear'
                ]),
                'message' => 'Student enrolled successfully'
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
                'message' => 'Failed to enroll student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified student enrollment.
     * @group Admin Academics
    */
    public function show(StudentEnrollment $enrollment): JsonResponse
    {
        $enrollment->load([
            'student',
            'classRoom.campus.school',
            'classRoom.course.department.faculty',
            'classRoom.subjects',
            'academicYear'
        ]);

        return response()->json([
            'success' => true,
            'data' => $enrollment,
            'message' => 'Student enrollment retrieved successfully'
        ]);
    }

    /**
     * Update the specified student enrollment.
     * @group Admin Academics
    */
    public function update(Request $request, StudentEnrollment $enrollment): JsonResponse
    {
        try {
            $validated = $request->validate([
                'student_id' => 'sometimes|required|exists:users,id',
                'class_id' => 'sometimes|required|exists:classes,id',
                'academic_year_id' => 'sometimes|required|exists:academic_years,id',
                'status' => 'sometimes|required|in:enrolled,completed,dropped,transferred',
                'enrollment_date' => 'sometimes|required|date',
                'completion_date' => 'nullable|date|after:enrollment_date',
                'notes' => 'nullable|string',
            ]);

            // Check for conflicts if class or academic year is being changed
            if (isset($validated['class_id']) || isset($validated['academic_year_id'])) {
                $classId = $validated['class_id'] ?? $enrollment->class_id;
                $academicYearId = $validated['academic_year_id'] ?? $enrollment->academic_year_id;

                $existingEnrollment = StudentEnrollment::where('student_id', $enrollment->student_id)
                    ->where('class_id', $classId)
                    ->where('academic_year_id', $academicYearId)
                    ->where('id', '!=', $enrollment->id)
                    ->where('status', '!=', 'dropped')
                    ->exists();

                if ($existingEnrollment) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Student is already enrolled in this class for this academic year'
                    ], 422);
                }
            }

            $enrollment->update($validated);

            return response()->json([
                'success' => true,
                'data' => $enrollment->fresh()->load([
                    'student',
                    'classRoom.campus.school',
                    'classRoom.course.department.faculty',
                    'academicYear'
                ]),
                'message' => 'Student enrollment updated successfully'
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
                'message' => 'Failed to update student enrollment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified student enrollment.
     * @group Admin Academics
    */
    public function destroy(StudentEnrollment $enrollment): JsonResponse
    {
        try {
            $enrollment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Student enrollment deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete student enrollment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change enrollment status.
     * @group Admin Academics
    */
    public function changeStatus(Request $request, StudentEnrollment $enrollment): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:enrolled,completed,dropped,transferred',
                'completion_date' => 'nullable|date|after:enrollment_date',
                'notes' => 'nullable|string',
            ]);

            $enrollment->update($validated);

            return response()->json([
                'success' => true,
                'data' => $enrollment->fresh()->load([
                    'student',
                    'classRoom.campus.school',
                    'classRoom.course.department.faculty',
                    'academicYear'
                ]),
                'message' => 'Enrollment status changed successfully'
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
                'message' => 'Failed to change enrollment status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk enroll students.
     * @group Admin Academics
    */
    public function bulkEnroll(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'class_id' => 'required|exists:classes,id',
                'academic_year_id' => 'required|exists:academic_years,id',
                'student_ids' => 'required|array|min:1',
                'student_ids.*' => 'exists:users,id',
                'enrollment_date' => 'required|date',
                'notes' => 'nullable|string',
            ]);

            $class = ClassRoom::findOrFail($validated['class_id']);
            $currentEnrollments = StudentEnrollment::where('class_id', $validated['class_id'])
                ->where('academic_year_id', $validated['academic_year_id'])
                ->where('status', 'enrolled')
                ->count();

            if (($currentEnrollments + count($validated['student_ids'])) > $class->capacity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bulk enrollment would exceed class capacity'
                ], 422);
            }

            $enrollments = [];
            $errors = [];

            DB::transaction(function() use ($validated, &$enrollments, &$errors) {
                foreach ($validated['student_ids'] as $studentId) {
                    // Check if student is already enrolled
                    $existingEnrollment = StudentEnrollment::where('student_id', $studentId)
                        ->where('class_id', $validated['class_id'])
                        ->where('academic_year_id', $validated['academic_year_id'])
                        ->where('status', '!=', 'dropped')
                        ->exists();

                    if ($existingEnrollment) {
                        $errors[] = "Student ID {$studentId} is already enrolled";
                        continue;
                    }

                    $enrollment = StudentEnrollment::create([
                        'student_id' => $studentId,
                        'class_id' => $validated['class_id'],
                        'academic_year_id' => $validated['academic_year_id'],
                        'status' => 'enrolled',
                        'enrollment_date' => $validated['enrollment_date'],
                        'notes' => $validated['notes'],
                    ]);

                    $enrollments[] = $enrollment;
                }
            });

            $response = [
                'success' => true,
                'data' => [
                    'enrollments' => $enrollments,
                    'errors' => $errors
                ],
                'message' => 'Bulk enrollment completed'
            ];

            if (!empty($errors)) {
                $response['message'] .= ' with some errors';
            }

            return response()->json($response, 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to perform bulk enrollment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get enrollments by class.
     * @group Admin Academics
    */
    public function byClass(ClassRoom $class, Request $request): JsonResponse
    {
        $query = $class->studentEnrollments()
                      ->with(['student', 'academicYear']);

        // Filter by academic year if provided
        if ($request->has('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $enrollments = $query->orderBy('enrollment_date', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $enrollments,
            'message' => 'Class enrollments retrieved successfully'
        ]);
    }

    /**
     * Get enrollments by student.
     * @group Admin Academics
    */
    public function byStudent(User $student, Request $request): JsonResponse
    {
        $query = StudentEnrollment::where('student_id', $student->id)
                                ->with([
                                    'classRoom.campus.school',
                                    'classRoom.course.department.faculty',
                                    'academicYear'
                                ]);

        // Filter by academic year if provided
        if ($request->has('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $enrollments = $query->orderBy('enrollment_date', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $enrollments,
            'message' => 'Student enrollments retrieved successfully'
        ]);
    }
} 