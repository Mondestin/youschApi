<?php

namespace App\Repositories\Attendance;

use App\Models\Attendance\StudentAttendanceExcuse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface StudentAttendanceExcuseRepositoryInterface
{
    public function getPaginatedExcuses(array $filters): LengthAwarePaginator;
    public function getExcuseById(int $id): ?StudentAttendanceExcuse;
    public function createExcuse(array $data): StudentAttendanceExcuse;
    public function updateExcuse(StudentAttendanceExcuse $excuse, array $data): bool;
    public function deleteExcuse(StudentAttendanceExcuse $excuse): bool;
    
    public function getExcusesByStudent(int $studentId, array $filters = []): Collection;
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
    public function getExcuseTrends(int $studentId, string $startDate, string $endDate): array;
} 