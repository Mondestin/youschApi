<?php

namespace App\Http\Controllers\Api\AdminAcademics;

use App\Http\Controllers\Controller;
use App\Models\AdminAcademics\Exam;
use App\Models\AdminAcademics\Subject;
use App\Models\AdminAcademics\ClassRoom;
use App\Models\AdminAcademics\Lab;
use App\Models\ExamsGradings\ExamType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    /**
     * Display a listing of exams.
     * @group Admin Academics
     */
    public function index(Request $request): JsonResponse
    {
        $query = Exam::with(['subject.course.department.faculty.school', 'classRoom.campus', 'examiner', 'examType', 'lab']);

        // Filter by subject if provided
        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Filter by class if provided
        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by examiner if provided
        if ($request->has('examiner_id')) {
            $query->where('examiner_id', $request->examiner_id);
        }

        // Filter by exam type if provided
        if ($request->has('exam_type_id')) {
            $query->where('exam_type_id', $request->exam_type_id);
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range if provided
        if ($request->has('start_date')) {
            $query->where('exam_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('exam_date', '<=', $request->end_date);
        }

        // Filter by school if provided
        if ($request->has('school_id')) {
            $query->whereHas('subject.course.department.faculty', function($q) use ($request) {
                $q->where('school_id', $request->school_id);
            });
        }

        $exams = $query->orderBy('exam_date', 'asc')
                      ->orderBy('start_time', 'asc')
                      ->get();

        return response()->json([
            'success' => true,
            'data' => $exams,
            'message' => 'Exams retrieved successfully'
        ]);
    }

    /**
     * Store a newly created exam.
     * @group Admin Academics
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'subject_id' => 'required|exists:subjects,id',
                'class_id' => 'required|exists:classes,id',
                'examiner_id' => 'required|exists:users,id',
                'exam_type_id' => 'required|exists:exam_types,id',
                'exam_date' => 'required|date',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'lab_id' => 'nullable|exists:labs,id',
                'instructions' => 'nullable|string',
                'status' => 'nullable|in:scheduled,ongoing,completed,cancelled',
            ]);

            // Check for time conflicts in the same class on the same date
            $timeConflict = Exam::where('class_id', $validated['class_id'])
                ->where('exam_date', $validated['exam_date'])
                ->where(function($query) use ($validated) {
                    $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                          ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                          ->orWhere(function($q) use ($validated) {
                              $q->where('start_time', '<=', $validated['start_time'])
                                ->where('end_time', '>=', $validated['end_time']);
                          });
                })
                ->exists();

            if ($timeConflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'Exam time conflicts with existing exam for this class'
                ], 422);
            }

            // Check for examiner conflicts on the same date and time
            $examinerConflict = Exam::where('examiner_id', $validated['examiner_id'])
                ->where('exam_date', $validated['exam_date'])
                ->where(function($query) use ($validated) {
                    $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                          ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                          ->orWhere(function($q) use ($validated) {
                              $q->where('start_time', '<=', $validated['start_time'])
                                ->where('end_time', '>=', $validated['end_time']);
                          });
                })
                ->exists();

            if ($examinerConflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'Examiner has a conflicting exam at this time'
                ], 422);
            }

            $exam = Exam::create($validated);

            return response()->json([
                'success' => true,
                'data' => $exam->load(['subject.course.department.faculty.school', 'classRoom.campus', 'examiner', 'examType', 'lab']),
                'message' => 'Exam created successfully'
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
                'message' => 'Failed to create exam',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified exam.
     * @group Admin Academics
     */
    public function show(Exam $exam): JsonResponse
    {
        $exam->load([
            'subject.course.department.faculty.school',
            'classRoom.campus',
            'examiner',
            'examType',
            'lab'
        ]);

        return response()->json([
            'success' => true,
            'data' => $exam,
            'message' => 'Exam retrieved successfully'
        ]);
    }

    /**
     * Update the specified exam.
     * @group Admin Academics
     */
    public function update(Request $request, Exam $exam): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'subject_id' => 'sometimes|required|exists:subjects,id',
                'class_id' => 'sometimes|required|exists:classes,id',
                'examiner_id' => 'sometimes|required|exists:users,id',
                'exam_type_id' => 'sometimes|required|exists:exam_types,id',
                'exam_date' => 'sometimes|required|date',
                'start_time' => 'sometimes|required|date_format:H:i',
                'end_time' => 'sometimes|required|date_format:H:i|after:start_time',
                'lab_id' => 'nullable|exists:labs,id',
                'instructions' => 'nullable|string',
                'status' => 'sometimes|required|in:scheduled,completed,cancelled',
            ]);

            // Check for conflicts if time/date/class/examiner is being changed
            if (isset($validated['exam_date']) || isset($validated['start_time']) || isset($validated['end_time']) || 
                isset($validated['class_id']) || isset($validated['examiner_id'])) {
                
                $examDate = $validated['exam_date'] ?? $exam->exam_date;
                $startTime = $validated['start_time'] ?? $exam->start_time;
                $endTime = $validated['end_time'] ?? $exam->end_time;
                $classId = $validated['class_id'] ?? $exam->class_id;
                $examinerId = $validated['examiner_id'] ?? $exam->examiner_id;

                // Check for time conflicts in the same class on the same date
                $timeConflict = Exam::where('class_id', $classId)
                    ->where('exam_date', $examDate)
                    ->where('id', '!=', $exam->id)
                    ->where(function($query) use ($startTime, $endTime) {
                        $query->whereBetween('start_time', [$startTime, $endTime])
                              ->orWhereBetween('end_time', [$startTime, $endTime])
                              ->orWhere(function($q) use ($startTime, $endTime) {
                                  $q->where('start_time', '<=', $startTime)
                                    ->where('end_time', '>=', $endTime);
                              });
                    })
                    ->exists();

                if ($timeConflict) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Exam time conflicts with existing exam for this class'
                    ], 422);
                }

                // Check for examiner conflicts on the same date and time
                $examinerConflict = Exam::where('examiner_id', $examinerId)
                    ->where('exam_date', $examDate)
                    ->where('id', '!=', $exam->id)
                    ->where(function($query) use ($startTime, $endTime) {
                        $query->whereBetween('start_time', [$startTime, $endTime])
                              ->orWhereBetween('end_time', [$startTime, $endTime])
                              ->orWhere(function($q) use ($startTime, $endTime) {
                                  $q->where('start_time', '<=', $startTime)
                                    ->where('end_time', '>=', $endTime);
                              });
                    })
                    ->exists();

                if ($examinerConflict) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Examiner has a conflicting exam at this time'
                    ], 422);
                }
            }

            $exam->update($validated);

            return response()->json([
                'success' => true,
                'data' => $exam->fresh()->load(['subject.course.department.faculty.school', 'classRoom.campus', 'examiner', 'examType', 'lab']),
                'message' => 'Exam updated successfully'
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
                'message' => 'Failed to update exam',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified exam.
     * @group Admin Academics
     */
    public function destroy(Exam $exam): JsonResponse
    {
        try {
            // Check if exam has any related data
            if ($exam->studentGrades()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete exam with related student grades. Please remove related records first.'
                ], 422);
            }

            $exam->delete();

            return response()->json([
                'success' => true,
                'message' => 'Exam deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete exam',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get exams by subject.
     * @group Admin Academics
     */
    public function bySubject(Subject $subject, Request $request): JsonResponse
    {
        $query = $subject->exams()
                        ->with(['classRoom.campus', 'examiner', 'examType', 'lab']);

        // Filter by date range if provided
        if ($request->has('start_date')) {
            $query->where('exam_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('exam_date', '<=', $request->end_date);
        }

        $exams = $query->orderBy('exam_date', 'asc')
                      ->orderBy('start_time', 'asc')
                      ->get();

        return response()->json([
            'success' => true,
            'data' => $exams,
            'message' => 'Subject exams retrieved successfully'
        ]);
    }

    /**
     * Get exams by class.
     * @group Admin Academics
     */
    public function byClass(ClassRoom $class, Request $request): JsonResponse
    {
        $query = $class->exams()
                      ->with(['subject.course', 'examiner', 'examType', 'lab']);

        // Filter by date range if provided
        if ($request->has('start_date')) {
            $query->where('exam_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('exam_date', '<=', $request->end_date);
        }

        $exams = $query->orderBy('exam_date', 'asc')
                      ->orderBy('start_time', 'asc')
                      ->get();

        return response()->json([
            'success' => true,
            'data' => $exams,
            'message' => 'Class exams retrieved successfully'
        ]);
    }

    /**
     * Get upcoming exams.
     * @group Admin Academics
     */
    public function upcoming(Request $request): JsonResponse
    {
        $query = Exam::with(['subject.course.department.faculty.school', 'classRoom.campus', 'examiner', 'examType', 'lab'])
                    ->where('exam_date', '>=', now()->toDateString());

        // Filter by school if provided
        if ($request->has('school_id')) {
            $query->whereHas('subject.course.department.faculty', function($q) use ($request) {
                $q->where('school_id', $request->school_id);
            });
        }

        // Filter by class if provided
        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $exams = $query->orderBy('exam_date', 'asc')
                      ->orderBy('start_time', 'asc')
                      ->limit(20)
                      ->get();

        return response()->json([
            'success' => true,
            'data' => $exams,
            'message' => 'Upcoming exams retrieved successfully'
        ]);
    }

    /**
     * Get exam statistics.
     * @group Admin Academics
    */
    public function statistics(Exam $exam): JsonResponse
    {
        $stats = [
            'total_students' => $exam->classRoom->studentEnrollments()->count(),
            'total_grades_recorded' => $exam->studentGrades()->count(),
            'average_score' => $exam->studentGrades()->avg('score'),
            'highest_score' => $exam->studentGrades()->max('score'),
            'lowest_score' => $exam->studentGrades()->min('score'),
            'days_until_exam' => now()->diffInDays($exam->exam_date, false),
            'is_past' => $exam->exam_date < now()->toDateString(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Exam statistics retrieved successfully'
        ]);
    }
} 