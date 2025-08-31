<?php

namespace App\Repositories\ExamsGradings;

use App\Models\ExamsGradings\ExamMark;
use App\Models\AdminAcademics\Exam;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ExamMarkRepository implements ExamMarkRepositoryInterface
{
    public function getPaginatedExamMarks(array $filters): LengthAwarePaginator
    {
        $query = ExamMark::with(['exam', 'student']);

        if (isset($filters['exam_id'])) {
            $query->where('exam_id', $filters['exam_id']);
        }

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (isset($filters['grade'])) {
            $query->where('grade', $filters['grade']);
        }

        if (isset($filters['min_marks'])) {
            $query->where('marks_obtained', '>=', $filters['min_marks']);
        }

        if (isset($filters['max_marks'])) {
            $query->where('marks_obtained', '<=', $filters['max_marks']);
        }

        if (isset($filters['has_grade']) && $filters['has_grade']) {
            $query->whereNotNull('grade');
        }

        if (isset($filters['is_passing'])) {
            if ($filters['is_passing']) {
                $query->where('marks_obtained', '>=', 40); // Assuming 40 is passing marks
            } else {
                $query->where('marks_obtained', '<', 40);
            }
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function getAllExamMarks(array $filters): Collection
    {
        $query = ExamMark::with(['exam', 'student']);

        if (isset($filters['exam_id'])) {
            $query->where('exam_id', $filters['exam_id']);
        }

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (isset($filters['grade'])) {
            $query->where('grade', $filters['grade']);
        }

        if (isset($filters['min_marks'])) {
            $query->where('marks_obtained', '>=', $filters['min_marks']);
        }

        if (isset($filters['max_marks'])) {
            $query->where('marks_obtained', '<=', $filters['max_marks']);
        }

        if (isset($filters['has_grade']) && $filters['has_grade']) {
            $query->whereNotNull('grade');
        }

        if (isset($filters['is_passing'])) {
            if ($filters['is_passing']) {
                $query->where('marks_obtained', '>=', 40); // Assuming 40 is passing marks
            } else {
                $query->where('marks_obtained', '<', 40);
            }
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getExamMarkById(int $id): ?ExamMark
    {
        return ExamMark::with(['exam', 'student'])->find($id);
    }

    public function createExamMark(array $data): ExamMark
    {
        return ExamMark::create($data);
    }

    public function updateExamMark(ExamMark $examMark, array $data): bool
    {
        return $examMark->update($data);
    }

    public function deleteExamMark(ExamMark $examMark): bool
    {
        return $examMark->delete();
    }

    public function getExamMarksByExam(int $examId): Collection
    {
        return ExamMark::with(['student'])
            ->where('exam_id', $examId)
            ->orderBy('marks_obtained', 'desc')
            ->get();
    }

    public function getExamMarksByStudent(int $studentId): Collection
    {
        return ExamMark::with(['exam.subject', 'exam.examType'])
            ->where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getExamMarksByExamAndStudent(int $examId, int $studentId): ?ExamMark
    {
        return ExamMark::with(['exam', 'student'])
            ->where('exam_id', $examId)
            ->where('student_id', $studentId)
            ->first();
    }

    public function bulkCreateExamMarks(array $examMarksData): array
    {
        $createdMarks = [];
        foreach ($examMarksData as $markData) {
            $createdMarks[] = ExamMark::create($markData);
        }
        return $createdMarks;
    }

    public function bulkUpdateExamMarks(array $examMarksData): array
    {
        $updatedMarks = [];
        foreach ($examMarksData as $markData) {
            $examMark = ExamMark::find($markData['id']);
            if ($examMark) {
                unset($markData['id']);
                $examMark->update($markData);
                $updatedMarks[] = $examMark;
            }
        }
        return $updatedMarks;
    }

    public function getExamMarkStatistics(array $filters = []): array
    {
        $query = ExamMark::query();

        if (isset($filters['exam_id'])) {
            $query->where('exam_id', $filters['exam_id']);
        }

        if (isset($filters['class_id'])) {
            $query->whereHas('exam', function ($q) use ($filters) {
                $q->where('class_id', $filters['class_id']);
            });
        }

        if (isset($filters['subject_id'])) {
            $query->whereHas('exam', function ($q) use ($filters) {
                $q->where('subject_id', $filters['subject_id']);
            });
        }

        if (isset($filters['date_from'])) {
            $query->whereHas('exam', function ($q) use ($filters) {
                $q->where('exam_date', '>=', $filters['date_from']);
            });
        }

        if (isset($filters['date_to'])) {
            $query->whereHas('exam', function ($q) use ($filters) {
                $q->where('exam_date', '<=', $filters['date_to']);
            });
        }

        $totalMarks = $query->count();
        $passingMarks = $query->where('marks_obtained', '>=', 40)->count();
        $averageMarks = $query->avg('marks_obtained');
        $highestMarks = $query->max('marks_obtained');
        $lowestMarks = $query->min('marks_obtained');

        return [
            'total_marks' => $totalMarks,
            'passing_marks' => $passingMarks,
            'failing_marks' => $totalMarks - $passingMarks,
            'pass_rate' => $totalMarks > 0 ? round(($passingMarks / $totalMarks) * 100, 2) : 0,
            'average_marks' => round($averageMarks, 2),
            'highest_marks' => $highestMarks,
            'lowest_marks' => $lowestMarks,
        ];
    }

    public function getStudentPerformanceInExam(int $examId, int $studentId): array
    {
        $examMark = ExamMark::with(['exam', 'student'])
            ->where('exam_id', $examId)
            ->where('student_id', $studentId)
            ->first();

        if (!$examMark) {
            return [
                'found' => false,
                'message' => 'Exam mark not found for this student and exam'
            ];
        }

        $exam = $examMark->exam;
        $totalStudents = ExamMark::where('exam_id', $examId)->count();
        $studentRank = ExamMark::where('exam_id', $examId)
            ->where('marks_obtained', '>', $examMark->marks_obtained)
            ->count() + 1;

        return [
            'found' => true,
            'exam_mark' => $examMark,
            'total_students' => $totalStudents,
            'student_rank' => $studentRank,
            'percentile' => $totalStudents > 0 ? round((($totalStudents - $studentRank + 1) / $totalStudents) * 100, 2) : 0,
            'is_passing' => $examMark->marks_obtained >= 40,
        ];
    }

    public function getExamResults(int $examId): array
    {
        $exam = Exam::with(['subject', 'classRoom'])->find($examId);
        if (!$exam) {
            return ['error' => 'Exam not found'];
        }

        $examMarks = ExamMark::with(['student'])
            ->where('exam_id', $examId)
            ->orderBy('marks_obtained', 'desc')
            ->get();

        $totalStudents = $examMarks->count();
        $passingStudents = $examMarks->where('marks_obtained', '>=', 40)->count();
        $averageMarks = $examMarks->avg('marks_obtained');

        $gradeDistribution = $examMarks->groupBy('grade')
            ->map(fn($group) => $group->count())
            ->toArray();

        return [
            'exam' => $exam,
            'total_students' => $totalStudents,
            'passing_students' => $passingStudents,
            'failing_students' => $totalStudents - $passingStudents,
            'pass_rate' => $totalStudents > 0 ? round(($passingStudents / $totalStudents) * 100, 2) : 0,
            'average_marks' => round($averageMarks, 2),
            'highest_marks' => $examMarks->max('marks_obtained'),
            'lowest_marks' => $examMarks->min('marks_obtained'),
            'grade_distribution' => $gradeDistribution,
            'exam_marks' => $examMarks,
        ];
    }
} 