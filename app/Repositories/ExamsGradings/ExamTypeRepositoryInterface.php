<?php

namespace App\Repositories\ExamsGradings;

use App\Models\ExamsGradings\ExamType;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ExamTypeRepositoryInterface
{
    public function getPaginatedExamTypes(array $filters): LengthAwarePaginator;
    public function getExamTypeById(int $id): ?ExamType;
    public function createExamType(array $data): ExamType;
    public function updateExamType(ExamType $examType, array $data): bool;
    public function deleteExamType(ExamType $examType): bool;
    public function getAllExamTypes(): Collection;
    public function getExamTypesByWeight(float $weight): Collection;
    public function getWeightedExamTypes(): Collection;
    public function getExamTypeStatistics(): array;
} 