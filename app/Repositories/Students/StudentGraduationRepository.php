<?php

namespace App\Repositories\Students;

use App\Models\Students\StudentGraduation;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StudentGraduationRepository extends BaseRepository
{
    public function __construct(StudentGraduation $model)
    {
        parent::__construct($model);
    }

    public function getPaginatedGraduations(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['student']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (isset($filters['graduation_date_from'])) {
            $query->whereDate('graduation_date', '>=', $filters['graduation_date_from']);
        }

        if (isset($filters['graduation_date_to'])) {
            $query->whereDate('graduation_date', '<=', $filters['graduation_date_to']);
        }

        if (isset($filters['diploma_number'])) {
            $query->where('diploma_number', 'like', '%' . $filters['diploma_number'] . '%');
        }

        return $query->orderBy('graduation_date', 'desc')->paginate($perPage);
    }

    public function getGraduationById(int $id, array $relationships = []): ?StudentGraduation
    {
        $query = $this->model->where('id', $id);
        
        if (!empty($relationships)) {
            $query->with($relationships);
        }
        
        return $query->first();
    }

    public function createGraduation(array $data): StudentGraduation
    {
        return $this->model->create($data);
    }

    public function updateGraduation(StudentGraduation $graduation, array $data): bool
    {
        return $graduation->update($data);
    }

    public function deleteGraduation(StudentGraduation $graduation): bool
    {
        return $graduation->delete();
    }

    public function issueDiploma(StudentGraduation $graduation): bool
    {
        return DB::transaction(function() use ($graduation) {
            $graduation->issue();
            $graduation->student->graduate();
            return true;
        });
    }

    public function getGraduationsByStudent(int $studentId): Collection
    {
        return $this->model->where('student_id', $studentId)
            ->orderBy('graduation_date', 'desc')
            ->get();
    }

    public function getGraduationsByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->model->whereBetween('graduation_date', [$startDate, $endDate])
            ->with(['student'])
            ->orderBy('graduation_date', 'desc')
            ->get();
    }

    public function getGraduationStatistics(array $filters = []): array
    {
        $query = $this->model->query();

        if (isset($filters['school_id'])) {
            $query->whereHas('student', function($q) use ($filters) {
                $q->where('school_id', $filters['school_id']);
            });
        }

        $total = $query->count();
        $pending = $query->where('status', StudentGraduation::STATUS_PENDING)->count();
        $issued = $query->where('status', StudentGraduation::STATUS_ISSUED)->count();

        $recent = $query->where('graduation_date', '>=', now()->subDays(30))->count();

        $byYear = $query->selectRaw('YEAR(graduation_date) as year, COUNT(*) as count')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->get();

        return [
            'total_graduations' => $total,
            'pending_graduations' => $pending,
            'issued_graduations' => $issued,
            'recent_graduations' => $recent,
            'issuance_rate' => $total > 0 ? round(($issued / $total) * 100, 2) : 0,
            'graduations_by_year' => $byYear,
        ];
    }

    public function hasGraduationRecord(int $studentId): bool
    {
        return $this->model->where('student_id', $studentId)->exists();
    }

    public function isDiplomaNumberUnique(string $diplomaNumber, ?int $excludeId = null): bool
    {
        $query = $this->model->where('diploma_number', $diplomaNumber);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }
} 