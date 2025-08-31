<?php

namespace App\Http\Controllers\Api\ExamsGradings;

use App\Http\Controllers\Controller;
use App\Models\ExamsGradings\{ReportCard, StudentGPA};
use App\Models\AdminAcademics\{ClassRoom, Term, AcademicYear};
use App\Models\User;
use App\Repositories\ExamsGradings\ReportCardRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class ReportCardController extends Controller
{
    public function __construct(
        private ReportCardRepositoryInterface $reportCardRepository
    ) {}

    /**
     * Display a listing of report cards.
     * @group Exams & Gradings
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'student_id', 'class_id', 'term_id', 'academic_year_id',
                'format', 'date_from', 'date_to'
            ]);

            $reportCards = $this->reportCardRepository->getPaginatedReportCards($filters);

            return response()->json([
                'success' => true,
                'data' => $reportCards,
                'message' => 'Report cards retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve report cards',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created report card.
     * @group Exams & Gradings
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'student_id' => 'required|exists:users,id',
                'class_id' => 'required|exists:class_rooms,id',
                'term_id' => 'required|exists:terms,id',
                'academic_year_id' => 'required|exists:academic_years,id',
                'gpa' => 'nullable|numeric|min:0|max:4.0',
                'cgpa' => 'nullable|numeric|min:0|max:4.0',
                'remarks' => 'nullable|string',
                'issued_date' => 'required|date',
                'format' => 'required|in:PDF,Digital',
            ]);

            // Check if report card already exists for this student, class, term, and academic year
            $existingReportCard = ReportCard::where('student_id', $validated['student_id'])
                                          ->where('class_id', $validated['class_id'])
                                          ->where('term_id', $validated['term_id'])
                                          ->where('academic_year_id', $validated['academic_year_id'])
                                          ->first();

            if ($existingReportCard) {
                return response()->json([
                    'success' => false,
                    'message' => 'Report card already exists for this student, class, term, and academic year'
                ], 422);
            }

            // Auto-calculate GPA and CGPA if not provided
            if (empty($validated['gpa'])) {
                $validated['gpa'] = $this->calculateGPA($validated['student_id'], $validated['term_id'], $validated['academic_year_id']);
            }

            if (empty($validated['cgpa'])) {
                $validated['cgpa'] = $this->calculateCGPA($validated['student_id'], $validated['academic_year_id']);
            }

            // Auto-generate remarks if not provided
            if (empty($validated['remarks'])) {
                $validated['remarks'] = $this->generateRemarks($validated['gpa']);
            }

            $reportCard = $this->reportCardRepository->createReportCard($validated);

            return response()->json([
                'success' => true,
                'data' => $reportCard->load(['student', 'class', 'term', 'academicYear']),
                'message' => 'Report card created successfully'
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
                'message' => 'Failed to create report card',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified report card.
     * @group Exams & Gradings
     */
    public function show(ReportCard $reportCard): JsonResponse
    {
        try {
            $reportCard->load(['student', 'class', 'term', 'academicYear']);

            return response()->json([
                'success' => true,
                'data' => $reportCard,
                'message' => 'Report card retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve report card',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified report card.
     * @group Exams & Gradings
     */
    public function update(Request $request, ReportCard $reportCard): JsonResponse
    {
        try {
            $validated = $request->validate([
                'gpa' => 'sometimes|required|numeric|min:0|max:4.0',
                'cgpa' => 'nullable|numeric|min:0|max:4.0',
                'remarks' => 'nullable|string',
                'issued_date' => 'sometimes|required|date',
                'format' => 'sometimes|required|in:PDF,Digital',
            ]);

            // Auto-calculate GPA and CGPA if not provided
            if (isset($validated['gpa']) && empty($validated['cgpa'])) {
                $validated['cgpa'] = $this->calculateCGPA($reportCard->student_id, $reportCard->academic_year_id);
            }

            // Auto-generate remarks if GPA is updated and remarks are not provided
            if (isset($validated['gpa']) && empty($validated['remarks'])) {
                $validated['remarks'] = $this->generateRemarks($validated['gpa']);
            }

            $updated = $this->reportCardRepository->updateReportCard($reportCard, $validated);

            if ($updated) {
                $reportCard->refresh()->load(['student', 'class', 'term', 'academicYear']);
                return response()->json([
                    'success' => true,
                    'data' => $reportCard,
                    'message' => 'Report card updated successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update report card'
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
                'message' => 'Failed to update report card',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified report card.
     * @group Exams & Gradings
     */
    public function destroy(ReportCard $reportCard): JsonResponse
    {
        try {
            $deleted = $this->reportCardRepository->deleteReportCard($reportCard);

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Report card deleted successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete report card'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete report card',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate GPA for a student in a specific term and academic year.
     * @group Exams & Gradings
     */
    private function calculateGPA(int $studentId, int $termId, int $academicYearId): float
    {
        $studentGPA = StudentGPA::where('student_id', $studentId)
                                ->where('term_id', $termId)
                                ->where('academic_year_id', $academicYearId)
                                ->first();

        return $studentGPA ? $studentGPA->gpa : 0.0;
    }

    /**
     * Calculate CGPA for a student in a specific academic year.
     * @group Exams & Gradings
     */
    private function calculateCGPA(int $studentId, int $academicYearId): float
    {
        $studentGPAs = StudentGPA::where('student_id', $studentId)
                                ->where('academic_year_id', $academicYearId)
                                ->get();

        if ($studentGPAs->isEmpty()) {
            return 0.0;
        }

        $totalGPA = $studentGPAs->sum('gpa');
        $termCount = $studentGPAs->count();

        return round($totalGPA / $termCount, 2);
    }

    /**
     * Generate remarks based on GPA.
     * @group Exams & Gradings
     */
    private function generateRemarks(float $gpa): string
    {
        if ($gpa >= 3.8) {
            return 'Outstanding performance! Keep up the excellent work.';
        } elseif ($gpa >= 3.0) {
            return 'Good performance. Continue to work hard and improve further.';
        } elseif ($gpa >= 2.0) {
            return 'Satisfactory performance. Focus on areas that need improvement.';
        } else {
            return 'Performance needs improvement. Please seek additional support and work harder.';
        }
    }

    /**
     * Get report cards by student.
     * @group Exams & Gradings
     */
    public function getByStudent(User $student): JsonResponse
    {
        try {
            $reportCards = $this->reportCardRepository->getReportCardsByStudent($student->id);

            return response()->json([
                'success' => true,
                'data' => $reportCards->load(['class', 'term', 'academicYear']),
                'message' => 'Report cards for student retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve report cards for student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get report cards by class.
     * @group Exams & Gradings
     */
    public function getByClass(ClassRoom $class): JsonResponse
    {
        try {
            $reportCards = $this->reportCardRepository->getReportCardsByClass($class->id);

            return response()->json([
                'success' => true,
                'data' => $reportCards->load(['student', 'term', 'academicYear']),
                'message' => 'Report cards for class retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve report cards for class',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get report cards by term.
     * @group Exams & Gradings
     */
    public function getByTerm(Term $term): JsonResponse
    {
        try {
            $reportCards = $this->reportCardRepository->getReportCardsByTerm($term->id);

            return response()->json([
                'success' => true,
                'data' => $reportCards->load(['student', 'class', 'academicYear']),
                'message' => 'Report cards for term retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve report cards for term',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get report cards by academic year.
     * @group Exams & Gradings
    */
    public function getByAcademicYear(AcademicYear $academicYear): JsonResponse
    {
        try {
            $reportCards = $this->reportCardRepository->getReportCardsByAcademicYear($academicYear->id);

            return response()->json([
                'success' => true,
                'data' => $reportCards->load(['student', 'class', 'term']),
                'message' => 'Report cards for academic year retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve report cards for academic year',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate a report card for a specific student, class, term, and academic year.
     * @group Exams & Gradings
    */
    public function generateReportCard(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'student_id' => 'required|exists:users,id',
                'class_id' => 'required|exists:class_rooms,id',
                'term_id' => 'required|exists:terms,id',
                'academic_year_id' => 'required|exists:academic_years,id',
            ]);

            $reportCard = $this->reportCardRepository->generateReportCard(
                $validated['student_id'],
                $validated['class_id'],
                $validated['term_id'],
                $validated['academic_year_id']
            );

            return response()->json([
                'success' => true,
                'data' => $reportCard->load(['student', 'class', 'term', 'academicYear']),
                'message' => 'Report card generated successfully'
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
                'message' => 'Failed to generate report card',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get report card statistics.
     * @group Exams & Gradings
    */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['class_id', 'term_id', 'academic_year_id', 'format', 'date_from', 'date_to']);
            $statistics = $this->reportCardRepository->getReportCardStatistics($filters);

            return response()->json([
                'success' => true,
                'data' => $statistics,
                'message' => 'Report card statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve report card statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 