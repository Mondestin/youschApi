<?php

namespace App\Repositories\Teachers;

use App\Models\Teachers\TeacherPerformance;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TeacherPerformanceRepositoryInterface
{
    public function getPaginatedPerformance(array $filters): LengthAwarePaginator;
    public function getPerformanceById(int $id): ?TeacherPerformance;
    public function createPerformance(array $data): TeacherPerformance;
    public function updatePerformance(TeacherPerformance $performance, array $data): bool;
    public function deletePerformance(TeacherPerformance $performance): bool;
    public function getPerformanceByTeacher(int $teacherId): Collection;
    public function getPerformanceByPeriod(string $period): Collection;
    public function getPerformanceByDateRange(string $startDate, string $endDate): Collection;
    public function getExcellentPerformance(): Collection;
    public function getGoodPerformance(): Collection;
    public function getSatisfactoryPerformance(): Collection;
    public function getPerformanceNeedingImprovement(): Collection;
    public function getRecentPerformance(int $days = 365): Collection;
    public function getPerformanceStatistics(): array;
    public function evaluateTeacher(int $teacherId, array $evaluationData): TeacherPerformance;
} 