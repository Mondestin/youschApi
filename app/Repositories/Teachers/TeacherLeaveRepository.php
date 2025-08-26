<?php

namespace App\Repositories\Teachers;

use App\Models\Teachers\TeacherLeave;
use App\Repositories\BaseRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class TeacherLeaveRepository extends BaseRepository implements TeacherLeaveRepositoryInterface
{
    public function __construct(TeacherLeave $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated leaves with filters
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPaginatedLeaves(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['teacher', 'reviewer']);

        // Apply filters
        if (isset($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        if (isset($filters['type'])) {
            $query->where('leave_type', $filters['type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['start_date'])) {
            $query->where('start_date', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('end_date', '<=', $filters['end_date']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get leave by ID
     *
     * @param int $id
     * @return TeacherLeave|null
     */
    public function getLeaveById(int $id): ?TeacherLeave
    {
        return $this->model->with(['teacher', 'reviewer'])->find($id);
    }

    /**
     * Create a new leave
     *
     * @param array $data
     * @return TeacherLeave
     */
    public function createLeave(array $data): TeacherLeave
    {
        $data['status'] = $data['status'] ?? 'pending';
        $data['submitted_at'] = now();
        
        return $this->model->create($data);
    }

    /**
     * Update leave
     *
     * @param TeacherLeave $leave
     * @param array $data
     * @return bool
     */
    public function updateLeave(TeacherLeave $leave, array $data): bool
    {
        return $leave->update($data);
    }

    /**
     * Delete leave
     *
     * @param TeacherLeave $leave
     * @return bool
     */
    public function deleteLeave(TeacherLeave $leave): bool
    {
        return $leave->delete();
    }

    /**
     * Get leaves by teacher
     *
     * @param int $teacherId
     * @return Collection
     */
    public function getLeavesByTeacher(int $teacherId): Collection
    {
        return $this->model->with(['teacher', 'reviewer'])
            ->where('teacher_id', $teacherId)
            ->orderBy('start_date', 'desc')
            ->get();
    }

    /**
     * Get leaves by type
     *
     * @param string $type
     * @return Collection
     */
    public function getLeavesByType(string $type): Collection
    {
        return $this->model->with(['teacher', 'reviewer'])
            ->where('leave_type', $type)
            ->orderBy('start_date', 'desc')
            ->get();
    }

    /**
     * Get leaves by status
     *
     * @param string $status
     * @return Collection
     */
    public function getLeavesByStatus(string $status): Collection
    {
        return $this->model->with(['teacher', 'reviewer'])
            ->where('status', $status)
            ->orderBy('start_date', 'desc')
            ->get();
    }

    /**
     * Get pending leaves
     *
     * @return Collection
     */
    public function getPendingLeaves(): Collection
    {
        return $this->model->with(['teacher', 'reviewer'])
            ->where('status', 'pending')
            ->orderBy('submitted_at', 'asc')
            ->get();
    }

    /**
     * Get approved leaves
     *
     * @return Collection
     */
    public function getApprovedLeaves(): Collection
    {
        return $this->model->with(['teacher', 'reviewer'])
            ->where('status', 'approved')
            ->orderBy('start_date', 'desc')
            ->get();
    }

    /**
     * Get rejected leaves
     *
     * @return Collection
     */
    public function getRejectedLeaves(): Collection
    {
        return $this->model->with(['teacher', 'reviewer'])
            ->where('status', 'rejected')
            ->orderBy('submitted_at', 'desc')
            ->get();
    }

    /**
     * Get leaves by date range
     *
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getLeavesByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->model->with(['teacher', 'reviewer'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->orderBy('start_date', 'asc')
            ->get();
    }

    /**
     * Get active leaves (currently ongoing)
     *
     * @return Collection
     */
    public function getActiveLeaves(): Collection
    {
        $today = Carbon::today()->toDateString();
        
        return $this->model->with(['teacher', 'reviewer'])
            ->where('status', 'approved')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->orderBy('start_date', 'asc')
            ->get();
    }

    /**
     * Get leave statistics
     *
     * @return array
     */
    public function getLeaveStatistics(): array
    {
        $totalLeaves = $this->model->count();
        $pendingLeaves = $this->model->where('status', 'pending')->count();
        $approvedLeaves = $this->model->where('status', 'approved')->count();
        $rejectedLeaves = $this->model->where('status', 'rejected')->count();

        $leaveTypes = $this->model->selectRaw('leave_type, COUNT(*) as count')
            ->groupBy('leave_type')
            ->pluck('count', 'leave_type')
            ->toArray();

        $monthlyStats = $this->model->selectRaw('MONTH(start_date) as month, COUNT(*) as count')
            ->whereYear('start_date', date('Y'))
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        return [
            'total_leaves' => $totalLeaves,
            'pending_leaves' => $pendingLeaves,
            'approved_leaves' => $approvedLeaves,
            'rejected_leaves' => $rejectedLeaves,
            'leave_types' => $leaveTypes,
            'monthly_stats' => $monthlyStats,
            'approval_rate' => $totalLeaves > 0 ? round(($approvedLeaves / $totalLeaves) * 100, 2) : 0
        ];
    }

    /**
     * Approve leave
     *
     * @param int $leaveId
     * @param int $reviewerId
     * @return bool
     */
    public function approveLeave(int $leaveId, int $reviewerId): bool
    {
        $leave = $this->model->find($leaveId);
        
        if (!$leave) {
            return false;
        }

        return $leave->update([
            'status' => 'approved',
            'reviewer_id' => $reviewerId,
            'reviewed_at' => now()
        ]);
    }

    /**
     * Reject leave
     *
     * @param int $leaveId
     * @param int $reviewerId
     * @return bool
     */
    public function rejectLeave(int $leaveId, int $reviewerId): bool
    {
        $leave = $this->model->find($leaveId);
        
        if (!$leave) {
            return false;
        }

        return $leave->update([
            'status' => 'rejected',
            'reviewer_id' => $reviewerId,
            'reviewed_at' => now()
        ]);
    }
} 