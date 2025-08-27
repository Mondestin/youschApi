<?php

namespace App\Repositories\Teachers;

use App\Models\Teachers\TeacherTimetable;
use App\Repositories\BaseRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class TeacherTimetableRepository extends BaseRepository implements TeacherTimetableRepositoryInterface
{
    public function __construct(TeacherTimetable $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated timetables with filters
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPaginatedTimetables(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['teacher', 'class', 'subject', 'academicYear', 'term']);

        // Apply filters
        if (isset($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        if (isset($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (isset($filters['day_of_week'])) {
            $query->where('day_of_week', $filters['day_of_week']);
        }

        if (isset($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        if (isset($filters['term_id'])) {
            $query->where('term_id', $filters['term_id']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->orderBy('day_of_week')
            ->orderBy('start_time')
            ->paginate($perPage);
    }

    /**
     * Get all timetables with filters (without pagination)
     *
     * @param array $filters
     * @return Collection
     */
    public function getAllTimetables(array $filters): Collection
    {
        $query = $this->model->with(['teacher', 'class', 'subject', 'academicYear', 'term']);

        // Apply filters
        if (isset($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        if (isset($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (isset($filters['day_of_week'])) {
            $query->where('day_of_week', $filters['day_of_week']);
        }

        if (isset($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        if (isset($filters['term_id'])) {
            $query->where('term_id', $filters['term_id']);
        }

        return $query->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get timetable by ID
     *
     * @param int $id
     * @return TeacherTimetable|null
     */
    public function getTimetableById(int $id): ?TeacherTimetable
    {
        return $this->model->with(['teacher', 'class', 'subject', 'academicYear', 'term'])->find($id);
    }

    /**
     * Create a new timetable entry
     *
     * @param array $data
     * @return TeacherTimetable
     */
    public function createTimetable(array $data): TeacherTimetable
    {
        $data['is_active'] = $data['is_active'] ?? true;
        
        return $this->model->create($data);
    }

    /**
     * Update timetable entry
     *
     * @param TeacherTimetable $timetable
     * @param array $data
     * @return bool
     */
    public function updateTimetable(TeacherTimetable $timetable, array $data): bool
    {
        return $timetable->update($data);
    }

    /**
     * Delete timetable entry
     *
     * @param TeacherTimetable $timetable
     * @return bool
     */
    public function deleteTimetable(TeacherTimetable $timetable): bool
    {
        return $timetable->delete();
    }

    /**
     * Get timetables by teacher
     *
     * @param int $teacherId
     * @return Collection
     */
    public function getTimetablesByTeacher(int $teacherId): Collection
    {
        return $this->model->with(['teacher', 'class', 'subject', 'academicYear', 'term'])
            ->where('teacher_id', $teacherId)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get timetables by class
     *
     * @param int $classId
     * @return Collection
     */
    public function getTimetablesByClass(int $classId): Collection
    {
        return $this->model->with(['teacher', 'class', 'subject', 'academicYear', 'term'])
            ->where('class_id', $classId)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get timetables by subject
     *
     * @param int $subjectId
     * @return Collection
     */
    public function getTimetablesBySubject(int $subjectId): Collection
    {
        return $this->model->with(['teacher', 'class', 'subject', 'academicYear', 'term'])
            ->where('subject_id', $subjectId)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get timetables by day of week
     *
     * @param string $dayOfWeek
     * @return Collection
     */
    public function getTimetablesByDay(string $dayOfWeek): Collection
    {
        return $this->model->with(['teacher', 'class', 'subject', 'academicYear', 'term'])
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get timetables by academic year and term
     *
     * @param int $academicYearId
     * @param int $termId
     * @return Collection
     */
    public function getTimetablesByAcademicYearAndTerm(int $academicYearId, int $termId): Collection
    {
        return $this->model->with(['teacher', 'class', 'subject', 'academicYear', 'term'])
            ->where('academic_year_id', $academicYearId)
            ->where('term_id', $termId)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get teacher weekly schedule
     *
     * @param int $teacherId
     * @param int $academicYearId
     * @param int $termId
     * @return array
     */
    public function getTeacherWeeklySchedule(int $teacherId, int $academicYearId, int $termId): array
    {
        $timetables = $this->model->with(['teacher', 'class', 'subject', 'academicYear', 'term'])
            ->where('teacher_id', $teacherId)
            ->where('academic_year_id', $academicYearId)
            ->where('term_id', $termId)
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $schedule = [
            'monday' => [],
            'tuesday' => [],
            'wednesday' => [],
            'thursday' => [],
            'friday' => [],
            'saturday' => [],
            'sunday' => []
        ];

        foreach ($timetables as $timetable) {
            $schedule[$timetable->day_of_week][] = [
                'id' => $timetable->id,
                'start_time' => $timetable->start_time,
                'end_time' => $timetable->end_time,
                'class' => $timetable->class,
                'subject' => $timetable->subject,
                'room_number' => $timetable->room_number,
                'notes' => $timetable->notes
            ];
        }

        return $schedule;
    }

    /**
     * Get class weekly schedule
     *
     * @param int $classId
     * @param int $academicYearId
     * @param int $termId
     * @return array
     */
    public function getClassWeeklySchedule(int $classId, int $academicYearId, int $termId): array
    {
        $timetables = $this->model->with(['teacher', 'class', 'subject', 'academicYear', 'term'])
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('term_id', $termId)
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $schedule = [
            'monday' => [],
            'tuesday' => [],
            'wednesday' => [],
            'thursday' => [],
            'friday' => [],
            'saturday' => [],
            'sunday' => []
        ];

        foreach ($timetables as $timetable) {
            $schedule[$timetable->day_of_week][] = [
                'id' => $timetable->id,
                'start_time' => $timetable->start_time,
                'end_time' => $timetable->end_time,
                'teacher' => $timetable->teacher,
                'subject' => $timetable->subject,
                'room_number' => $timetable->room_number,
                'notes' => $timetable->notes
            ];
        }

        return $schedule;
    }

    /**
     * Check for time conflicts
     *
     * @param int $teacherId
     * @param string $dayOfWeek
     * @param string $startTime
     * @param string $endTime
     * @param int $academicYearId
     * @param int $termId
     * @param int|null $excludeId
     * @return Collection
     */
    public function checkTimeConflicts(
        int $teacherId,
        string $dayOfWeek,
        string $startTime,
        string $endTime,
        int $academicYearId,
        int $termId,
        ?int $excludeId = null
    ): Collection {
        $query = $this->model->with(['teacher', 'class', 'subject'])
            ->where('teacher_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->where('academic_year_id', $academicYearId)
            ->where('term_id', $termId)
            ->where('is_active', true);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->where(function ($q) use ($startTime, $endTime) {
            $q->whereBetween('start_time', [$startTime, $endTime])
                ->orWhereBetween('end_time', [$startTime, $endTime])
                ->orWhere(function ($subQ) use ($startTime, $endTime) {
                    $subQ->where('start_time', '<=', $startTime)
                        ->where('end_time', '>=', $endTime);
                });
        })->get();
    }

    /**
     * Get timetable statistics
     *
     * @return array
     */
    public function getTimetableStatistics(): array
    {
        $totalEntries = $this->model->count();
        $activeEntries = $this->model->where('is_active', true)->count();
        $inactiveEntries = $this->model->where('is_active', false)->count();

        $entriesByDay = $this->model->selectRaw('day_of_week, COUNT(*) as count')
            ->groupBy('day_of_week')
            ->pluck('count', 'day_of_week')
            ->toArray();

        $entriesByAcademicYear = $this->model->selectRaw('academic_year_id, COUNT(*) as count')
            ->groupBy('academic_year_id')
            ->pluck('count', 'academic_year_id')
            ->toArray();

        $entriesByTerm = $this->model->selectRaw('term_id, COUNT(*) as count')
            ->groupBy('term_id')
            ->pluck('count', 'term_id')
            ->toArray();

        return [
            'total_entries' => $totalEntries,
            'active_entries' => $activeEntries,
            'inactive_entries' => $inactiveEntries,
            'entries_by_day' => $entriesByDay,
            'entries_by_academic_year' => $entriesByAcademicYear,
            'entries_by_term' => $entriesByTerm
        ];
    }
} 