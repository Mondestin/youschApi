<?php

namespace App\Repositories\Students;

use App\Models\Students\AcademicHistory;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AcademicHistoryRepository extends BaseRepository
{
    public function __construct(AcademicHistory $model)
    {
        parent::__construct($model);
    }

    public function getPaginatedHistory(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['student', 'subject', 'classRoom', 'term', 'academicYear']);

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (isset($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (isset($filters['term_id'])) {
            $query->where('term_id', $filters['term_id']);
        }

        if (isset($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        if (isset($filters['grade'])) {
            $query->where('grade', $filters['grade']);
        }

        if (isset($filters['min_gpa'])) {
            $query->where('gpa', '>=', $filters['min_gpa']);
        }

        if (isset($filters['max_gpa'])) {
            $query->where('gpa', '<=', $filters['max_gpa']);
        }

        return $query->orderBy('academic_year_id', 'desc')
            ->orderBy('term_id', 'desc')
            ->paginate($perPage);
    }

    public function getHistoryById(int $id, array $relationships = []): ?AcademicHistory
    {
        $query = $this->model->where('id', $id);
        
        if (!empty($relationships)) {
            $query->with($relationships);
        }
        
        return $query->first();
    }

    public function createHistory(array $data): AcademicHistory
    {
        return $this->model->create($data);
    }

    public function updateHistory(AcademicHistory $history, array $data): bool
    {
        return $history->update($data);
    }

    public function deleteHistory(AcademicHistory $history): bool
    {
        return $history->delete();
    }

    public function getHistoryByStudent(int $studentId): Collection
    {
        return $this->model->where('student_id', $studentId)
            ->with(['subject', 'classRoom', 'term', 'academicYear'])
            ->orderBy('academic_year_id', 'desc')
            ->orderBy('term_id', 'desc')
            ->get();
    }

    public function getHistoryBySubject(int $subjectId): Collection
    {
        return $this->model->where('subject_id', $subjectId)
            ->with(['student', 'classRoom', 'term', 'academicYear'])
            ->orderBy('academic_year_id', 'desc')
            ->orderBy('term_id', 'desc')
            ->get();
    }

    public function getHistoryByClass(int $classId): Collection
    {
        return $this->model->where('class_id', $classId)
            ->with(['student', 'subject', 'term', 'academicYear'])
            ->orderBy('academic_year_id', 'desc')
            ->orderBy('term_id', 'desc')
            ->get();
    }

    public function getHistoryByTerm(int $termId): Collection
    {
        return $this->model->where('term_id', $termId)
            ->with(['student', 'subject', 'classRoom', 'academicYear'])
            ->orderBy('student_id')
            ->get();
    }

    public function getHistoryByAcademicYear(int $academicYearId): Collection
    {
        return $this->model->where('academic_year_id', $academicYearId)
            ->with(['student', 'subject', 'classRoom', 'term'])
            ->orderBy('term_id', 'desc')
            ->orderBy('student_id')
            ->get();
    }

    public function getAcademicPerformance(int $studentId): array
    {
        $history = $this->getHistoryByStudent($studentId);
        
        $overallGPA = $history->avg('gpa');
        $totalSubjects = $history->count();
        $passingSubjects = $history->where('grade', '!=', 'F')->count();
        $failingSubjects = $history->where('grade', 'F')->count();

        return [
            'overall_gpa' => round($overallGPA, 2),
            'total_subjects' => $totalSubjects,
            'passing_subjects' => $passingSubjects,
            'failing_subjects' => $failingSubjects,
            'pass_rate' => $totalSubjects > 0 ? round(($passingSubjects / $totalSubjects) * 100, 2) : 0,
        ];
    }

    public function getClassPerformance(int $classId, int $termId, int $academicYearId): array
    {
        $history = $this->model->where('class_id', $classId)
            ->where('term_id', $termId)
            ->where('academic_year_id', $academicYearId)
            ->with(['student', 'subject'])
            ->get();

        $totalStudents = $history->unique('student_id')->count();
        $averageGPA = $history->avg('gpa');
        $passingStudents = $history->groupBy('student_id')
            ->filter(function($studentHistory) {
                return $studentHistory->where('grade', '!=', 'F')->count() === $studentHistory->count();
            })
            ->count();

        return [
            'total_students' => $totalStudents,
            'average_gpa' => round($averageGPA, 2),
            'passing_students' => $passingStudents,
            'pass_rate' => $totalStudents > 0 ? round(($passingStudents / $totalStudents) * 100, 2) : 0,
        ];
    }

    public function getSubjectPerformance(int $subjectId, int $termId, int $academicYearId): array
    {
        $history = $this->model->where('subject_id', $subjectId)
            ->where('term_id', $termId)
            ->where('academic_year_id', $academicYearId)
            ->with(['student', 'classRoom'])
            ->get();

        $totalStudents = $history->count();
        $averageMarks = $history->avg('marks');
        $averageGPA = $history->avg('gpa');
        $passingStudents = $history->where('grade', '!=', 'F')->count();

        return [
            'total_students' => $totalStudents,
            'average_marks' => round($averageMarks, 2),
            'average_gpa' => round($averageGPA, 2),
            'passing_students' => $passingStudents,
            'pass_rate' => $totalStudents > 0 ? round(($passingStudents / $totalStudents) * 100, 2) : 0,
        ];
    }

    public function getHistoryStatistics(array $filters = []): array
    {
        $query = $this->model->query();

        if (isset($filters['school_id'])) {
            $query->whereHas('student', function($q) use ($filters) {
                $q->where('school_id', $filters['school_id']);
            });
        }

        $total = $query->count();
        $averageGPA = $query->avg('gpa');
        $totalMarks = $query->sum('marks');

        $byGrade = $query->selectRaw('grade, COUNT(*) as count')
            ->groupBy('grade')
            ->orderBy('grade')
            ->get();

        $byTerm = $query->selectRaw('term_id, COUNT(*) as count')
            ->groupBy('term_id')
            ->orderBy('term_id', 'desc')
            ->get();

        return [
            'total_records' => $total,
            'average_gpa' => round($averageGPA, 2),
            'total_marks' => $totalMarks,
            'records_by_grade' => $byGrade,
            'records_by_term' => $byTerm,
        ];
    }
} 