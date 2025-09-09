<?php

namespace App\Repositories\Teachers;

use App\Models\Teachers\TeacherAssignment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TeacherAssignmentRepository implements TeacherAssignmentRepositoryInterface
{
    /**
     * Get all assignments with filters
     * @param array $filters The filters for the assignments
     * @return Collection The assignments
     */
    public function getAllAssignments(array $filters = []): Collection
    {
        $query = TeacherAssignment::with(['teacher', 'class', 'subject']);
        //->with(['teacher', 'class', 'subject']);

        if (isset($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        if (isset($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (isset($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (isset($filters['is_primary'])) {
            $query->where('is_primary', $filters['is_primary']);
        }

        if (isset($filters['assigned_by'])) {
            // Check if assigned_by is an email or ID
            if (filter_var($filters['assigned_by'], FILTER_VALIDATE_EMAIL)) {
                // If it's an email, find the user ID first
                $userId = \App\Models\User::where('email', $filters['assigned_by'])->value('id');
                if ($userId) {
                    $query->where('assigned_by', $userId);
                } else {
                    // If user not found, return empty result
                    $query->where('assigned_by', -1);
                }
            } else {
                // If it's an ID, use it directly
                $query->where('assigned_by', $filters['assigned_by']);
            }
        }

        // Handle assigned_by_email filter (alternative to assigned_by)
        if (isset($filters['assigned_by_email'])) {
            $userId = \App\Models\User::where('email', $filters['assigned_by_email'])->value('id');
            if ($userId) {
                $query->where('assigned_by', $userId);
            } else {
                // If user not found, return empty result
                $query->where('assigned_by', -1);
            }
        }

        if (isset($filters['school_id'])) {
            $query->where('school_id', $filters['school_id']);
        }

        // Filter by assignment date range
        if (isset($filters['assignment_date_from'])) {
            $query->where('assignment_date', '>=', $filters['assignment_date_from']);
        }

        if (isset($filters['assignment_date_to'])) {
            $query->where('assignment_date', '<=', $filters['assignment_date_to']);
        }

        // Filter by end date range
        if (isset($filters['end_date_from'])) {
            $query->where('end_date', '>=', $filters['end_date_from']);
        }

        if (isset($filters['end_date_to'])) {
            $query->where('end_date', '<=', $filters['end_date_to']);
        }

        // Filter by weekly hours range
        if (isset($filters['weekly_hours_min'])) {
            $query->where('weekly_hours', '>=', $filters['weekly_hours_min']);
        }

        if (isset($filters['weekly_hours_max'])) {
            $query->where('weekly_hours', '<=', $filters['weekly_hours_max']);
        }

        // Filter by active status if specified, otherwise show all
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // Search in notes
        if (isset($filters['notes_search'])) {
            $query->where('notes', 'like', '%' . $filters['notes_search'] . '%');
        }

        $assignments = $query->orderBy('created_at', 'desc')->get();    

        return $assignments;

    }


    /**
     * Get an assignment by its ID
     * @param int $id The ID of the assignment
     * @return TeacherAssignment|null The assignment if found, null otherwise
     */
    public function getAssignmentById(int $id): ?TeacherAssignment
    {
        return TeacherAssignment::with(['teacher', 'class', 'subject'])->find($id);
    }

    /**
     * Create a new assignment
     * @param array $data The data for the assignment
     * @return TeacherAssignment The created assignment
     */
    public function createAssignment(array $data): TeacherAssignment
    {
        $assignment = TeacherAssignment::create($data);
        return $assignment->load(['teacher', 'class', 'subject']);
    }

    /**
     * Update an existing assignment
     * @param TeacherAssignment $assignment The assignment to update
     * @param array $data The data to update
     * @return bool True if the update was successful, false otherwise
     */
    public function updateAssignment(TeacherAssignment $assignment, array $data): bool
    {
        return $assignment->update($data);
    }

    /**
     * Delete an assignment
     * @param TeacherAssignment $assignment The assignment to delete
     * @return bool True if the deletion was successful, false otherwise
     */
    public function deleteAssignment(TeacherAssignment $assignment): bool
    {
        return $assignment->delete();
    }

    /**
     * Get all assignments for a specific teacher
     * @param int $teacherId The ID of the teacher
     * @return Collection The assignments for the teacher
     */
    public function getAssignmentsByTeacher(int $teacherId): Collection
    {
        return TeacherAssignment::with(['class', 'subject'])
            ->where('teacher_id', $teacherId)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get all assignments for a specific class
     * @param int $classId The ID of the class
     * @return Collection The assignments for the class
     */
    public function getAssignmentsByClass(int $classId): Collection
    {
        return TeacherAssignment::with(['teacher', 'subject'])
            ->where('class_id', $classId)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get all assignments for a specific subject
     * @param int $subjectId The ID of the subject
     * @return Collection The assignments for the subject
     */
    public function getAssignmentsBySubject(int $subjectId): Collection
    {
        return TeacherAssignment::with(['teacher', 'class'])
            ->where('subject_id', $subjectId)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get all assignments for a specific academic year
     * @param int $academicYearId The academic year ID
     * @return Collection The assignments for the academic year
     */
    public function getAssignmentsByAcademicYear(int $academicYearId): Collection
    {
        return TeacherAssignment::with(['teacher', 'class', 'subject'])
            ->where('academic_year_id', $academicYearId)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get all assignments for a specific term
     * @param string $term The term
     * @return Collection The assignments for the term
     */
    public function getAssignmentsByTerm(string $term): Collection
    {
        return TeacherAssignment::with(['teacher', 'class', 'subject'])
            ->where('term', $term)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get all active assignments
     * @return Collection The active assignments
     */
    public function getActiveAssignments(): Collection
    {
        return TeacherAssignment::with(['teacher', 'class', 'subject'])
            ->where('is_active', true)
            ->where('assignment_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get();
    }

    /**
     * Get all assignments within a date range
     * @param string $startDate The start date
     * @param string $endDate The end date
     * @return Collection The assignments within the date range
     */
    public function getAssignmentsByDateRange(string $startDate, string $endDate): Collection
    {
        return TeacherAssignment::with(['teacher', 'class', 'subject'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('assignment_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('assignment_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->where('is_active', true)
            ->orderBy('assignment_date')
            ->get();
    }

    /**
     * Check for assignment conflicts
     * @param int $teacherId The ID of the teacher
     * @param int $classId The ID of the class
     * @param int $subjectId The ID of the subject
     * @param string $academicYear The academic year
     * @param string $term The term
     * @param string $startDate The start date
     * @param string $endDate The end date
     * @param int|null $excludeId The ID of the assignment to exclude from the check
     * @return array The conflicts found
     */
    public function checkAssignmentConflicts(
        int $teacherId, 
        int $classId, 
        int $subjectId, 
        int $academicYearId, 
        string $role, 
        string $assignmentDate, 
        string $endDate, 
        ?int $excludeId = null
    ): array {
        $query = TeacherAssignment::where('teacher_id', $teacherId)
            ->where('academic_year_id', $academicYearId)
            ->where('role', $role)
            ->where('is_active', true)
            ->where(function ($q) use ($assignmentDate, $endDate) {
                $q->whereBetween('assignment_date', [$assignmentDate, $endDate])
                    ->orWhereBetween('end_date', [$assignmentDate, $endDate])
                    ->orWhere(function ($subQ) use ($assignmentDate, $endDate) {
                        $subQ->where('assignment_date', '<=', $assignmentDate)
                            ->where('end_date', '>=', $endDate);
                    });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $conflicts = $query->with(['class', 'subject'])->get();

        $conflictDetails = [];
        foreach ($conflicts as $conflict) {
            $conflictDetails[] = [
                'id' => $conflict->id,
                'class' => $conflict->class->name ?? 'Unknown Class',
                'subject' => $conflict->subject->name ?? 'Unknown Subject',
                'start_date' => $conflict->start_date,
                'end_date' => $conflict->end_date,
                'hours_per_week' => $conflict->hours_per_week,
                'conflict_type' => 'Schedule Overlap'
            ];
        }

        return $conflictDetails;
    }

    /**
     * Bulk import assignments
     * @param array $assignments The assignments to import
     * @return array The results of the import
     */
    public function bulkImportAssignments(array $assignments): array
    {
        $results = [
            'successful' => 0,
            'failed' => 0,
            'errors' => []
        ];

        DB::beginTransaction();
        try {
            foreach ($assignments as $index => $assignmentData) {
                try {
                    // Check for conflicts before creating
                    $conflicts = $this->checkAssignmentConflicts(
                        $assignmentData['teacher_id'],
                        $assignmentData['class_id'],
                        $assignmentData['subject_id'],
                        $assignmentData['academic_year'],
                        $assignmentData['term'],
                        $assignmentData['start_date'],
                        $assignmentData['end_date']
                    );

                    if (!empty($conflicts)) {
                        $results['failed']++;
                        $results['errors'][] = [
                            'index' => $index,
                            'message' => 'Assignment conflicts detected',
                            'conflicts' => $conflicts
                        ];
                        continue;
                    }

                    $this->createAssignment($assignmentData);
                    $results['successful']++;
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'index' => $index,
                        'message' => $e->getMessage()
                    ];
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }


    /**
     * Get assignment statistics
     * @return array The statistics
     */
    public function getAssignmentStatistics(): array
    {
        $totalAssignments = TeacherAssignment::count();
        $activeAssignments = TeacherAssignment::where('is_active', true)->count();
        $assignmentsThisYear = TeacherAssignment::where('academic_year', date('Y'))->count();

        $assignmentsByTerm = TeacherAssignment::select('term', DB::raw('count(*) as count'))
            ->groupBy('term')
            ->get()
            ->pluck('count', 'term')
            ->toArray();

        $assignmentsByFaculty = TeacherAssignment::join('teachers', 'teacher_assignments.teacher_id', '=', 'teachers.id')
            ->join('departments', 'teachers.department_id', '=', 'departments.id')
            ->join('faculties', 'departments.faculty_id', '=', 'faculties.id')
            ->select('faculties.name', DB::raw('count(*) as count'))
            ->groupBy('faculties.id', 'faculties.name')
            ->get()
            ->pluck('count', 'name')
            ->toArray();

        return [
            'total_assignments' => $totalAssignments,
            'active_assignments' => $activeAssignments,
            'assignments_this_year' => $assignmentsThisYear,
            'assignments_by_term' => $assignmentsByTerm,
            'assignments_by_faculty' => $assignmentsByFaculty,
            'average_hours_per_week' => TeacherAssignment::where('is_active', true)->avg('hours_per_week') ?? 0
        ];
    }

    /**
     * Generate an assignment report
     * @param array $filters The filters for the report
     * @return array The report
     */
    public function generateAssignmentReport(array $filters): array
    {
        $query = TeacherAssignment::with(['teacher.department.faculty', 'class', 'subject']);

        if (isset($filters['academic_year'])) {
            $query->where('academic_year', $filters['academic_year']);
        }

        if (isset($filters['term'])) {
            $query->where('term', $filters['term']);
        }

        if (isset($filters['department_id'])) {
            $query->whereHas('teacher.department', function ($q) use ($filters) {
                $q->where('id', $filters['department_id']);
            });
        }

        if (isset($filters['faculty_id'])) {
            $query->whereHas('teacher.department.faculty', function ($q) use ($filters) {
                $q->where('id', $filters['faculty_id']);
            });
        }

        $assignments = $query->get();

        $report = [
            'summary' => [
                'total_assignments' => $assignments->count(),
                'unique_teachers' => $assignments->pluck('teacher_id')->unique()->count(),
                'unique_classes' => $assignments->pluck('class_id')->unique()->count(),
                'unique_subjects' => $assignments->pluck('subject_id')->unique()->count(),
                'total_hours' => $assignments->sum('hours_per_week')
            ],
            'assignments_by_faculty' => $assignments->groupBy('teacher.department.faculty.name')
                ->map(function ($facultyAssignments) {
                    return [
                        'count' => $facultyAssignments->count(),
                        'teachers' => $facultyAssignments->pluck('teacher_id')->unique()->count(),
                        'total_hours' => $facultyAssignments->sum('hours_per_week')
                    ];
                }),
            'assignments_by_department' => $assignments->groupBy('teacher.department.name')
                ->map(function ($deptAssignments) {
                    return [
                        'count' => $deptAssignments->count(),
                        'teachers' => $deptAssignments->pluck('teacher_id')->unique()->count(),
                        'total_hours' => $deptAssignments->sum('hours_per_week')
                    ];
                }),
            'assignments_by_term' => $assignments->groupBy('term')
                ->map(function ($termAssignments) {
                    return [
                        'count' => $termAssignments->count(),
                        'teachers' => $termAssignments->pluck('teacher_id')->unique()->count(),
                        'total_hours' => $termAssignments->sum('hours_per_week')
                    ];
                }),
            'teacher_workload' => $assignments->groupBy('teacher.name')
                ->map(function ($teacherAssignments) {
                    return [
                        'assignments' => $teacherAssignments->count(),
                        'classes' => $teacherAssignments->pluck('class_id')->unique()->count(),
                        'subjects' => $teacherAssignments->pluck('subject_id')->unique()->count(),
                        'total_hours' => $teacherAssignments->sum('hours_per_week')
                    ];
                })
        ];

        return $report;
    }
} 