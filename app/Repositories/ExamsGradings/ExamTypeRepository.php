<?php

namespace App\Repositories\ExamsGradings;

use App\Models\ExamsGradings\ExamType;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ExamTypeRepository implements ExamTypeRepositoryInterface
{
    public function getPaginatedExamTypes(array $filters): LengthAwarePaginator
    {
        $query = ExamType::query();

        if (isset($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (isset($filters['weight'])) {
            $query->where('weight', $filters['weight']);
        }

        if (isset($filters['min_weight'])) {
            $query->where('weight', '>=', $filters['min_weight']);
        }

        if (isset($filters['max_weight'])) {
            $query->where('weight', '<=', $filters['max_weight']);
        }

        return $query->orderBy('name')->paginate(15);
    }

    public function getExamTypeById(int $id): ?ExamType
    {
        return ExamType::find($id);
    }

    public function createExamType(array $data): ExamType
    {
        return ExamType::create($data);
    }

    public function updateExamType(ExamType $examType, array $data): bool
    {
        return $examType->update($data);
    }

    public function deleteExamType(ExamType $examType): bool
    {
        return $examType->delete();
    }

    public function getAllExamTypes(): Collection
    {
        return ExamType::orderBy('name')->get();
    }

    public function getExamTypesByWeight(float $weight): Collection
    {
        return ExamType::byWeight($weight)->get();
    }

    public function getWeightedExamTypes(): Collection
    {
        return ExamType::withWeightGreaterThan(100.0)->get();
    }

    public function getExamTypeStatistics(): array
    {
        $totalTypes = ExamType::count();
        $weightedTypes = ExamType::withWeightGreaterThan(100.0)->count();
        $standardTypes = ExamType::where('weight', 100.0)->count();

        $weightDistribution = ExamType::selectRaw('weight, COUNT(*) as count')
            ->groupBy('weight')
            ->orderBy('weight')
            ->get()
            ->pluck('count', 'weight')
            ->toArray();

        return [
            'total_types' => $totalTypes,
            'weighted_types' => $weightedTypes,
            'standard_types' => $standardTypes,
            'weight_distribution' => $weightDistribution,
            'average_weight' => ExamType::avg('weight'),
            'min_weight' => ExamType::min('weight'),
            'max_weight' => ExamType::max('weight'),
        ];
    }
} 