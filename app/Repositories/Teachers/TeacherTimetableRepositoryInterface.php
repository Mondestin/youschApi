<?php

namespace App\Repositories\Teachers;

use App\Models\Teachers\TeacherTimetable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TeacherTimetableRepositoryInterface
{
    public function getPaginatedTimetables(array $filters): LengthAwarePaginator;
    public function getTimetableById(int $id): ?TeacherTimetable;
    public function createTimetable(array $data): TeacherTimetable;
    public function updateTimetable(TeacherTimetable $timetable, array $data): bool;
    public function deleteTimetable(TeacherTimetable $timetable): bool;
    public function getTimetablesByTeacher(int $teacherId): Collection;
    public function getTimetablesByClass(int $classId): Collection;
    public function getTimetablesByDate(string $date): Collection;
    public function getTimetablesByDateRange(string $startDate, string $endDate): Collection;
    public function getTimetablesByTimeRange(string $startTime, string $endTime): Collection;
    public function generateTimetable(int $teacherId, array $constraints): Collection;
    public function getTimetableStatistics(): array;
} 