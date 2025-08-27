<?php

namespace App\Repositories\Teachers;

use App\Models\Teachers\TeacherAssignment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TeacherAssignmentRepository implements TeacherAssignmentRepositoryInterface
{
    public function getPaginatedAssignments(array $filters): LengthAwarePaginator
    {
        $query = TeacherAssignment::with(['teacher', 'class', 'subject']);

        if (isset($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        if (isset($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (isset($filters['academic_year'])) {
            $query->where('academic_year', $filters['academic_year']);
        }

        if (isset($filters['term'])) {
            $query->where('term', $filters['term']);
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function getAssignmentById(int $id): ?TeacherAssignment
    {
        return TeacherAssignment::with(['teacher', 'class', 'subject'])->find($id);
    }

    public function createAssignment(array $data): TeacherAssignment
    {
        return TeacherAssignment::create($data);
    }

    public function updateAssignment(TeacherAssignment $assignment, array $data): bool
    {
        return $assignment->update($data);
    }

    public function deleteAssignment(TeacherAssignment $assignment): bool
    {
        return $assignment->delete();
    }

    public function getAssignmentsByTeacher(int $teacherId): Collection
    {
        return TeacherAssignment::with(['class', 'subject'])
            ->where('teacher_id', $teacherId)
            ->where('is_active', true)
            ->orderBy('academic_year', 'desc')
            ->orderBy('term')
            ->get();
    }

    public function getAssignmentsByClass(int $classId): Collection
    {
        return TeacherAssignment::with(['teacher', 'subject'])
            ->where('class_id', $classId)
            ->where('is_active', true)
            ->orderBy('academic_year', 'desc')
            ->orderBy('term')
            ->get();
    }

    public function getAssignmentsBySubject(int $subjectId): Collection
    {
        return TeacherAssignment::with(['teacher', 'class'])
            ->where('subject_id', $subjectId)
            ->where('is_active', true)
            ->orderBy('academic_year', 'desc')
            ->orderBy('term')
            ->get();
    }

    public function getAssignmentsByAcademicYear(string $academicYear): Collection
    {
        return TeacherAssignment::with(['teacher', 'class', 'subject'])
            ->where('academic_year', $academicYear)
            ->where('is_active', true)
            ->orderBy('term')
            ->get();
    }

    public function getAssignmentsByTerm(string $term): Collection
    {
        return TeacherAssignment::with(['teacher', 'class', 'subject'])
            ->where('term', $term)
            ->where('is_active', true)
            ->orderBy('academic_year', 'desc')
            ->get();
    }

    public function getActiveAssignments(): Collection
    {
        return TeacherAssignment::with(['teacher', 'class', 'subject'])
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->orderBy('academic_year', 'desc')
            ->orderBy('term')
            ->get();
    }

    public function getAssignmentsByDateRange(string $startDate, string $endDate): Collection
    {
        return TeacherAssignment::with(['teacher', 'class', 'subject'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->where('is_active', true)
            ->orderBy('start_date')
            ->get();
    }

    public function checkAssignmentConflicts(
        int $teacherId, 
        int $classId, 
        int $subjectId, 
        string $academicYear, 
        string $term, 
        string $startDate, 
        string $endDate, 
        ?int $excludeId = null
    ): array {
        $query = TeacherAssignment::where('teacher_id', $teacherId)
            ->where('academic_year', $academicYear)
            ->where('term', $term)
            ->where('is_active', true)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($subQ) use ($startDate, $endDate) {
                        $subQ->where('start_date', '<=', $startDate)
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
     * Get all assignments with filters (without pagination)
     */
    public function getAllAssignments(array $filters): Collection
    {
        $query = TeacherAssignment::with(['teacher', 'class', 'subject']);

        if (isset($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        if (isset($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (isset($filters['academic_year'])) {
            $query->where('academic_year', $filters['academic_year']);
        }

        if (isset($filters['term'])) {
            $query->where('term', $filters['term']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

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