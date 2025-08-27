<?php

namespace App\Http\Controllers\Api\ExamsGradings;

use App\Http\Controllers\Controller;
use App\Models\ExamsGradings\{ExamMark, Exam};
use App\Models\User;
use App\Repositories\ExamsGradings\ExamMarkRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class ExamMarkController extends Controller
{
    public function __construct(
        private ExamMarkRepositoryInterface $examMarkRepository
    ) {}

    /**
     * Display a listing of exam marks.
     * @group Exams & Gradings
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'exam_id', 'student_id', 'grade', 'min_marks', 'max_marks',
                'has_grade', 'is_passing'
            ]);

            $examMarks = $this->examMarkRepository->getPaginatedExamMarks($filters);

            return response()->json([
                'success' => true,
                'data' => $examMarks,
                'message' => 'Exam marks retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve exam marks',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created exam mark.
     * @group Exams & Gradings
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'exam_id' => 'required|exists:exams,id',
                'student_id' => 'required|exists:users,id',
                'marks_obtained' => 'required|numeric|min:0|max:999.99',
                'grade' => 'nullable|string|max:5',
                'remarks' => 'nullable|string',
            ]);

            // Check if mark already exists for this student and exam
            $existingMark = ExamMark::where('exam_id', $validated['exam_id'])
                                   ->where('student_id', $validated['student_id'])
                                   ->first();

            if ($existingMark) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mark already exists for this student and exam'
                ], 422);
            }

            // Auto-calculate grade if not provided
            if (empty($validated['grade'])) {
                $validated['grade'] = $this->calculateGrade($validated['marks_obtained']);
            }

            $examMark = $this->examMarkRepository->createExamMark($validated);

            return response()->json([
                'success' => true,
                'data' => $examMark->load(['exam', 'student']),
                'message' => 'Exam mark created successfully'
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
                'message' => 'Failed to create exam mark',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified exam mark.
     * @group Exams & Gradings
     */
    public function show(ExamMark $examMark): JsonResponse
    {
        try {
            $examMark->load(['exam', 'student']);

            return response()->json([
                'success' => true,
                'data' => $examMark,
                'message' => 'Exam mark retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve exam mark',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified exam mark.
     * @group Exams & Gradings
     */
    public function update(Request $request, ExamMark $examMark): JsonResponse
    {
        try {
            $validated = $request->validate([
                'marks_obtained' => 'sometimes|required|numeric|min:0|max:999.99',
                'grade' => 'nullable|string|max:5',
                'remarks' => 'nullable|string',
            ]);

            // Auto-calculate grade if marks are updated and grade is not provided
            if (isset($validated['marks_obtained']) && empty($validated['grade'])) {
                $validated['grade'] = $this->calculateGrade($validated['marks_obtained']);
            }

            $updated = $this->examMarkRepository->updateExamMark($examMark, $validated);

            if ($updated) {
                $examMark->refresh()->load(['exam', 'student']);
                return response()->json([
                    'success' => true,
                    'data' => $examMark,
                    'message' => 'Exam mark updated successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update exam mark'
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
                'message' => 'Failed to update exam mark',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified exam mark.
     * @group Exams & Gradings
     */
    public function destroy(ExamMark $examMark): JsonResponse
    {
        try {
            $deleted = $this->examMarkRepository->deleteExamMark($examMark);

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Exam mark deleted successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete exam mark'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete exam mark',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get exam marks by exam.
     * @group Exams & Gradings
     */
    public function getByExam(Exam $exam): JsonResponse
    {
        try {
            $examMarks = $this->examMarkRepository->getExamMarksByExam($exam->id);

            return response()->json([
                'success' => true,
                'data' => $examMarks->load(['student']),
                'message' => 'Exam marks for exam retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve exam marks for exam',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get exam marks by student.
     * @group Exams & Gradings
     */
    public function getByStudent(User $student): JsonResponse
    {
        try {
            $examMarks = $this->examMarkRepository->getExamMarksByStudent($student->id);

            return response()->json([
                'success' => true,
                'data' => $examMarks->load(['exam.subject', 'exam.examType']),
                'message' => 'Exam marks for student retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve exam marks for student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get exam mark by exam and student.
     * @group Exams & Gradings
     */
    public function getByExamAndStudent(Exam $exam, User $student): JsonResponse
    {
        try {
            $examMark = $this->examMarkRepository->getExamMarksByExamAndStudent($exam->id, $student->id);

            if (!$examMark) {
                return response()->json([
                    'success' => false,
                    'message' => 'Exam mark not found for this student and exam'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $examMark->load(['exam', 'student']),
                'message' => 'Exam mark retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve exam mark',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk create exam marks.
     * @group Exams & Gradings
     */
    public function bulkCreate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'exam_id' => 'required|exists:exams,id',
                'marks' => 'required|array|min:1',
                'marks.*.student_id' => 'required|exists:users,id',
                'marks.*.marks_obtained' => 'required|numeric|min:0|max:999.99',
                'marks.*.grade' => 'nullable|string|max:5',
                'marks.*.remarks' => 'nullable|string',
            ]);

            $examMarksData = [];
            foreach ($validated['marks'] as $mark) {
                $markData = [
                    'exam_id' => $validated['exam_id'],
                    'student_id' => $mark['student_id'],
                    'marks_obtained' => $mark['marks_obtained'],
                    'grade' => $mark['grade'] ?? $this->calculateGrade($mark['marks_obtained']),
                    'remarks' => $mark['remarks'] ?? null,
                ];
                $examMarksData[] = $markData;
            }

            $createdMarks = $this->examMarkRepository->bulkCreateExamMarks($examMarksData);

            return response()->json([
                'success' => true,
                'data' => $createdMarks,
                'message' => 'Exam marks created successfully'
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
                'message' => 'Failed to create exam marks',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update exam marks.
     * @group Exams & Gradings
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'marks' => 'required|array|min:1',
                'marks.*.id' => 'required|exists:exam_marks,id',
                'marks.*.marks_obtained' => 'sometimes|required|numeric|min:0|max:999.99',
                'marks.*.grade' => 'nullable|string|max:5',
                'marks.*.remarks' => 'nullable|string',
            ]);

            $examMarksData = [];
            foreach ($validated['marks'] as $mark) {
                $markData = ['id' => $mark['id']];
                
                if (isset($mark['marks_obtained'])) {
                    $markData['marks_obtained'] = $mark['marks_obtained'];
                    // Auto-calculate grade if marks are updated and grade is not provided
                    if (!isset($mark['grade'])) {
                        $markData['grade'] = $this->calculateGrade($mark['marks_obtained']);
                    } else {
                        $markData['grade'] = $mark['grade'];
                    }
                } else {
                    $markData['grade'] = $mark['grade'] ?? null;
                }
                
                $markData['remarks'] = $mark['remarks'] ?? null;
                $examMarksData[] = $markData;
            }

            $updatedMarks = $this->examMarkRepository->bulkUpdateExamMarks($examMarksData);

            return response()->json([
                'success' => true,
                'data' => $updatedMarks,
                'message' => 'Exam marks updated successfully'
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
                'message' => 'Failed to update exam marks',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get exam mark statistics.
     * @group Exams & Gradings
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['exam_id', 'class_id', 'subject_id', 'date_from', 'date_to']);
            $statistics = $this->examMarkRepository->getExamMarkStatistics($filters);

            return response()->json([
                'success' => true,
                'data' => $statistics,
                'message' => 'Exam mark statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve exam mark statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student performance in a specific exam.
     * @group Exams & Gradings
    */
    public function getStudentPerformanceInExam(int $examId, int $studentId): JsonResponse
    {
        try {
            $performance = $this->examMarkRepository->getStudentPerformanceInExam($examId, $studentId);

            return response()->json([
                'success' => true,
                'data' => $performance,
                'message' => 'Student performance retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve student performance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get exam results for a specific exam.
     * @group Exams & Gradings
     */
    public function getExamResults(Exam $exam): JsonResponse
    {
        try {
            $results = $this->examMarkRepository->getExamResults($exam->id);

            return response()->json([
                'success' => true,
                'data' => $results,
                'message' => 'Exam results retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve exam results',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get performance report for exams.
     * @group Exams & Gradings
     */
    public function performanceReport(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'class_id' => 'nullable|exists:class_rooms,id',
                'subject_id' => 'nullable|exists:subjects,id',
                'exam_type_id' => 'nullable|exists:exam_types,id',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
            ]);

            $query = ExamMark::with(['exam.subject', 'exam.classRoom', 'exam.examType', 'student']);

            // Apply filters
            if (isset($validated['class_id'])) {
                $query->whereHas('exam', function ($q) use ($validated) {
                    $q->where('class_id', $validated['class_id']);
                });
            }

            if (isset($validated['subject_id'])) {
                $query->whereHas('exam', function ($q) use ($validated) {
                    $q->where('subject_id', $validated['subject_id']);
                });
            }

            if (isset($validated['exam_type_id'])) {
                $query->whereHas('exam', function ($q) use ($validated) {
                    $q->where('exam_type_id', $validated['exam_type_id']);
                });
            }

            if (isset($validated['date_from'])) {
                $query->whereHas('exam', function ($q) use ($validated) {
                    $q->where('exam_date', '>=', $validated['date_from']);
                });
            }

            if (isset($validated['date_to'])) {
                $query->whereHas('exam', function ($q) use ($validated) {
                    $q->where('exam_date', '<=', $validated['date_to']);
                });
            }

            $examMarks = $query->get();

            // Calculate performance metrics
            $totalMarks = $examMarks->count();
            $passingMarks = $examMarks->filter(fn($mark) => $mark->isPassing())->count();
            $averageMarks = $examMarks->avg('marks_obtained');
            $highestMarks = $examMarks->max('marks_obtained');
            $lowestMarks = $examMarks->min('marks_obtained');

            $gradeDistribution = $examMarks->groupBy('grade')
                                         ->map(fn($group) => $group->count())
                                         ->toArray();

            $performanceReport = [
                'total_marks' => $totalMarks,
                'passing_marks' => $passingMarks,
                'failing_marks' => $totalMarks - $passingMarks,
                'pass_rate' => $totalMarks > 0 ? round(($passingMarks / $totalMarks) * 100, 2) : 0,
                'average_marks' => round($averageMarks, 2),
                'highest_marks' => $highestMarks,
                'lowest_marks' => $lowestMarks,
                'grade_distribution' => $gradeDistribution,
                'exam_marks' => $examMarks->take(100), // Limit to first 100 for performance
            ];

            return response()->json([
                'success' => true,
                'data' => $performanceReport,
                'message' => 'Performance report generated successfully'
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
                'message' => 'Failed to generate performance report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get class performance report.
     * @group Exams & Gradings
     */
    public function classPerformanceReport(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'class_id' => 'required|exists:class_rooms,id',
                'exam_type_id' => 'nullable|exists:exam_types,id',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
            ]);

            $query = ExamMark::with(['exam.subject', 'student'])
                            ->whereHas('exam', function ($q) use ($validated) {
                                $q->where('class_id', $validated['class_id']);
                            });

            if (isset($validated['exam_type_id'])) {
                $query->whereHas('exam', function ($q) use ($validated) {
                    $q->where('exam_type_id', $validated['exam_type_id']);
                });
            }

            if (isset($validated['date_from'])) {
                $query->whereHas('exam', function ($q) use ($validated) {
                    $q->where('exam_date', '>=', $validated['date_from']);
                });
            }

            if (isset($validated['date_to'])) {
                $query->whereHas('exam', function ($q) use ($validated) {
                    $q->where('exam_date', '<=', $validated['date_to']);
                });
            }

            $examMarks = $query->get();

            // Group by subject and calculate performance
            $subjectPerformance = $examMarks->groupBy('exam.subject.name')
                                          ->map(function ($marks, $subjectName) {
                                              $totalMarks = $marks->count();
                                              $passingMarks = $marks->filter(fn($mark) => $mark->isPassing())->count();
                                              
                                              return [
                                                  'subject' => $subjectName,
                                                  'total_marks' => $totalMarks,
                                                  'passing_marks' => $passingMarks,
                                                  'failing_marks' => $totalMarks - $passingMarks,
                                                  'pass_rate' => $totalMarks > 0 ? round(($passingMarks / $totalMarks) * 100, 2) : 0,
                                                  'average_marks' => round($marks->avg('marks_obtained'), 2),
                                                  'highest_marks' => $marks->max('marks_obtained'),
                                                  'lowest_marks' => $marks->min('marks_obtained'),
                                              ];
                                          })
                                          ->values();

            $classReport = [
                'class_id' => $validated['class_id'],
                'total_exam_marks' => $examMarks->count(),
                'subjects_performance' => $subjectPerformance,
                'overall_pass_rate' => $examMarks->count() > 0 ? 
                    round(($examMarks->filter(fn($mark) => $mark->isPassing())->count() / $examMarks->count()) * 100, 2) : 0,
            ];

            return response()->json([
                'success' => true,
                'data' => $classReport,
                'message' => 'Class performance report generated successfully'
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
                'message' => 'Failed to generate class performance report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subject performance report.
     * @group Exams & Gradings
     */
    public function subjectPerformanceReport(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'subject_id' => 'required|exists:subjects,id',
                'class_id' => 'nullable|exists:class_rooms,id',
                'exam_type_id' => 'nullable|exists:exam_types,id',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
            ]);

            $query = ExamMark::with(['exam.classRoom', 'exam.examType', 'student'])
                            ->whereHas('exam', function ($q) use ($validated) {
                                $q->where('subject_id', $validated['subject_id']);
                            });

            if (isset($validated['class_id'])) {
                $query->whereHas('exam', function ($q) use ($validated) {
                    $q->where('class_id', $validated['class_id']);
                });
            }

            if (isset($validated['exam_type_id'])) {
                $query->whereHas('exam', function ($q) use ($validated) {
                    $q->where('exam_type_id', $validated['exam_type_id']);
                });
            }

            if (isset($validated['date_from'])) {
                $query->whereHas('exam', function ($q) use ($validated) {
                    $q->where('exam_date', '>=', $validated['date_from']);
                });
            }

            if (isset($validated['date_to'])) {
                $query->whereHas('exam', function ($q) use ($validated) {
                    $q->where('exam_date', '<=', $validated['date_to']);
                });
            }

            $examMarks = $query->get();

            // Group by class and calculate performance
            $classPerformance = $examMarks->groupBy('exam.classRoom.name')
                                        ->map(function ($marks, $className) {
                                            $totalMarks = $marks->count();
                                            $passingMarks = $marks->filter(fn($mark) => $mark->isPassing())->count();
                                            
                                            return [
                                                'class' => $className,
                                                'total_marks' => $totalMarks,
                                                'passing_marks' => $passingMarks,
                                                'failing_marks' => $totalMarks - $passingMarks,
                                                'pass_rate' => $totalMarks > 0 ? round(($passingMarks / $totalMarks) * 100, 2) : 0,
                                                'average_marks' => round($marks->avg('marks_obtained'), 2),
                                                'highest_marks' => $marks->max('marks_obtained'),
                                                'lowest_marks' => $marks->min('marks_obtained'),
                                            ];
                                        })
                                        ->values();

            $subjectReport = [
                'subject_id' => $validated['subject_id'],
                'total_exam_marks' => $examMarks->count(),
                'classes_performance' => $classPerformance,
                'overall_pass_rate' => $examMarks->count() > 0 ? 
                    round(($examMarks->filter(fn($mark) => $mark->isPassing())->count() / $examMarks->count()) * 100, 2) : 0,
            ];

            return response()->json([
                'success' => true,
                'data' => $subjectReport,
                'message' => 'Subject performance report generated successfully'
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
                'message' => 'Failed to generate subject performance report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate grade based on marks obtained.
     * @group Exams & Gradings
    */
    private function calculateGrade(float $marks): string
    {
        if ($marks >= 90) return 'A+';
        if ($marks >= 80) return 'A';
        if ($marks >= 70) return 'B+';
        if ($marks >= 60) return 'B';
        if ($marks >= 50) return 'C+';
        if ($marks >= 40) return 'C';
        return 'F';
    }
} 