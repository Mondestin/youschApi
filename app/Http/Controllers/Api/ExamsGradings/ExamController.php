<?php

namespace App\Http\Controllers\Api\ExamsGradings;

use App\Http\Controllers\Controller;
use App\Models\ExamsGradings\Exam;
use App\Models\AdminAcademics\{ClassRoom, Subject, Lab, Term, AcademicYear};
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
     * @group Exams & Gradings
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Exam::with(['classRoom', 'subject', 'lab', 'examType', 'examiner']);

            // Apply filters
            if ($request->filled('class_id')) {
                $query->where('class_id', $request->class_id);
            }

            if ($request->filled('subject_id')) {
                $query->where('subject_id', $request->subject_id);
            }

            if ($request->filled('exam_type_id')) {
                $query->where('exam_type_id', $request->exam_type_id);
            }

            if ($request->filled('examiner_id')) {
                $query->where('examiner_id', $request->examiner_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('date_from')) {
                $query->where('exam_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->where('exam_date', '<=', $request->date_to);
            }

            $exams = $query->orderBy('exam_date', 'desc')
                          ->orderBy('start_time')
                          ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $exams,
                'message' => 'Exams retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve exams',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created exam.
     * @group Exams & Gradings
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'class_id' => 'required|exists:class_rooms,id',
                'subject_id' => 'required|exists:subjects,id',
                'lab_id' => 'nullable|exists:labs,id',
                'exam_type_id' => 'required|exists:exam_types,id',
                'exam_date' => 'required|date|after_or_equal:today',
                'start_time' => 'required|date_format:H:i:s',
                'end_time' => 'required|date_format:H:i:s|after:start_time',
                'examiner_id' => 'required|exists:users,id',
                'instructions' => 'nullable|string',
            ]);

            // Check for time conflicts
            $conflicts = $this->checkTimeConflicts(
                $validated['class_id'],
                $validated['exam_date'],
                $validated['start_time'],
                $validated['end_time']
            );

            if (!empty($conflicts)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Time conflict detected',
                    'conflicts' => $conflicts
                ], 422);
            }

            $exam = Exam::create($validated);

            return response()->json([
                'success' => true,
                'data' => $exam->load(['classRoom', 'subject', 'lab', 'examType', 'examiner']),
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
     * @group Exams & Gradings
     */
    public function show(Exam $exam): JsonResponse
    {
        try {
            $exam->load(['classRoom', 'subject', 'lab', 'examType', 'examiner', 'examMarks.student']);

            return response()->json([
                'success' => true,
                'data' => $exam,
                'message' => 'Exam retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve exam',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified exam.
     * @group Exams & Gradings
     */
    public function update(Request $request, Exam $exam): JsonResponse
    {
        try {
            $validated = $request->validate([
                'class_id' => 'sometimes|required|exists:class_rooms,id',
                'subject_id' => 'sometimes|required|exists:subjects,id',
                'lab_id' => 'nullable|exists:labs,id',
                'exam_type_id' => 'sometimes|required|exists:exam_types,id',
                'exam_date' => 'sometimes|required|date',
                'start_time' => 'sometimes|required|date_format:H:i:s',
                'end_time' => 'sometimes|required|date_format:H:i:s|after:start_time',
                'examiner_id' => 'sometimes|required|exists:users,id',
                'instructions' => 'nullable|string',
                'status' => 'sometimes|required|in:scheduled,completed,cancelled',
            ]);

            // Check for time conflicts if date/time is being updated
            if (isset($validated['exam_date']) || isset($validated['start_time']) || isset($validated['end_time'])) {
                $examDate = $validated['exam_date'] ?? $exam->exam_date;
                $startTime = $validated['start_time'] ?? $exam->start_time;
                $endTime = $validated['end_time'] ?? $exam->end_time;

                $conflicts = $this->checkTimeConflicts(
                    $validated['class_id'] ?? $exam->class_id,
                    $examDate,
                    $startTime,
                    $endTime,
                    $exam->id // Exclude current exam from conflict check
                );

                if (!empty($conflicts)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Time conflict detected',
                        'conflicts' => $conflicts
                    ], 422);
                }
            }

            $updated = $exam->update($validated);

            if ($updated) {
                $exam->refresh()->load(['classRoom', 'subject', 'lab', 'examType', 'examiner']);
                return response()->json([
                    'success' => true,
                    'data' => $exam,
                    'message' => 'Exam updated successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update exam'
            ], 500);

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
     * @group Exams & Gradings      
     */
    public function destroy(Exam $exam): JsonResponse
    {
        try {
            // Check if exam has marks
            if ($exam->examMarks()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete exam as it has associated marks'
                ], 422);
            }

            $deleted = $exam->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Exam deleted successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete exam'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete exam',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get upcoming exams.
     * @group Exams & Gradings
     */
    public function upcoming(): JsonResponse
    {
        try {
            $exams = Exam::with(['classRoom', 'subject', 'examType', 'examiner'])
                        ->upcoming()
                        ->orderBy('exam_date')
                        ->orderBy('start_time')
                        ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $exams,
                'message' => 'Upcoming exams retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve upcoming exams',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get past exams.
     * @group Exams & Gradings
     */
    public function past(): JsonResponse
    {
        try {
            $exams = Exam::with(['classRoom', 'subject', 'examType', 'examiner'])
                        ->past()
                        ->orderBy('exam_date', 'desc')
                        ->orderBy('start_time')
                        ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $exams,
                'message' => 'Past exams retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve past exams',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get scheduled exams.
     * @group Exams & Gradings
     */
    public function scheduled(): JsonResponse
    {
        try {
            $exams = Exam::with(['classRoom', 'subject', 'examType', 'examiner'])
                        ->scheduled()
                        ->orderBy('exam_date')
                        ->orderBy('start_time')
                        ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $exams,
                'message' => 'Scheduled exams retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve scheduled exams',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get completed exams.
     * @group Exams & Gradings
     */
    public function completed(): JsonResponse
    {
        try {
            $exams = Exam::with(['classRoom', 'subject', 'examType', 'examiner'])
                        ->completed()
                        ->orderBy('exam_date', 'desc')
                        ->orderBy('start_time')
                        ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $exams,
                'message' => 'Completed exams retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve completed exams',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cancelled exams.
     * @group Exams & Gradings
     */
    public function cancelled(): JsonResponse
    {
        try {
            $exams = Exam::with(['classRoom', 'subject', 'examType', 'examiner'])
                        ->cancelled()
                        ->orderBy('exam_date', 'desc')
                        ->orderBy('start_time')
                        ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $exams,
                'message' => 'Cancelled exams retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cancelled exams',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get exams by class.
     * @group Exams & Gradings
     */
    public function getByClass(ClassRoom $class): JsonResponse
    {
        try {
            $exams = Exam::with(['subject', 'examType', 'examiner'])
                        ->byClass($class->id)
                        ->orderBy('exam_date', 'desc')
                        ->orderBy('start_time')
                        ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $exams,
                'message' => 'Exams for class retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve exams for class',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get exams by subject.
     * @group Exams & Gradings
     */
    public function getBySubject(Subject $subject): JsonResponse
    {
        try {
            $exams = Exam::with(['classRoom', 'examType', 'examiner'])
                        ->bySubject($subject->id)
                        ->orderBy('exam_date', 'desc')
                        ->orderBy('start_time')
                        ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $exams,
                'message' => 'Exams for subject retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve exams for subject',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get exams by examiner.
     * @group Exams & Gradings
     */
    public function getByExaminer(User $examiner): JsonResponse
    {
        try {
            $exams = Exam::with(['classRoom', 'subject', 'examType'])
                        ->byExaminer($examiner->id)
                        ->orderBy('exam_date', 'desc')
                        ->orderBy('start_time')
                        ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $exams,
                'message' => 'Exams for examiner retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve exams for examiner',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get exams by exam type.
     * @group Exams & Gradings
     */
    public function getByType(ExamType $examType): JsonResponse
    {
        try {
            $exams = Exam::with(['classRoom', 'subject', 'examiner'])
                        ->byExamType($examType->id)
                        ->orderBy('exam_date', 'desc')
                        ->orderBy('start_time')
                        ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $exams,
                'message' => 'Exams for type retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve exams for type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get exams by date range.
     * @group Exams & Gradings
     */
    public function getByDateRange(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $exams = Exam::with(['classRoom', 'subject', 'examType', 'examiner'])
                        ->byDateRange($validated['start_date'], $validated['end_date'])
                        ->orderBy('exam_date')
                        ->orderBy('start_time')
                        ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $exams,
                'message' => 'Exams for date range retrieved successfully'
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
                'message' => 'Failed to retrieve exams for date range',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark exam as completed.
     * @group Exams & Gradings
     */
    public function markAsCompleted(Exam $exam): JsonResponse
    {
        try {
            if ($exam->isCompleted()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Exam is already completed'
                ], 422);
            }

            $updated = $exam->markAsCompleted();

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'Exam marked as completed successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark exam as completed'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark exam as completed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark exam as cancelled.
     * @group Exams & Gradings
    */
    public function markAsCancelled(Exam $exam): JsonResponse
    {
        try {
            if ($exam->isCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Exam is already cancelled'
                ], 422);
            }

            $updated = $exam->markAsCancelled();

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'Exam marked as cancelled successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark exam as cancelled'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark exam as cancelled',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get exam statistics.
     * @group Exams & Gradings
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_exams' => Exam::count(),
                'scheduled_exams' => Exam::scheduled()->count(),
                'completed_exams' => Exam::completed()->count(),
                'cancelled_exams' => Exam::cancelled()->count(),
                'upcoming_exams' => Exam::upcoming()->count(),
                'past_exams' => Exam::past()->count(),
                'today_exams' => Exam::where('exam_date', today())->count(),
                'this_week_exams' => Exam::whereBetween('exam_date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'this_month_exams' => Exam::whereMonth('exam_date', now()->month)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Exam statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve exam statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check for time conflicts when scheduling exams.
     * @group Exams & Gradings
    */
    private function checkTimeConflicts(int $classId, string $examDate, string $startTime, string $endTime, ?int $excludeExamId = null): array
    {
        $query = Exam::where('class_id', $classId)
                    ->where('exam_date', $examDate)
                    ->where('status', '!=', 'cancelled');

        if ($excludeExamId) {
            $query->where('id', '!=', $excludeExamId);
        }

        $conflicts = $query->where(function ($q) use ($startTime, $endTime) {
            $q->where(function ($subQ) use ($startTime, $endTime) {
                // Check if new exam overlaps with existing exams
                $subQ->where('start_time', '<', $endTime)
                     ->where('end_time', '>', $startTime);
            });
        })->get();

        return $conflicts->map(function ($exam) {
            return [
                'id' => $exam->id,
                'subject' => $exam->subject->name ?? 'Unknown',
                'start_time' => $exam->start_time,
                'end_time' => $exam->end_time,
                'status' => $exam->status,
            ];
        })->toArray();
    }
} 