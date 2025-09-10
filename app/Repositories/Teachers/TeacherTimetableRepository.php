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
        $query = $this->model->with(['teacher', 'classRoom', 'subject']);

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

        if (isset($filters['date'])) {
            $query->where('date', $filters['date']);
        }

        if (isset($filters['date_from'])) {
            $query->where('date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('date', '<=', $filters['date_to']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->orderBy('date')
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
        $query = $this->model->with(['teacher', 'classRoom', 'subject']);

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

        if (isset($filters['date'])) {
            $query->where('date', $filters['date']);
        }

        if (isset($filters['date_from'])) {
            $query->where('date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('date', '<=', $filters['date_to']);
        }

        return $query->orderBy('date')
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
        return $this->model->with(['teacher', 'classRoom', 'subject'])->find($id);
    }

    /**
     * Create a new timetable entry
     *
     * @param array $data
     * @return TeacherTimetable
     */
    public function createTimetable(array $data): TeacherTimetable
    {
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
        return $this->model->with(['teacher', 'classRoom', 'subject'])
            ->where('teacher_id', $teacherId)
            ->orderBy('date')
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
        return $this->model->with(['teacher', 'classRoom', 'subject'])
            ->where('class_id', $classId)
            ->orderBy('date')
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
        return $this->model->with(['teacher', 'classRoom', 'subject'])
            ->where('subject_id', $subjectId)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get timetables by specific date
     *
     * @param string $date
     * @return Collection
     */
    public function getTimetablesByDate(string $date): Collection
    {
        return $this->model->with(['teacher', 'classRoom', 'subject'])
            ->where('date', $date)
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get teacher weekly schedule
     *
     * @param int $teacherId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getTeacherWeeklySchedule(int $teacherId, string $startDate, string $endDate): array
    {
        $timetables = $this->model->with(['teacher', 'classRoom', 'subject'])
            ->where('teacher_id', $teacherId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        $schedule = [];
        foreach ($timetables as $timetable) {
            $dayOfWeek = date('l', strtotime($timetable->date));
            $schedule[$dayOfWeek][] = [
                'id' => $timetable->id,
                'date' => $timetable->date,
                'start_time' => $timetable->start_time,
                'end_time' => $timetable->end_time,
                'teacher' => $timetable->teacher,
                'subject' => $timetable->subject,
                'class' => $timetable->classRoom,
                'room' => $timetable->room
            ];
        }

        return $schedule;
    }

    /**
     * Get class weekly schedule
     *
     * @param int $classId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getClassWeeklySchedule(int $classId, string $startDate, string $endDate): array
    {
        $timetables = $this->model->with(['teacher', 'classRoom', 'subject'])
            ->where('class_id', $classId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        $schedule = [];
        foreach ($timetables as $timetable) {
            $dayOfWeek = date('l', strtotime($timetable->date));
            $schedule[$dayOfWeek][] = [
                'id' => $timetable->id,
                'date' => $timetable->date,
                'start_time' => $timetable->start_time,
                'end_time' => $timetable->end_time,
                'teacher' => $timetable->teacher,
                'subject' => $timetable->subject,
                'room' => $timetable->room
            ];
        }

        return $schedule;
    }

    /**
     * Check for time conflicts
     *
     * @param int $teacherId
     * @param string $date
     * @param string $startTime
     * @param string $endTime
     * @param int|null $excludeId
     * @return Collection
     */
    public function checkTimeConflicts(
        int $teacherId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $excludeId = null
    ): Collection {
        $query = $this->model->with(['teacher', 'classRoom', 'subject'])
            ->where('teacher_id', $teacherId)
            ->where('date', $date);

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
        
        $entriesByDate = $this->model->selectRaw('date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $entriesByTeacher = $this->model->selectRaw('teacher_id, COUNT(*) as count')
            ->groupBy('teacher_id')
            ->pluck('count', 'teacher_id')
            ->toArray();

        $entriesByClass = $this->model->selectRaw('class_id, COUNT(*) as count')
            ->groupBy('class_id')
            ->pluck('count', 'class_id')
            ->toArray();

        $entriesBySubject = $this->model->selectRaw('subject_id, COUNT(*) as count')
            ->groupBy('subject_id')
            ->pluck('count', 'subject_id')
            ->toArray();

        return [
            'total_entries' => $totalEntries,
            'entries_by_date' => $entriesByDate,
            'entries_by_teacher' => $entriesByTeacher,
            'entries_by_class' => $entriesByClass,
            'entries_by_subject' => $entriesBySubject
        ];
    }

    /**
     * Get timetables by date range
     *
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getTimetablesByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->model->with(['teacher', 'classRoom', 'subject'])
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get timetables by time range
     *
     * @param string $startTime
     * @param string $endTime
     * @return Collection
     */
    public function getTimetablesByTimeRange(string $startTime, string $endTime): Collection
    {
        return $this->model->with(['teacher', 'classRoom', 'subject'])
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($subQuery) use ($startTime, $endTime) {
                        $subQuery->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            })
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Generate timetable for a teacher based on constraints
     *
     * @param int $teacherId
     * @param array $constraints
     * @return Collection
     */
    public function generateTimetable(int $teacherId, array $constraints): Collection
    {
        $query = $this->model->with(['teacher', 'classRoom', 'subject'])
            ->where('teacher_id', $teacherId);

        // Apply constraints
        if (isset($constraints['date_from'])) {
            $query->where('date', '>=', $constraints['date_from']);
        }

        if (isset($constraints['date_to'])) {
            $query->where('date', '<=', $constraints['date_to']);
        }

        if (isset($constraints['class_id'])) {
            $query->where('class_id', $constraints['class_id']);
        }

        if (isset($constraints['subject_id'])) {
            $query->where('subject_id', $constraints['subject_id']);
        }

        if (isset($constraints['date'])) {
            $query->where('date', $constraints['date']);
        }

        return $query->orderBy('date')
            ->orderBy('start_time')
            ->get();
    }
} 