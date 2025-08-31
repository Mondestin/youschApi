<?php

namespace App\Repositories\Attendance;

use App\Models\Attendance\TeacherAttendanceExcuse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TeacherAttendanceExcuseRepositoryInterface
{
    public function getPaginatedExcuses(array $filters): LengthAwarePaginator;
    public function getExcuseById(int $id): ?TeacherAttendanceExcuse;
    public function createExcuse(array $data): TeacherAttendanceExcuse;
    public function updateExcuse(TeacherAttendanceExcuse $excuse, array $data): bool;
    public function deleteExcuse(TeacherAttendanceExcuse $excuse): bool;
    
    public function getExcusesByTeacher(int $teacherId, array $filters = []): Collection;
    public function getExcusesByClass(int $classId, array $filters = []): Collection;
    public function getExcusesBySubject(int $subjectId, array $filters = []): Collection;
    public function getExcusesByDate(string $date, array $filters = []): Collection;
    public function getExcusesByDateRange(string $startDate, string $endDate, array $filters = []): Collection;
    public function getExcusesByStatus(string $status, array $filters = []): Collection;
    public function getPendingExcuses(array $filters = []): Collection;
    public function getApprovedExcuses(array $filters = []): Collection;
    public function getRejectedExcuses(array $filters = []): Collection;
    
    public function approveExcuse(int $excuseId, int $reviewerId): bool;
    public function rejectExcuse(int $excuseId, int $reviewerId): bool;
    public function getExcuseStatistics(array $filters = []): array;
    public function generateExcuseReport(array $filters = []): array;
    public function getExcuseTrends(int $teacherId, string $startDate, string $endDate): array;
} 