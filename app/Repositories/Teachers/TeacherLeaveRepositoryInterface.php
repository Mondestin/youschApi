<?php

namespace App\Repositories\Teachers;

use App\Models\Teachers\TeacherLeave;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TeacherLeaveRepositoryInterface
{
    public function getPaginatedLeaves(array $filters): LengthAwarePaginator;
    public function getAllLeaves(array $filters): Collection;
    public function getLeaveById(int $id): ?TeacherLeave;
    public function createLeave(array $data): TeacherLeave;
    public function updateLeave(TeacherLeave $leave, array $data): bool;
    public function deleteLeave(TeacherLeave $leave): bool;
    public function getLeavesByTeacher(int $teacherId): Collection;
    public function getLeavesByType(string $type): Collection;
    public function getLeavesByStatus(string $status): Collection;
    public function getPendingLeaves(): Collection;
    public function getApprovedLeaves(): Collection;
    public function getRejectedLeaves(): Collection;
    public function getLeavesByDateRange(string $startDate, string $endDate): Collection;
    public function getActiveLeaves(): Collection;
    public function getLeaveStatistics(): array;
    public function approveLeave(int $leaveId, int $reviewerId): bool;
    public function rejectLeave(int $leaveId, int $reviewerId): bool;
} 