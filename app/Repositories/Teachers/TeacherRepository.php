<?php

namespace App\Repositories\Teachers;

use App\Models\Teachers\Teacher;
use App\Repositories\BaseRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TeacherRepository extends BaseRepository implements TeacherRepositoryInterface
{
    public function __construct(Teacher $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated teachers with filters.
     */
    public function getPaginatedTeachers(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['school', 'campus']);

        if (isset($filters['school_id'])) {
            $query->where('school_id', $filters['school_id']);
        }

        if (isset($filters['campus_id'])) {
            $query->where('campus_id', $filters['campus_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (isset($filters['hire_date_from'])) {
            $query->where('hire_date', '>=', $filters['hire_date_from']);
        }

        if (isset($filters['hire_date_to'])) {
            $query->where('hire_date', '<=', $filters['hire_date_to']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')
                    ->paginate(config('teachers.pagination.default_per_page', 15));
    }

    /**
     * Get teacher by ID with relationships.
     */
    public function getTeacherById(int $id, array $with = []): ?Teacher
    {
        return $this->model->with($with)->find($id);
    }

    /**
     * Create a new teacher.
     */
    public function createTeacher(array $data): Teacher
    {
        return $this->model->create($data);
    }

    /**
     * Update teacher.
     */
    public function updateTeacher(Teacher $teacher, array $data): bool
    {
        return $teacher->update($data);
    }

    /**
     * Delete teacher.
     */
    public function deleteTeacher(Teacher $teacher): bool
    {
        return $teacher->delete();
    }

    /**
     * Get teachers by campus.
     */
    public function getTeachersByCampus(int $campusId): Collection
    {
        return $this->model->byCampus($campusId)->with(['school'])->get();
    }

    /**
     * Get teachers by school.
     */
    public function getTeachersBySchool(int $schoolId): Collection
    {
        return $this->model->bySchool($schoolId)->with(['campus'])->get();
    }

    /**
     * Get teachers by status.
     */
    public function getTeachersByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }

    /**
     * Get teachers by hire date range.
     */
    public function getTeachersByHireDateRange(string $startDate, string $endDate): Collection
    {
        return $this->model->byHireDateRange($startDate, $endDate)->get();
    }

    /**
     * Get teacher statistics.
     */
    public function getTeacherStatistics(array $filters): array
    {
        $query = $this->model->query();

        if (isset($filters['school_id'])) {
            $query->where('school_id', $filters['school_id']);
        }

        if (isset($filters['campus_id'])) {
            $query->where('campus_id', $filters['campus_id']);
        }

        $totalTeachers = $query->count();
        $activeTeachers = $query->where('status', Teacher::STATUS_ACTIVE)->count();
        $onLeaveTeachers = $query->where('status', Teacher::STATUS_ON_LEAVE)->count();
        $resignedTeachers = $query->where('status', Teacher::STATUS_RESIGNED)->count();
        $suspendedTeachers = $query->where('status', Teacher::STATUS_SUSPENDED)->count();

        $recentHires = $query->where('hire_date', '>=', now()->subDays(30))->count();

        return [
            'total_teachers' => $totalTeachers,
            'active_teachers' => $activeTeachers,
            'on_leave_teachers' => $onLeaveTeachers,
            'resigned_teachers' => $resignedTeachers,
            'suspended_teachers' => $suspendedTeachers,
            'recent_hires' => $recentHires,
        ];
    }

    /**
     * Get teacher workload.
     */
    public function getTeacherWorkload(int $teacherId, int $termId): array
    {
        $teacher = $this->model->with(['assignments' => function ($query) use ($termId) {
            $query->where('term_id', $termId)
                  ->with(['classRoom', 'subject', 'lab']);
        }])->find($teacherId);

        if (!$teacher) {
            return [];
        }

        $assignments = $teacher->assignments;
        $totalClasses = $assignments->count();
        $totalSubjects = $assignments->pluck('subject_id')->unique()->count();
        $totalLabs = $assignments->whereNotNull('lab_id')->count();

        return [
            'teacher' => $teacher->only(['id', 'first_name', 'last_name', 'email']),
            'assignments' => $assignments,
            'total_classes' => $totalClasses,
            'total_subjects' => $totalSubjects,
            'total_labs' => $totalLabs,
        ];
    }

    /**
     * Get teacher schedule.
     */
    public function getTeacherSchedule(int $teacherId, string $date): Collection
    {
        return $this->model->find($teacherId)
                          ->timetables()
                          ->where('date', $date)
                          ->with(['classRoom', 'subject', 'lab'])
                          ->orderBy('start_time')
                          ->get();
    }

    /**
     * Search teachers.
     */
    public function searchTeachers(string $query, array $filters = []): LengthAwarePaginator
    {
        $searchQuery = $this->model->where(function ($q) use ($query) {
            $q->where('first_name', 'like', "%{$query}%")
              ->orWhere('last_name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%")
              ->orWhere('phone', 'like', "%{$query}%");
        });

        // Apply additional filters
        foreach ($filters as $key => $value) {
            if ($value !== null) {
                $searchQuery->where($key, $value);
            }
        }

        return $searchQuery->with(['school', 'campus'])
                          ->orderBy('created_at', 'desc')
                          ->paginate(config('teachers.pagination.default_per_page', 15));
    }

    /**
     * Get active teachers count.
     */
    public function getActiveTeachersCount(): int
    {
        return $this->model->active()->count();
    }

    /**
     * Get teachers on leave count.
     */
    public function getTeachersOnLeaveCount(): int
    {
        return $this->model->onLeave()->count();
    }

    /**
     * Get recent hires.
     */
    public function getRecentHires(int $days = 30): Collection
    {
        return $this->model->where('hire_date', '>=', now()->subDays($days))
                          ->orderBy('hire_date', 'desc')
                          ->get();
    }
} 