<?php

namespace App\Repositories\AdminAcademics;

use App\Repositories\BaseRepository;
use App\Models\AdminAcademics\Exam;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class ExamRepository extends BaseRepository
{
    /**
     * Constructor
     */
    public function __construct(Exam $model)
    {
        parent::__construct($model);
    }

    /**
     * Get scheduled exams
     */
    public function getScheduledExams(): Collection
    {
        return $this->model->byStatus('scheduled')->get();
    }

    /**
     * Get exams by exam type
     */
    public function getExamsByExamType(int $examTypeId): Collection
    {
        return $this->model->byExamType($examTypeId)->get();
    }

    /**
     * Get upcoming exams
     */
    public function getUpcomingExams(): Collection
    {
        return $this->model->upcoming()->get();
    }

    /**
     * Get exams by date range
     */
    public function getExamsByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->model->byDateRange($startDate, $endDate)->get();
    }

    /**
     * Get exams for a specific class
     */
    public function getExamsByClass(int $classId): Collection
    {
        return $this->model->where('class_id', $classId)->get();
    }

    /**
     * Get exams for a specific subject
     */
    public function getExamsBySubject(int $subjectId): Collection
    {
        return $this->model->where('subject_id', $subjectId)->get();
    }

    /**
     * Get exams by examiner
     */
    public function getExamsByExaminer(int $examinerId): Collection
    {
        return $this->model->where('examiner_id', $examinerId)->get();
    }

    /**
     * Get exams scheduled for today
     */
    public function getTodayExams(): Collection
    {
        $today = Carbon::today()->toDateString();
        return $this->model->where('exam_date', $today)->get();
    }

    /**
     * Get exams scheduled for this week
     */
    public function getThisWeekExams(): Collection
    {
        $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
        $endOfWeek = Carbon::now()->endOfWeek()->toDateString();
        return $this->model->byDateRange($startOfWeek, $endOfWeek)->get();
    }

    /**
     * Get exams scheduled for this month
     */
    public function getThisMonthExams(): Collection
    {
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
        return $this->model->byDateRange($startOfMonth, $endOfMonth)->get();
    }

    /**
     * Get exams with student grades
     */
    public function getExamsWithGrades(): Collection
    {
        return $this->model->with('studentGrades')->get();
    }

    /**
     * Get exam statistics
     */
    public function getExamStatistics(int $schoolId = null): array
    {
        $query = $this->model->newQuery();
        
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        $totalExams = $query->count();
        $activeExams = $query->where('is_active', true)->count();
        $upcomingExams = $query->upcoming()->count();
        $todayExams = $query->where('exam_date', Carbon::today()->toDateString())->count();

        return [
            'total_exams' => $totalExams,
            'active_exams' => $activeExams,
            'upcoming_exams' => $upcomingExams,
            'today_exams' => $todayExams,
        ];
    }

    /**
     * Get exams by academic year
     */
    public function getExamsByAcademicYear(int $academicYearId): Collection
    {
        return $this->model->whereHas('classRoom', function($query) use ($academicYearId) {
            $query->whereHas('course', function($q) use ($academicYearId) {
                $q->whereHas('department', function($d) use ($academicYearId) {
                    $d->whereHas('faculty', function($f) use ($academicYearId) {
                        $f->whereHas('school', function($s) use ($academicYearId) {
                            $s->whereHas('academicYears', function($ay) use ($academicYearId) {
                                $ay->where('id', $academicYearId);
                            });
                        });
                    });
                });
            });
        })->get();
    }

    /**
     * Search exams by name or instructions
     */
    public function searchExams(string $searchTerm): Collection
    {
        return $this->model->where('name', 'like', "%{$searchTerm}%")
                          ->orWhere('instructions', 'like', "%{$searchTerm}%")
                          ->get();
    }

    /**
     * Get exams with related data
     */
    public function getExamsWithRelations(): Collection
    {
        return $this->model->with([
            'subject',
            'classRoom',
            'examiner',
            'examType',
            'lab',
            'studentGrades'
        ])->get();
    }

    /**
     * Get exam by ID with relations
     */
    public function findWithRelations(int $id)
    {
        return $this->model->with([
            'subject',
            'classRoom',
            'examiner',
            'examType',
            'lab',
            'studentGrades'
        ])->find($id);
    }
} 