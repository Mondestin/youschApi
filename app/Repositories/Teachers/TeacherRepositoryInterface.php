<?php

namespace App\Repositories\Teachers;

use App\Models\Teachers\Teacher;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TeacherRepositoryInterface
{
    public function getPaginatedTeachers(array $filters): LengthAwarePaginator;
    public function getTeacherById(int $id, array $with = []): ?Teacher;
    public function createTeacher(array $data): Teacher;
    public function updateTeacher(Teacher $teacher, array $data): bool;
    public function deleteTeacher(Teacher $teacher): bool;
    public function getTeachersByCampus(int $campusId): Collection;
    public function getTeachersBySchool(int $schoolId): Collection;
    public function getTeachersByStatus(string $status): Collection;
    public function getTeachersByHireDateRange(string $startDate, string $endDate): Collection;
    public function getTeacherStatistics(array $filters): array;
    public function getTeacherWorkload(int $teacherId, int $termId): array;
    public function getTeacherSchedule(int $teacherId, string $date): Collection;
    public function searchTeachers(string $query, array $filters = []): LengthAwarePaginator;
    public function getActiveTeachersCount(): int;
    public function getTeachersOnLeaveCount(): int;
    public function getRecentHires(int $days = 30): Collection;
} 