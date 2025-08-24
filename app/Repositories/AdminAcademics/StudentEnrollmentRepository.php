<?php

namespace App\Repositories\AdminAcademics;

use App\Repositories\BaseRepository;
use App\Models\AdminAcademics\StudentEnrollment;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class StudentEnrollmentRepository extends BaseRepository
{
    /**
     * Constructor
     */
    public function __construct(StudentEnrollment $model)
    {
        parent::__construct($model);
    }

    /**
     * Get enrollments by student
     */
    public function getEnrollmentsByStudent(int $studentId): Collection
    {
        return $this->model->where('student_id', $studentId)->get();
    }

    /**
     * Get enrollments by class
     */
    public function getEnrollmentsByClass(int $classId): Collection
    {
        return $this->model->where('class_id', $classId)->get();
    }

    /**
     * Get enrollments by academic year
     */
    public function getEnrollmentsByAcademicYear(int $academicYearId): Collection
    {
        return $this->model->where('academic_year_id', $academicYearId)->get();
    }

    /**
     * Get enrollments by school
     */
    public function getEnrollmentsBySchool(int $schoolId): Collection
    {
        return $this->model->where('school_id', $schoolId)->get();
    }

    /**
     * Get active enrollments
     */
    public function getActiveEnrollments(): Collection
    {
        return $this->model->active()->get();
    }

    /**
     * Get enrollments by status
     */
    public function getEnrollmentsByStatus(string $status): Collection
    {
        return $this->model->byStatus($status)->get();
    }

    /**
     * Get enrollments by date range
     */
    public function getEnrollmentsByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->model->whereBetween('enrollment_date', [$startDate, $endDate])->get();
    }

    /**
     * Get enrollments for today
     */
    public function getTodayEnrollments(): Collection
    {
        $today = Carbon::today()->toDateString();
        return $this->model->where('enrollment_date', $today)->get();
    }

    /**
     * Get enrollments for this week
     */
    public function getThisWeekEnrollments(): Collection
    {
        $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
        $endOfWeek = Carbon::now()->endOfWeek()->toDateString();
        return $this->model->whereBetween('enrollment_date', [$startOfWeek, $endOfWeek])->get();
    }

    /**
     * Get enrollments for this month
     */
    public function getThisMonthEnrollments(): Collection
    {
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
        return $this->model->whereBetween('enrollment_date', [$startOfMonth, $endOfMonth])->get();
    }

    /**
     * Get class enrollment count
     */
    public function getClassEnrollmentCount(int $classId): int
    {
        return $this->model->where('class_id', $classId)
                          ->where('status', StudentEnrollment::STATUS_ACTIVE)
                          ->count();
    }

    /**
     * Get school enrollment count
     */
    public function getSchoolEnrollmentCount(int $schoolId): int
    {
        return $this->model->where('school_id', $schoolId)
                          ->where('status', StudentEnrollment::STATUS_ACTIVE)
                          ->count();
    }

    /**
     * Get academic year enrollment count
     */
    public function getAcademicYearEnrollmentCount(int $academicYearId): int
    {
        return $this->model->where('academic_year_id', $academicYearId)
                          ->where('status', StudentEnrollment::STATUS_ACTIVE)
                          ->count();
    }

    /**
     * Get enrollment statistics
     */
    public function getEnrollmentStatistics(int $schoolId = null): array
    {
        $query = $this->model->newQuery();
        
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        $totalEnrollments = $query->count();
        $activeEnrollments = $query->where('status', StudentEnrollment::STATUS_ACTIVE)->count();
        $graduatedEnrollments = $query->where('status', StudentEnrollment::STATUS_GRADUATED)->count();
        $transferredEnrollments = $query->where('status', StudentEnrollment::STATUS_TRANSFERRED)->count();
        $suspendedEnrollments = $query->where('status', StudentEnrollment::STATUS_SUSPENDED)->count();

        return [
            'total_enrollments' => $totalEnrollments,
            'active_enrollments' => $activeEnrollments,
            'graduated_enrollments' => $graduatedEnrollments,
            'transferred_enrollments' => $transferredEnrollments,
            'suspended_enrollments' => $suspendedEnrollments,
        ];
    }

    /**
     * Get enrollments by faculty
     */
    public function getEnrollmentsByFaculty(int $facultyId): Collection
    {
        return $this->model->whereHas('classRoom', function($query) use ($facultyId) {
            $query->whereHas('course', function($q) use ($facultyId) {
                $q->whereHas('department', function($d) use ($facultyId) {
                    $d->where('faculty_id', $facultyId);
                });
            });
        })->get();
    }

    /**
     * Get enrollments by department
     */
    public function getEnrollmentsByDepartment(int $departmentId): Collection
    {
        return $this->model->whereHas('classRoom', function($query) use ($departmentId) {
            $query->whereHas('course', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        })->get();
    }

    /**
     * Get enrollments by course
     */
    public function getEnrollmentsByCourse(int $courseId): Collection
    {
        return $this->model->whereHas('classRoom', function($query) use ($courseId) {
            $query->where('course_id', $courseId);
        })->get();
    }

    /**
     * Get enrollments with relations
     */
    public function getEnrollmentsWithRelations(): Collection
    {
        return $this->model->with([
            'student',
            'classRoom',
            'academicYear',
            'enrolledBy',
            'school'
        ])->get();
    }

    /**
     * Find enrollment by ID with relations
     */
    public function findWithRelations(int $id)
    {
        return $this->model->with([
            'student',
            'classRoom',
            'academicYear',
            'enrolledBy',
            'school'
        ])->find($id);
    }

    /**
     * Check if student is enrolled in class
     */
    public function isStudentEnrolled(int $studentId, int $classId, int $academicYearId): bool
    {
        return $this->model->where('student_id', $studentId)
                          ->where('class_id', $classId)
                          ->where('academic_year_id', $academicYearId)
                          ->where('status', StudentEnrollment::STATUS_ACTIVE)
                          ->exists();
    }

    /**
     * Get student's current enrollments
     */
    public function getStudentCurrentEnrollments(int $studentId): Collection
    {
        return $this->model->where('student_id', $studentId)
                          ->where('status', StudentEnrollment::STATUS_ACTIVE)
                          ->get();
    }

    /**
     * Get enrollment history for student
     */
    public function getStudentEnrollmentHistory(int $studentId): Collection
    {
        return $this->model->where('student_id', $studentId)
                          ->orderBy('enrollment_date', 'desc')
                          ->get();
    }
} 