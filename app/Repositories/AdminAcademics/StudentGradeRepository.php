<?php

namespace App\Repositories\AdminAcademics;

use App\Repositories\BaseRepository;
use App\Models\AdminAcademics\StudentGrade;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StudentGradeRepository extends BaseRepository
{
    /**
     * Constructor
     */
    public function __construct(StudentGrade $model)
    {
        parent::__construct($model);
    }

    /**
     * Get grades by student
     */
    public function getGradesByStudent(int $studentId): Collection
    {
        return $this->model->where('student_id', $studentId)->get();
    }

    /**
     * Get grades by subject
     */
    public function getGradesBySubject(int $subjectId): Collection
    {
        return $this->model->where('subject_id', $subjectId)->get();
    }

    /**
     * Get grades by class
     */
    public function getGradesByClass(int $classId): Collection
    {
        return $this->model->where('class_id', $classId)->get();
    }

    /**
     * Get grades by exam
     */
    public function getGradesByExam(int $examId): Collection
    {
        return $this->model->where('exam_id', $examId)->get();
    }

    /**
     * Get grades by academic year
     */
    public function getGradesByAcademicYear(int $academicYearId): Collection
    {
        return $this->model->where('academic_year_id', $academicYearId)->get();
    }

    /**
     * Get grades by term
     */
    public function getGradesByTerm(int $termId): Collection
    {
        return $this->model->where('term_id', $termId)->get();
    }

    /**
     * Get grades by school
     */
    public function getGradesBySchool(int $schoolId): Collection
    {
        return $this->model->where('school_id', $schoolId)->get();
    }

    /**
     * Get student's average grade for a subject
     */
    public function getStudentSubjectAverage(int $studentId, int $subjectId): float
    {
        $grades = $this->model->where('student_id', $studentId)
                              ->where('subject_id', $subjectId)
                              ->get();

        if ($grades->isEmpty()) {
            return 0.0;
        }

        $totalScore = $grades->sum('score');
        $totalMaxScore = $grades->sum('max_score');

        return $totalMaxScore > 0 ? ($totalScore / $totalMaxScore) * 100 : 0.0;
    }

    /**
     * Get student's average grade for a class
     */
    public function getStudentClassAverage(int $studentId, int $classId): float
    {
        $grades = $this->model->where('student_id', $studentId)
                              ->where('class_id', $classId)
                              ->get();

        if ($grades->isEmpty()) {
            return 0.0;
        }

        $totalScore = $grades->sum('score');
        $totalMaxScore = $grades->sum('max_score');

        return $totalMaxScore > 0 ? ($totalScore / $totalMaxScore) * 100 : 0.0;
    }

    /**
     * Get class average for a subject
     */
    public function getClassSubjectAverage(int $classId, int $subjectId): float
    {
        $grades = $this->model->where('class_id', $classId)
                              ->where('subject_id', $subjectId)
                              ->get();

        if ($grades->isEmpty()) {
            return 0.0;
        }

        $totalScore = $grades->sum('score');
        $totalMaxScore = $grades->sum('max_score');

        return $totalMaxScore > 0 ? ($totalScore / $totalMaxScore) * 100 : 0.0;
    }

    /**
     * Get student's GPA for an academic year
     */
    public function getStudentGPA(int $studentId, int $academicYearId): float
    {
        $grades = $this->model->where('student_id', $studentId)
                              ->where('academic_year_id', $academicYearId)
                              ->get();

        if ($grades->isEmpty()) {
            return 0.0;
        }

        $totalGradePoints = 0;
        $totalCredits = 0;

        foreach ($grades as $grade) {
            // Convert percentage to GPA (assuming 4.0 scale)
            $percentage = $grade->percentage;
            $gpa = $this->percentageToGPA($percentage);
            $totalGradePoints += $gpa;
            $totalCredits += 1; // Assuming 1 credit per subject
        }

        return $totalCredits > 0 ? $totalGradePoints / $totalCredits : 0.0;
    }

    /**
     * Get top performing students in a class
     */
    public function getTopStudentsInClass(int $classId, int $limit = 10): Collection
    {
        return $this->model->select('student_id', DB::raw('AVG(percentage) as average_percentage'))
                          ->where('class_id', $classId)
                          ->groupBy('student_id')
                          ->orderBy('average_percentage', 'desc')
                          ->limit($limit)
                          ->get();
    }

    /**
     * Get failing students in a class
     */
    public function getFailingStudentsInClass(int $classId, float $passingThreshold = 60.0): Collection
    {
        return $this->model->select('student_id', DB::raw('AVG(percentage) as average_percentage'))
                          ->where('class_id', $classId)
                          ->groupBy('student_id')
                          ->having('average_percentage', '<', $passingThreshold)
                          ->get();
    }

    /**
     * Get grade distribution for a class
     */
    public function getClassGradeDistribution(int $classId): array
    {
        $grades = $this->model->where('class_id', $classId)->get();

        $distribution = [
            'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0
        ];

        foreach ($grades as $grade) {
            $letterGrade = $this->percentageToLetterGrade($grade->percentage);
            if (isset($distribution[$letterGrade])) {
                $distribution[$letterGrade]++;
            }
        }

        return $distribution;
    }

    /**
     * Get grades with relations
     */
    public function getGradesWithRelations(): Collection
    {
        return $this->model->with([
            'student',
            'subject',
            'classRoom',
            'exam',
            'academicYear',
            'term',
            'gradedBy',
            'school'
        ])->get();
    }

    /**
     * Find grade by ID with relations
     */
    public function findWithRelations(int $id)
    {
        return $this->model->with([
            'student',
            'subject',
            'classRoom',
            'exam',
            'academicYear',
            'term',
            'gradedBy',
            'school'
        ])->find($id);
    }

    /**
     * Convert percentage to letter grade
     */
    private function percentageToLetterGrade(float $percentage): string
    {
        if ($percentage >= 90) return 'A';
        if ($percentage >= 80) return 'B';
        if ($percentage >= 70) return 'C';
        if ($percentage >= 60) return 'D';
        return 'F';
    }

    /**
     * Convert percentage to GPA (4.0 scale)
     */
    private function percentageToGPA(float $percentage): float
    {
        if ($percentage >= 90) return 4.0;
        if ($percentage >= 80) return 3.0;
        if ($percentage >= 70) return 2.0;
        if ($percentage >= 60) return 1.0;
        return 0.0;
    }
} 