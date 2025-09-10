<?php

namespace App\Repositories\ExamsGradings;

use App\Models\ExamsGradings\StudentGPA;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class StudentGPARepository implements StudentGPARepositoryInterface
{
    public function getPaginatedStudentGPAs(array $filters): LengthAwarePaginator
    {
        $query = StudentGPA::with(['student', 'term', 'academicYear']);

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (isset($filters['term_id'])) {
            $query->where('term_id', $filters['term_id']);
        }

        if (isset($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        if (isset($filters['min_gpa'])) {
            $query->where('gpa', '>=', $filters['min_gpa']);
        }

        if (isset($filters['max_gpa'])) {
            $query->where('gpa', '<=', $filters['max_gpa']);
        }

        if (isset($filters['performance_level'])) {
            switch ($filters['performance_level']) {
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

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function getAllStudentGPAs(array $filters): Collection
    {
        $query = StudentGPA::with(['student', 'term', 'academicYear']);

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (isset($filters['term_id'])) {
            $query->where('term_id', $filters['term_id']);
        }

        if (isset($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        if (isset($filters['min_gpa'])) {
            $query->where('gpa', '>=', $filters['min_gpa']);
        }

        if (isset($filters['max_gpa'])) {
            $query->where('gpa', '<=', $filters['max_gpa']);
        }

        if (isset($filters['performance_level'])) {
            switch ($filters['performance_level']) {
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

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getStudentGPAById(int $id): ?StudentGPA
    {
        return StudentGPA::with(['student', 'term', 'academicYear'])->find($id);
    }

    public function createStudentGPA(array $data): StudentGPA
    {
        return StudentGPA::create($data);
    }

    public function updateStudentGPA(StudentGPA $studentGPA, array $data): bool
    {
        return $studentGPA->update($data);
    }

    public function deleteStudentGPA(StudentGPA $studentGPA): bool
    {
        return $studentGPA->delete();
    }

    public function getStudentGPAByStudent(int $studentId): Collection
    {
        return StudentGPA::with(['term', 'academicYear'])
            ->where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getStudentGPAByTerm(int $termId): Collection
    {
        return StudentGPA::with(['student', 'academicYear'])
            ->where('term_id', $termId)
            ->orderBy('gpa', 'desc')
            ->get();
    }

    public function getStudentGPAByAcademicYear(int $academicYearId): Collection
    {
        return StudentGPA::with(['student', 'term'])
            ->where('academic_year_id', $academicYearId)
            ->orderBy('gpa', 'desc')
            ->get();
    }

    public function getStudentGPAByStudentAndTerm(int $studentId, int $termId): ?StudentGPA
    {
        return StudentGPA::with(['student', 'term', 'academicYear'])
            ->where('student_id', $studentId)
            ->where('term_id', $termId)
            ->first();
    }

    public function getStudentGPAByStudentAndAcademicYear(int $studentId, int $academicYearId): ?StudentGPA
    {
        return StudentGPA::with(['student', 'term', 'academicYear'])
            ->where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->first();
    }

    public function calculateStudentGPA(int $studentId, int $termId, int $academicYearId): float
    {
        // This is a simplified calculation - in a real implementation,
        // you would calculate based on exam marks and their weights
        $studentGPA = StudentGPA::where('student_id', $studentId)
            ->where('term_id', $termId)
            ->where('academic_year_id', $academicYearId)
            ->first();

        return $studentGPA ? $studentGPA->gpa : 0.0;
    }

    public function calculateStudentCGPA(int $studentId, int $academicYearId): float
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

    public function getTopPerformers(int $termId, int $academicYearId, int $limit = 10): Collection
    {
        return StudentGPA::with(['student', 'term', 'academicYear'])
            ->where('term_id', $termId)
            ->where('academic_year_id', $academicYearId)
            ->orderBy('gpa', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getLowPerformers(int $termId, int $academicYearId, int $limit = 10): Collection
    {
        return StudentGPA::with(['student', 'term', 'academicYear'])
            ->where('term_id', $termId)
            ->where('academic_year_id', $academicYearId)
            ->orderBy('gpa', 'asc')
            ->limit($limit)
            ->get();
    }

    public function getGPADistribution(int $termId, int $academicYearId): array
    {
        $studentGPAs = StudentGPA::where('term_id', $termId)
            ->where('academic_year_id', $academicYearId)
            ->get();

        $totalStudents = $studentGPAs->count();
        $excellentStudents = $studentGPAs->where('gpa', '>=', 3.8)->count();
        $goodStudents = $studentGPAs->where('gpa', '>=', 3.0)->where('gpa', '<', 3.8)->count();
        $satisfactoryStudents = $studentGPAs->where('gpa', '>=', 2.0)->where('gpa', '<', 3.0)->count();
        $poorStudents = $studentGPAs->where('gpa', '<', 2.0)->count();

        return [
            'total_students' => $totalStudents,
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
        ];
    }

    public function getGPAStatistics(array $filters = []): array
    {
        $query = StudentGPA::query();

        if (isset($filters['term_id'])) {
            $query->where('term_id', $filters['term_id']);
        }

        if (isset($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        if (isset($filters['class_id'])) {
            $query->whereHas('student', function ($q) use ($filters) {
                $q->where('class_id', $filters['class_id']);
            });
        }

        if (isset($filters['min_gpa'])) {
            $query->where('gpa', '>=', $filters['min_gpa']);
        }

        if (isset($filters['max_gpa'])) {
            $query->where('gpa', '<=', $filters['max_gpa']);
        }

        $totalGPAs = $query->count();
        $averageGPA = $query->avg('gpa');
        $highestGPA = $query->max('gpa');
        $lowestGPA = $query->min('gpa');

        return [
            'total_gpas' => $totalGPAs,
            'average_gpa' => round($averageGPA, 2),
            'highest_gpa' => $highestGPA,
            'lowest_gpa' => $lowestGPA,
        ];
    }
} 