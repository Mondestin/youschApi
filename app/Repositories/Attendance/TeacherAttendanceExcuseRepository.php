<?php

namespace App\Repositories\Attendance;

use App\Models\Attendance\TeacherAttendanceExcuse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TeacherAttendanceExcuseRepository implements TeacherAttendanceExcuseRepositoryInterface
{
    public function getPaginatedExcuses(array $filters): LengthAwarePaginator
    {
        $query = TeacherAttendanceExcuse::with(['teacher', 'class', 'subject', 'lab', 'reviewer']);

        if (isset($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        if (isset($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (isset($filters['date'])) {
            $query->where('date', $filters['date']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function getExcuseById(int $id): ?TeacherAttendanceExcuse
    {
        return TeacherAttendanceExcuse::with(['teacher', 'class', 'subject', 'lab', 'reviewer'])->find($id);
    }

    public function createExcuse(array $data): TeacherAttendanceExcuse
    {
        return TeacherAttendanceExcuse::create($data);
    }

    public function updateExcuse(TeacherAttendanceExcuse $excuse, array $data): bool
    {
        return $excuse->update($data);
    }

    public function deleteExcuse(TeacherAttendanceExcuse $excuse): bool
    {
        return $excuse->delete();
    }

    public function getExcusesByTeacher(int $teacherId, array $filters = []): Collection
    {
        $query = TeacherAttendanceExcuse::with(['class', 'subject', 'lab', 'reviewer'])
            ->where('teacher_id', $teacherId);

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('date', [$filters['start_date'], $filters['end_date']]);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('date', 'desc')->get();
    }

    public function getExcusesByClass(int $classId, array $filters = []): Collection
    {
        $query = TeacherAttendanceExcuse::with(['teacher', 'subject', 'lab', 'reviewer'])
            ->where('class_id', $classId);

        if (isset($filters['date'])) {
            $query->where('date', $filters['date']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('date', 'desc')->get();
    }

    public function getExcusesBySubject(int $subjectId, array $filters = []): Collection
    {
        $query = TeacherAttendanceExcuse::with(['teacher', 'class', 'lab', 'reviewer'])
            ->where('subject_id', $subjectId);

        if (isset($filters['date'])) {
            $query->where('date', $filters['date']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('date', 'desc')->get();
    }

    public function getExcusesByDate(string $date, array $filters = []): Collection
    {
        $query = TeacherAttendanceExcuse::with(['teacher', 'class', 'subject', 'lab', 'reviewer'])
            ->where('date', $date);

        if (isset($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getExcusesByDateRange(string $startDate, string $endDate, array $filters = []): Collection
    {
        $query = TeacherAttendanceExcuse::with(['teacher', 'class', 'subject', 'lab', 'reviewer'])
            ->whereBetween('date', [$startDate, $endDate]);

        if (isset($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        if (isset($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('date')->orderBy('created_at')->get();
    }

    public function getExcusesByStatus(string $status, array $filters = []): Collection
    {
        $query = TeacherAttendanceExcuse::with(['teacher', 'class', 'subject', 'lab', 'reviewer'])
            ->where('status', $status);

        if (isset($filters['date'])) {
            $query->where('date', $filters['date']);
        }

        if (isset($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getPendingExcuses(array $filters = []): Collection
    {
        return $this->getExcusesByStatus('pending', $filters);
    }

    public function getApprovedExcuses(array $filters = []): Collection
    {
        return $this->getExcusesByStatus('approved', $filters);
    }

    public function getRejectedExcuses(array $filters = []): Collection
    {
        return $this->getExcusesByStatus('rejected', $filters);
    }

    public function approveExcuse(int $excuseId, int $reviewerId): bool
    {
        $excuse = $this->getExcuseById($excuseId);
        if ($excuse) {
            $excuse->approve($reviewerId);
            return true;
        }
        return false;
    }

    public function rejectExcuse(int $excuseId, int $reviewerId): bool
    {
        $excuse = $this->getExcuseById($excuseId);
        if ($excuse) {
            $excuse->reject($reviewerId);
            return true;
        }
        return false;
    }

    public function getExcuseStatistics(array $filters = []): array
    {
        $query = TeacherAttendanceExcuse::query();

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('date', [$filters['start_date'], $filters['end_date']]);
        }

        if (isset($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        $totalExcuses = $query->count();
        $pendingCount = (clone $query)->where('status', 'pending')->count();
        $approvedCount = (clone $query)->where('status', 'approved')->count();
        $rejectedCount = (clone $query)->where('status', 'rejected')->count();

        return [
            'total_excuses' => $totalExcuses,
            'pending_count' => $pendingCount,
            'approved_count' => $approvedCount,
            'rejected_count' => $rejectedCount,
            'pending_percentage' => $totalExcuses > 0 ? round(($pendingCount / $totalExcuses) * 100, 2) : 0,
            'approved_percentage' => $totalExcuses > 0 ? round(($approvedCount / $totalExcuses) * 100, 2) : 0,
            'rejected_percentage' => $totalExcuses > 0 ? round(($rejectedCount / $totalExcuses) * 100, 2) : 0,
        ];
    }

    public function generateExcuseReport(array $filters = []): array
    {
        $query = TeacherAttendanceExcuse::with(['teacher', 'class', 'subject', 'lab', 'reviewer']);

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('date', [$filters['start_date'], $filters['end_date']]);
        }

        if (isset($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        $excuses = $query->get();

        $report = [
            'summary' => $this->getExcuseStatistics($filters),
            'excuses_by_teacher' => $excuses->groupBy('teacher.name')
                ->map(function ($teacherExcuses) {
                    return [
                        'total' => $teacherExcuses->count(),
                        'pending' => $teacherExcuses->where('status', 'pending')->count(),
                        'approved' => $teacherExcuses->where('status', 'approved')->count(),
                        'rejected' => $teacherExcuses->where('status', 'rejected')->count(),
                    ];
                }),
            'excuses_by_date' => $excuses->groupBy('date')
                ->map(function ($dateExcuses) {
                    return [
                        'total' => $dateExcuses->count(),
                        'pending' => $dateExcuses->where('status', 'pending')->count(),
                        'approved' => $dateExcuses->where('status', 'approved')->count(),
                        'rejected' => $dateExcuses->where('status', 'rejected')->count(),
                    ];
                }),
            'excuses_by_status' => $excuses->groupBy('status')
                ->map(function ($statusExcuses) {
                    return [
                        'count' => $statusExcuses->count(),
                        'percentage' => round(($statusExcuses->count() / $excuses->count()) * 100, 2),
                    ];
                })
        ];

        return $report;
    }

    public function getExcuseTrends(int $teacherId, string $startDate, string $endDate): array
    {
        $excuses = $this->getExcusesByTeacher($teacherId, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        $trends = [
            'total_excuses' => $excuses->count(),
            'pending_excuses' => $excuses->where('status', 'pending')->count(),
            'approved_excuses' => $excuses->where('status', 'approved')->count(),
            'rejected_excuses' => $excuses->where('status', 'rejected')->count(),
            'approval_rate' => $excuses->count() > 0 ? round(($excuses->where('status', 'approved')->count() / $excuses->count()) * 100, 2) : 0,
            'daily_trends' => $excuses->groupBy('date')
                ->map(function ($dayExcuses) {
                    return [
                        'date' => $dayExcuses->first()->date->format('Y-m-d'),
                        'status' => $dayExcuses->first()->status,
                        'subject' => $dayExcuses->first()->subject->name ?? 'Unknown',
                        'class' => $dayExcuses->first()->class->name ?? 'Unknown',
                        'reason' => $dayExcuses->first()->reason,
                    ];
                })
        ];

        return $trends;
    }
} 