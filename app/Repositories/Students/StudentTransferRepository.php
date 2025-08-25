<?php

namespace App\Repositories\Students;

use App\Models\Students\StudentTransfer;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StudentTransferRepository extends BaseRepository
{
    public function __construct(StudentTransfer $model)
    {
        parent::__construct($model);
    }

    public function getPaginatedTransfers(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['student', 'fromCampus', 'toCampus', 'reviewer']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (isset($filters['from_campus_id'])) {
            $query->where('from_campus_id', $filters['from_campus_id']);
        }

        if (isset($filters['to_campus_id'])) {
            $query->where('to_campus_id', $filters['to_campus_id']);
        }

        if (isset($filters['reviewer_id'])) {
            $query->where('reviewer_id', $filters['reviewer_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('request_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('request_date', '<=', $filters['date_to']);
        }

        return $query->orderBy('request_date', 'desc')->paginate($perPage);
    }

    public function getTransferById(int $id, array $relationships = []): ?StudentTransfer
    {
        $query = $this->model->where('id', $id);
        
        if (!empty($relationships)) {
            $query->with($relationships);
        }
        
        return $query->first();
    }

    public function createTransfer(array $data): StudentTransfer
    {
        return $this->model->create($data);
    }

    public function updateTransfer(StudentTransfer $transfer, array $data): bool
    {
        return $transfer->update($data);
    }

    public function deleteTransfer(StudentTransfer $transfer): bool
    {
        return $transfer->delete();
    }

    public function approveTransfer(StudentTransfer $transfer, int $reviewerId): bool
    {
        return DB::transaction(function() use ($transfer, $reviewerId) {
            $transfer->approve($reviewerId);
            
            $student = $transfer->student;
            $student->update(['campus_id' => $transfer->to_campus_id]);
            
            return true;
        });
    }

    public function rejectTransfer(StudentTransfer $transfer, int $reviewerId): bool
    {
        return $transfer->reject($reviewerId);
    }

    public function getTransfersByStudent(int $studentId): Collection
    {
        return $this->model->where('student_id', $studentId)
            ->with(['fromCampus', 'toCampus', 'reviewer'])
            ->orderBy('request_date', 'desc')
            ->get();
    }

    public function getTransfersByCampus(int $campusId): Collection
    {
        return $this->model->where(function($query) use ($campusId) {
            $query->where('from_campus_id', $campusId)
                  ->orWhere('to_campus_id', $campusId);
        })
        ->with(['student', 'fromCampus', 'toCampus', 'reviewer'])
        ->orderBy('request_date', 'desc')
        ->get();
    }

    public function getTransferStatistics(array $filters = []): array
    {
        $query = $this->model->query();

        if (isset($filters['school_id'])) {
            $query->whereHas('student', function($q) use ($filters) {
                $q->where('school_id', $filters['school_id']);
            });
        }

        $total = $query->count();
        $pending = $query->where('status', StudentTransfer::STATUS_PENDING)->count();
        $approved = $query->where('status', StudentTransfer::STATUS_APPROVED)->count();
        $rejected = $query->where('status', StudentTransfer::STATUS_REJECTED)->count();

        $recent = $query->where('request_date', '>=', now()->subDays(30))->count();

        $avgProcessingTime = $this->model->where('status', StudentTransfer::STATUS_APPROVED)
            ->whereNotNull('approved_date')
            ->get()
            ->avg('transfer_duration');

        return [
            'total_transfers' => $total,
            'pending_transfers' => $pending,
            'approved_transfers' => $approved,
            'rejected_transfers' => $rejected,
            'recent_transfers' => $recent,
            'approval_rate' => $total > 0 ? round(($approved / $total) * 100, 2) : 0,
            'average_processing_days' => round($avgProcessingTime, 1),
        ];
    }

    public function hasPendingTransfer(int $studentId): bool
    {
        return $this->model->where('student_id', $studentId)
            ->where('status', StudentTransfer::STATUS_PENDING)
            ->exists();
    }
} 