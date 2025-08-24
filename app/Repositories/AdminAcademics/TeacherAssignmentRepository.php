<?php

namespace App\Repositories\AdminAcademics;

use App\Repositories\BaseRepository;
use App\Models\AdminAcademics\TeacherAssignment;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class TeacherAssignmentRepository extends BaseRepository
{
    /**
     * Constructor
     */
    public function __construct(TeacherAssignment $model)
    {
        parent::__construct($model);
    }

    /**
     * Get assignments by teacher
     */
    public function getAssignmentsByTeacher(int $teacherId): Collection
    {
        return $this->model->where('teacher_id', $teacherId)->get();
    }

    /**
     * Get assignments by class
     */
    public function getAssignmentsByClass(int $classId): Collection
    {
        return $this->model->where('class_id', $classId)->get();
    }

    /**
     * Get assignments by subject
     */
    public function getAssignmentsBySubject(int $subjectId): Collection
    {
        return $this->model->where('subject_id', $subjectId)->get();
    }

    /**
     * Get assignments by academic year
     */
    public function getAssignmentsByAcademicYear(int $academicYearId): Collection
    {
        return $this->model->where('academic_year_id', $academicYearId)->get();
    }

    /**
     * Get assignments by school
     */
    public function getAssignmentsBySchool(int $schoolId): Collection
    {
        return $this->model->where('school_id', $schoolId)->get();
    }

    /**
     * Get active assignments
     */
    public function getActiveAssignments(): Collection
    {
        return $this->model->active()->get();
    }

    /**
     * Get assignments by role
     */
    public function getAssignmentsByRole(string $role): Collection
    {
        return $this->model->byRole($role)->get();
    }

    /**
     * Get primary teacher assignments
     */
    public function getPrimaryAssignments(): Collection
    {
        return $this->model->primary()->get();
    }

    /**
     * Get assignments by date range
     */
    public function getAssignmentsByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->model->whereBetween('assignment_date', [$startDate, $endDate])->get();
    }

    /**
     * Get assignments for today
     */
    public function getTodayAssignments(): Collection
    {
        $today = Carbon::today()->toDateString();
        return $this->model->whereDate('assignment_date', $today)->get();
    }

    /**
     * Get assignments for this week
     */
    public function getThisWeekAssignments(): Collection
    {
        $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
        $endOfWeek = Carbon::now()->endOfWeek()->toDateString();
        return $this->model->whereBetween('assignment_date', [$startOfWeek, $endOfWeek])->get();
    }

    /**
     * Get assignments for this month
     */
    public function getThisMonthAssignments(): Collection
    {
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
        return $this->model->whereBetween('assignment_date', [$startOfMonth, $endOfMonth])->get();
    }

    /**
     * Get teacher's current assignments
     */
    public function getTeacherCurrentAssignments(int $teacherId): Collection
    {
        return $this->model->where('teacher_id', $teacherId)
                          ->where('is_active', true)
                          ->get();
    }

    /**
     * Get class teacher assignments
     */
    public function getClassTeacherAssignments(int $classId): Collection
    {
        return $this->model->where('class_id', $classId)
                          ->where('is_active', true)
                          ->get();
    }

    /**
     * Get subject teacher assignments
     */
    public function getSubjectTeacherAssignments(int $subjectId): Collection
    {
        return $this->model->where('subject_id', $subjectId)
                          ->where('is_active', true)
                          ->get();
    }

    /**
     * Get assignment statistics
     */
    public function getAssignmentStatistics(int $schoolId = null): array
    {
        $query = $this->model->newQuery();
        
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        $totalAssignments = $query->count();
        $activeAssignments = $query->where('is_active', true)->count();
        $primaryAssignments = $query->where('is_primary', true)->count();
        $teacherAssignments = $query->where('role', TeacherAssignment::ROLE_TEACHER)->count();
        $coordinatorAssignments = $query->where('role', TeacherAssignment::ROLE_COORDINATOR)->count();
        $assistantAssignments = $query->where('role', TeacherAssignment::ROLE_ASSISTANT)->count();
        $substituteAssignments = $query->where('role', TeacherAssignment::ROLE_SUBSTITUTE)->count();

        return [
            'total_assignments' => $totalAssignments,
            'active_assignments' => $activeAssignments,
            'primary_assignments' => $primaryAssignments,
            'teacher_assignments' => $teacherAssignments,
            'coordinator_assignments' => $coordinatorAssignments,
            'assistant_assignments' => $assistantAssignments,
            'substitute_assignments' => $substituteAssignments,
        ];
    }

    /**
     * Get assignments by faculty
     */
    public function getAssignmentsByFaculty(int $facultyId): Collection
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
     * Get assignments by department
     */
    public function getAssignmentsByDepartment(int $departmentId): Collection
    {
        return $this->model->whereHas('classRoom', function($query) use ($departmentId) {
            $query->whereHas('course', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        })->get();
    }

    /**
     * Get assignments by course
     */
    public function getAssignmentsByCourse(int $courseId): Collection
    {
        return $this->model->whereHas('classRoom', function($query) use ($courseId) {
            $query->where('course_id', $courseId);
        })->get();
    }

    /**
     * Get teacher workload
     */
    public function getTeacherWorkload(int $teacherId, int $academicYearId): array
    {
        $assignments = $this->model->where('teacher_id', $teacherId)
                                  ->where('academic_year_id', $academicYearId)
                                  ->where('is_active', true)
                                  ->get();

        $classes = $assignments->pluck('class_id')->unique()->count();
        $subjects = $assignments->pluck('subject_id')->unique()->count();
        $primaryAssignments = $assignments->where('is_primary', true)->count();

        return [
            'total_assignments' => $assignments->count(),
            'classes' => $classes,
            'subjects' => $subjects,
            'primary_assignments' => $primaryAssignments,
        ];
    }

    /**
     * Get assignments with relations
     */
    public function getAssignmentsWithRelations(): Collection
    {
        return $this->model->with([
            'teacher',
            'classRoom',
            'subject',
            'academicYear',
            'assignedBy',
            'school'
        ])->get();
    }

    /**
     * Find assignment by ID with relations
     */
    public function findWithRelations(int $id)
    {
        return $this->model->with([
            'teacher',
            'classRoom',
            'subject',
            'academicYear',
            'assignedBy',
            'school'
        ])->find($id);
    }

    /**
     * Check if teacher is assigned to class
     */
    public function isTeacherAssignedToClass(int $teacherId, int $classId, int $academicYearId): bool
    {
        return $this->model->where('teacher_id', $teacherId)
                          ->where('class_id', $classId)
                          ->where('academic_year_id', $academicYearId)
                          ->where('is_active', true)
                          ->exists();
    }

    /**
     * Check if teacher is assigned to subject
     */
    public function isTeacherAssignedToSubject(int $teacherId, int $subjectId, int $academicYearId): bool
    {
        return $this->model->where('teacher_id', $teacherId)
                          ->where('subject_id', $subjectId)
                          ->where('academic_year_id', $academicYearId)
                          ->where('is_active', true)
                          ->exists();
    }

    /**
     * Get substitute teachers for a class
     */
    public function getSubstituteTeachersForClass(int $classId, int $academicYearId): Collection
    {
        return $this->model->where('class_id', $classId)
                          ->where('academic_year_id', $academicYearId)
                          ->where('role', TeacherAssignment::ROLE_SUBSTITUTE)
                          ->where('is_active', true)
                          ->get();
    }
} 