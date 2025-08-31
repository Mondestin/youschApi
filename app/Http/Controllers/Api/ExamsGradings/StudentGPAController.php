<?php

namespace App\Http\Controllers\Api\ExamsGradings;

use App\Http\Controllers\Controller;
use App\Models\ExamsGradings\StudentGPA;
use App\Models\AdminAcademics\{Term, AcademicYear};
use App\Models\User;
use App\Repositories\ExamsGradings\StudentGPARepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class StudentGPAController extends Controller
{
    public function __construct(
        private StudentGPARepositoryInterface $studentGPARepository
    ) {}

    /**
     * Display a listing of student GPAs.
     * @group Exams & Gradings
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'student_id', 'term_id', 'academic_year_id', 'min_gpa', 'max_gpa',
                'performance_level'
            ]);

            $studentGPAs = $this->studentGPARepository->getAllStudentGPAs($filters);

            return response()->json([
                'success' => true,
                'data' => $studentGPAs,
                'message' => 'Student GPAs retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve student GPAs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created student GPA.
     * @group Exams & Gradings
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'student_id' => 'required|exists:users,id',
                'term_id' => 'required|exists:terms,id',
                'academic_year_id' => 'required|exists:academic_years,id',
                'gpa' => 'required|numeric|min:0|max:4.0',
                'cgpa' => 'nullable|numeric|min:0|max:4.0',
            ]);

            // Check if GPA already exists for this student, term, and academic year
            $existingGPA = StudentGPA::where('student_id', $validated['student_id'])
                                    ->where('term_id', $validated['term_id'])
                                    ->where('academic_year_id', $validated['term_id'])
                                    ->first();

            if ($existingGPA) {
                return response()->json([
                    'success' => false,
                    'message' => 'GPA already exists for this student, term, and academic year'
                ], 422);
            }

            // Auto-calculate CGPA if not provided
            if (empty($validated['cgpa'])) {
                $validated['cgpa'] = $this->calculateCGPAInternal($validated['student_id'], $validated['academic_year_id']);
            }

            $studentGPA = $this->studentGPARepository->createStudentGPA($validated);

            return response()->json([
                'success' => true,
                'data' => $studentGPA->load(['student', 'term', 'academicYear']),
                'message' => 'Student GPA created successfully'
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
                'message' => 'Failed to create student GPA',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified student GPA.
     * @group Exams & Gradings
     */
    public function show(StudentGPA $studentGPA): JsonResponse
    {
        try {
            $studentGPA->load(['student', 'term', 'academicYear']);

            return response()->json([
                'success' => true,
                'data' => $studentGPA,
                'message' => 'Student GPA retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve student GPA',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified student GPA.
     * @group Exams & Gradings
     */
    public function update(Request $request, StudentGPA $studentGPA): JsonResponse
    {
        try {
            $validated = $request->validate([
                'gpa' => 'sometimes|required|numeric|min:0|max:4.0',
                'cgpa' => 'nullable|numeric|min:0|max:4.0',
            ]);

            // Auto-calculate CGPA if GPA is updated and CGPA is not provided
            if (isset($validated['gpa']) && empty($validated['cgpa'])) {
                $validated['cgpa'] = $this->calculateCGPAInternal($studentGPA->student_id, $studentGPA->academic_year_id);
            }

            $updated = $this->studentGPARepository->updateStudentGPA($studentGPA, $validated);

            if ($updated) {
                $studentGPA->refresh()->load(['student', 'term', 'academicYear']);
                return response()->json([
                    'success' => true,
                    'data' => $studentGPA,
                    'message' => 'Student GPA updated successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update student GPA'
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
                'message' => 'Failed to update student GPA',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified student GPA.
     * @group Exams & Gradings
     */
    public function destroy(StudentGPA $studentGPA): JsonResponse
    {
        try {
            $deleted = $this->studentGPARepository->deleteStudentGPA($studentGPA);

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student GPA deleted successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete student GPA'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete student GPA',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student GPAs by student.
     * @group Exams & Gradings
     */
    public function getByStudent(User $student): JsonResponse
    {
        try {
            $studentGPAs = $this->studentGPARepository->getStudentGPAByStudent($student->id);

            return response()->json([
                'success' => true,
                'data' => $studentGPAs->load(['term', 'academicYear']),
                'message' => 'Student GPAs retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve student GPAs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student GPAs by term.
     * @group Exams & Gradings
     */
    public function getByTerm(Term $term): JsonResponse
    {
        try {
            $studentGPAs = $this->studentGPARepository->getStudentGPAByTerm($term->id);

            return response()->json([
                'success' => true,
                'data' => $studentGPAs->load(['student', 'academicYear']),
                'message' => 'Student GPAs for term retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve student GPAs for term',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student GPAs by academic year.
     * @group Exams & Gradings
     */
    public function getByAcademicYear(AcademicYear $academicYear): JsonResponse
    {
        try {
            $studentGPAs = $this->studentGPARepository->getStudentGPAByAcademicYear($academicYear->id);

            return response()->json([
                'success' => true,
                'data' => $studentGPAs->load(['student', 'term']),
                'message' => 'Student GPAs for academic year retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve student GPAs for academic year',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student GPA by student and term.
     * @group Exams & Gradings
     */
    public function getByStudentAndTerm(User $student, Term $term): JsonResponse
    {
        try {
            $studentGPA = $this->studentGPARepository->getStudentGPAByStudentAndTerm($student->id, $term->id);

            if (!$studentGPA) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student GPA not found for this student and term'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $studentGPA->load(['student', 'term', 'academicYear']),
                'message' => 'Student GPA retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve student GPA',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student GPA by student and academic year.
     * @group Exams & Gradings
     */
    public function getByStudentAndAcademicYear(User $student, AcademicYear $academicYear): JsonResponse
    {
        try {
            $studentGPA = $this->studentGPARepository->getStudentGPAByStudentAndAcademicYear($student->id, $academicYear->id);

            if (!$studentGPA) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student GPA not found for this student and academic year'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $studentGPA->load(['student', 'term', 'academicYear']),
                'message' => 'Student GPA retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve student GPA',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate GPA for a student in a specific term and academic year.
     * @group Exams & Gradings
     */
    public function calculateGPA(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'student_id' => 'required|exists:users,id',
                'term_id' => 'required|exists:terms,id',
                'academic_year_id' => 'required|exists:academic_years,id',
            ]);

            $gpa = $this->studentGPARepository->calculateStudentGPA(
                $validated['student_id'],
                $validated['term_id'],
                $validated['academic_year_id']
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'student_id' => $validated['student_id'],
                    'term_id' => $validated['term_id'],
                    'academic_year_id' => $validated['academic_year_id'],
                    'calculated_gpa' => $gpa,
                ],
                'message' => 'GPA calculated successfully'
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
                'message' => 'Failed to calculate GPA',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate CGPA for a student in a specific academic year.
     * @group Exams & Gradings
    */
    public function calculateCGPA(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'student_id' => 'required|exists:users,id',
                'academic_year_id' => 'required|exists:academic_years,id',
            ]);

            $cgpa = $this->studentGPARepository->calculateStudentCGPA(
                $validated['student_id'],
                $validated['academic_year_id']
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'student_id' => $validated['student_id'],
                    'academic_year_id' => $validated['academic_year_id'],
                    'calculated_cgpa' => $cgpa,
                ],
                'message' => 'CGPA calculated successfully'
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
                'message' => 'Failed to calculate CGPA',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get top performers in a specific term and academic year.
     * @group Exams & Gradings
    */
    public function getTopPerformers(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'term_id' => 'required|exists:terms,id',
                'academic_year_id' => 'required|exists:academic_years,id',
                'limit' => 'nullable|integer|min:1|max:100',
            ]);

            $limit = $validated['limit'] ?? 10;
            $topPerformers = $this->studentGPARepository->getTopPerformers(
                $validated['term_id'],
                $validated['academic_year_id'],
                $limit
            );

            return response()->json([
                'success' => true,
                'data' => $topPerformers->load(['student', 'term', 'academicYear']),
                'message' => 'Top performers retrieved successfully'
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
                'message' => 'Failed to retrieve top performers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get low performers in a specific term and academic year.
     * @group Exams & Gradings
    */
    public function getLowPerformers(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'term_id' => 'required|exists:terms,id',
                'academic_year_id' => 'required|exists:academic_years,id',
                'limit' => 'nullable|integer|min:1|max:100',
            ]);

            $limit = $validated['limit'] ?? 10;
            $lowPerformers = $this->studentGPARepository->getLowPerformers(
                $validated['term_id'],
                $validated['academic_year_id'],
                $limit
            );

            return response()->json([
                'success' => true,
                'data' => $lowPerformers->load(['student', 'term', 'academicYear']),
                'message' => 'Low performers retrieved successfully'
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
                'message' => 'Failed to retrieve low performers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get GPA distribution for a specific term and academic year.
     * @group Exams & Gradings
    */
    public function getGPADistribution(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'term_id' => 'required|exists:terms,id',
                'academic_year_id' => 'required|exists:academic_years,id',
            ]);

            $distribution = $this->studentGPARepository->getGPADistribution(
                $validated['term_id'],
                $validated['academic_year_id']
            );

            return response()->json([
                'success' => true,
                'data' => $distribution,
                'message' => 'GPA distribution retrieved successfully'
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
                'message' => 'Failed to retrieve GPA distribution',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get GPA statistics with filters.
     * @group Exams & Gradings
    */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['term_id', 'academic_year_id', 'class_id', 'min_gpa', 'max_gpa']);
            $statistics = $this->studentGPARepository->getGPAStatistics($filters);

            return response()->json([
                'success' => true,
                'data' => $statistics,
                'message' => 'GPA statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve GPA statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get performance trends for a student across terms.
     * @group Exams & Gradings
    */
    public function getPerformanceTrends(User $student, AcademicYear $academicYear): JsonResponse
    {
        try {
            $trends = $this->studentGPARepository->getPerformanceTrends($student->id, $academicYear->id);

            return response()->json([
                'success' => true,
                'data' => $trends,
                'message' => 'Performance trends retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve performance trends',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get academic performance report.
     * @group Exams & Gradings
    */
    public function academicPerformanceReport(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'academic_year_id' => 'required|exists:academic_years,id',
                'term_id' => 'nullable|exists:terms,id',
                'class_id' => 'nullable|exists:class_rooms,id',
                'performance_level' => 'nullable|in:excellent,good,satisfactory,poor',
            ]);

            $query = StudentGPA::with(['student', 'term'])
                              ->where('academic_year_id', $validated['academic_year_id']);

            if (isset($validated['term_id'])) {
                $query->where('term_id', $validated['term_id']);
            }

            if (isset($validated['class_id'])) {
                $query->whereHas('student', function ($q) use ($validated) {
                    $q->where('class_id', $validated['class_id']);
                });
            }

            if (isset($validated['performance_level'])) {
                switch ($validated['performance_level']) {
                    case 'excellent':
                        $query->where('gpa', '>=', 3.8);
                        break;
                    case 'good':
                        $query->where('gpa', '>=', 3.0)->where('gpa', '<', 3.8);
                        break;
                    case 'satisfactory':
                        $query->where('gpa', '>=', 2.0)->where('gpa', '<', 3.0);
                        break;
                    case 'poor':
                        $query->where('gpa', '<', 2.0);
                        break;
                }
            }

            $studentGPAs = $query->get();

            // Calculate performance metrics
            $totalStudents = $studentGPAs->count();
            $excellentStudents = $studentGPAs->filter(fn($gpa) => $gpa->isExcellent())->count();
            $goodStudents = $studentGPAs->filter(fn($gpa) => $gpa->isGood())->count();
            $satisfactoryStudents = $studentGPAs->filter(fn($gpa) => $gpa->isSatisfactory())->count();
            $poorStudents = $studentGPAs->filter(fn($gpa) => $gpa->isPoor())->count();

            $averageGPA = $studentGPAs->avg('gpa');
            $averageCGPA = $studentGPAs->avg('cgpa');
            $highestGPA = $studentGPAs->max('gpa');
            $lowestGPA = $studentGPAs->min('gpa');

            $performanceReport = [
                'academic_year_id' => $validated['academic_year_id'],
                'term_id' => $validated['term_id'] ?? 'All Terms',
                'total_students' => $totalStudents,
                'performance_distribution' => [
                    'excellent' => [
                        'count' => $excellentStudents,
                        'percentage' => $totalStudents > 0 ? round(($excellentStudents / $totalStudents) * 100, 2) : 0,
                    ],
                    'good' => [
                        'count' => $goodStudents,
                        'percentage' => $totalStudents > 0 ? round(($goodStudents / $totalStudents) * 100, 2) : 0,
                    ],
                    'satisfactory' => [
                        'count' => $satisfactoryStudents,
                        'percentage' => $totalStudents > 0 ? round(($satisfactoryStudents / $totalStudents) * 100, 2) : 0,
                    ],
                    'poor' => [
                        'count' => $poorStudents,
                        'percentage' => $totalStudents > 0 ? round(($poorStudents / $totalStudents) * 100, 2) : 0,
                    ],
                ],
                'statistics' => [
                    'average_gpa' => round($averageGPA, 2),
                    'average_cgpa' => round($averageCGPA, 2),
                    'highest_gpa' => $highestGPA,
                    'lowest_gpa' => $lowestGPA,
                ],
                'student_gpas' => $studentGPAs->take(100), // Limit to first 100 for performance
            ];

            return response()->json([
                'success' => true,
                'data' => $performanceReport,
                'message' => 'Academic performance report generated successfully'
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
                'message' => 'Failed to generate academic performance report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate CGPA for a student in a specific academic year.
     * @group Exams & Gradings
    */
    private function calculateCGPAInternal(int $studentId, int $academicYearId): float
    {
        // This is a simplified calculation - in a real implementation,
        // you would calculate based on all terms in the academic year
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
} 