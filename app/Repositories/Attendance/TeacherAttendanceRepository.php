<?php

namespace App\Repositories\Attendance;

use App\Models\Attendance\TeacherAttendance;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TeacherAttendanceRepository implements TeacherAttendanceRepositoryInterface
{
    public function getPaginatedAttendance(array $filters): LengthAwarePaginator
    {
        $query = TeacherAttendance::with(['teacher', 'class', 'subject', 'lab', 'timetable']);

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

        if (isset($filters['term'])) {
            $query->whereHas('timetable', function ($q) use ($filters) {
                $q->where('term', $filters['term']);
            });
        }

        if (isset($filters['academic_year'])) {
            $query->whereHas('timetable', function ($q) use ($filters) {
                $q->where('academic_year', $filters['academic_year']);
            });
        }

        return $query->orderBy('date', 'desc')->paginate(15);
    }

    public function getAttendanceById(int $id): ?TeacherAttendance
    {
        return TeacherAttendance::with(['teacher', 'class', 'subject', 'lab', 'timetable'])->find($id);
    }

    public function createAttendance(array $data): TeacherAttendance
    {
        return TeacherAttendance::create($data);
    }

    public function updateAttendance(TeacherAttendance $attendance, array $data): bool
    {
        return $attendance->update($data);
    }

    public function deleteAttendance(TeacherAttendance $attendance): bool
    {
        return $attendance->delete();
    }

    public function getAttendanceByTeacher(int $teacherId, array $filters = []): Collection
    {
        $query = TeacherAttendance::with(['class', 'subject', 'lab', 'timetable'])
            ->where('teacher_id', $teacherId);

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('date', [$filters['start_date'], $filters['end_date']]);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('date', 'desc')->get();
    }

    public function getAttendanceByClass(int $classId, array $filters = []): Collection
    {
        $query = TeacherAttendance::with(['teacher', 'subject', 'lab', 'timetable'])
            ->where('class_id', $classId);

        if (isset($filters['date'])) {
            $query->where('date', $filters['date']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('teacher_id')->get();
    }

    public function getAttendanceBySubject(int $subjectId, array $filters = []): Collection
    {
        $query = TeacherAttendance::with(['teacher', 'class', 'lab', 'timetable'])
            ->where('subject_id', $subjectId);

        if (isset($filters['date'])) {
            $query->where('date', $filters['date']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('date', 'desc')->get();
    }

    public function getAttendanceByDate(string $date, array $filters = []): Collection
    {
        $query = TeacherAttendance::with(['teacher', 'class', 'subject', 'lab', 'timetable'])
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

        return $query->orderBy('class_id')->orderBy('teacher_id')->get();
    }

    public function getAttendanceByDateRange(string $startDate, string $endDate, array $filters = []): Collection
    {
        $query = TeacherAttendance::with(['teacher', 'class', 'subject', 'lab', 'timetable'])
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

        return $query->orderBy('date')->orderBy('class_id')->get();
    }

    public function getAttendanceByStatus(string $status, array $filters = []): Collection
    {
        $query = TeacherAttendance::with(['teacher', 'class', 'subject', 'lab', 'timetable'])
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

        return $query->orderBy('date', 'desc')->get();
    }

    public function getAttendanceByTerm(string $term, array $filters = []): Collection
    {
        $query = TeacherAttendance::with(['teacher', 'class', 'subject', 'lab', 'timetable'])
            ->whereHas('timetable', function ($q) use ($term) {
                $q->where('term', $term);
            });

        if (isset($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        if (isset($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('date')->get();
    }

    public function getAttendanceByAcademicYear(string $academicYear, array $filters = []): Collection
    {
        $query = TeacherAttendance::with(['teacher', 'class', 'subject', 'lab', 'timetable'])
            ->whereHas('timetable', function ($q) use ($academicYear) {
                $q->where('academic_year', $academicYear);
            });

        if (isset($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        if (isset($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('date')->get();
    }

    public function bulkCreateAttendance(array $attendanceData): array
    {
        $results = [
            'successful' => 0,
            'failed' => 0,
            'errors' => []
        ];

        DB::beginTransaction();
        try {
            foreach ($attendanceData as $index => $data) {
                try {
                    $this->createAttendance($data);
                    $results['successful']++;
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'index' => $index,
                        'message' => $e->getMessage()
                    ];
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    public function bulkUpdateAttendance(array $attendanceData): array
    {
        $results = [
            'successful' => 0,
            'failed' => 0,
            'errors' => []
        ];

        DB::beginTransaction();
        try {
            foreach ($attendanceData as $index => $data) {
                try {
                    if (isset($data['id'])) {
                        $attendance = $this->getAttendanceById($data['id']);
                        if ($attendance) {
                            unset($data['id']);
                            $this->updateAttendance($attendance, $data);
                            $results['successful']++;
                        } else {
                            $results['failed']++;
                            $results['errors'][] = [
                                'index' => $index,
                                'message' => 'Attendance record not found'
                            ];
                        }
                    } else {
                        $results['failed']++;
                        $results['errors'][] = [
                            'index' => $index,
                            'message' => 'Missing attendance ID'
                        ];
                    }
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'index' => $index,
                        'message' => $e->getMessage()
                    ];
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    public function getAttendanceStatistics(array $filters = []): array
    {
        $query = TeacherAttendance::query();

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('date', [$filters['start_date'], $filters['end_date']]);
        }

        if (isset($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        $totalRecords = $query->count();
        $presentCount = (clone $query)->where('status', 'present')->count();
        $absentCount = (clone $query)->where('status', 'absent')->count();
        $lateCount = (clone $query)->where('status', 'late')->count();

        return [
            'total_records' => $totalRecords,
            'present_count' => $presentCount,
            'absent_count' => $absentCount,
            'late_count' => $lateCount,
            'present_percentage' => $totalRecords > 0 ? round(($presentCount / $totalRecords) * 100, 2) : 0,
            'absent_percentage' => $totalRecords > 0 ? round(($absentCount / $totalRecords) * 100, 2) : 0,
            'late_percentage' => $totalRecords > 0 ? round(($lateCount / $totalRecords) * 100, 2) : 0,
        ];
    }

    public function generateAttendanceReport(array $filters = []): array
    {
        $query = TeacherAttendance::with(['teacher', 'class', 'subject', 'lab', 'timetable']);

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('date', [$filters['start_date'], $filters['end_date']]);
        }

        if (isset($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        $attendance = $query->get();

        $report = [
            'summary' => $this->getAttendanceStatistics($filters),
            'attendance_by_teacher' => $attendance->groupBy('teacher.name')
                ->map(function ($teacherAttendance) {
                    return [
                        'total' => $teacherAttendance->count(),
                        'present' => $teacherAttendance->where('status', 'present')->count(),
                        'absent' => $teacherAttendance->where('status', 'absent')->count(),
                        'late' => $teacherAttendance->where('status', 'late')->count(),
                    ];
                }),
            'attendance_by_date' => $attendance->groupBy('date')
                ->map(function ($dateAttendance) {
                    return [
                        'total' => $dateAttendance->count(),
                        'present' => $dateAttendance->where('status', 'present')->count(),
                        'absent' => $dateAttendance->where('status', 'absent')->count(),
                        'late' => $dateAttendance->where('status', 'late')->count(),
                    ];
                }),
            'attendance_by_status' => $attendance->groupBy('status')
                ->map(function ($statusAttendance) {
                    return [
                        'count' => $statusAttendance->count(),
                        'percentage' => round(($statusAttendance->count() / $attendance->count()) * 100, 2),
                    ];
                })
        ];

        return $report;
    }

    public function getAttendanceTrends(int $teacherId, string $startDate, string $endDate): array
    {
        $attendance = $this->getAttendanceByTeacher($teacherId, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        $trends = [
            'total_days' => $attendance->count(),
            'present_days' => $attendance->where('status', 'present')->count(),
            'absent_days' => $attendance->where('status', 'absent')->count(),
            'late_days' => $attendance->where('status', 'late')->count(),
            'attendance_rate' => $attendance->count() > 0 ? round(($attendance->where('status', 'present')->count() / $attendance->count()) * 100, 2) : 0,
            'daily_trends' => $attendance->groupBy('date')
                ->map(function ($dayAttendance) {
                    return [
                        'date' => $dayAttendance->first()->date->format('Y-m-d'),
                        'status' => $dayAttendance->first()->status,
                        'subject' => $dayAttendance->first()->subject->name ?? 'Unknown',
                        'class' => $dayAttendance->first()->class->name ?? 'Unknown',
                    ];
                })
        ];

        return $trends;
    }

    public function getClassAttendanceSummary(int $classId, string $date): array
    {
        $attendance = $this->getAttendanceByClass($classId, ['date' => $date]);

        $summary = [
            'class_id' => $classId,
            'date' => $date,
            'total_teachers' => $attendance->count(),
            'present_teachers' => $attendance->where('status', 'present')->count(),
            'absent_teachers' => $attendance->where('status', 'absent')->count(),
            'late_teachers' => $attendance->where('status', 'late')->count(),
            'attendance_rate' => $attendance->count() > 0 ? round(($attendance->where('status', 'present')->count() / $attendance->count()) * 100, 2) : 0,
            'teacher_details' => $attendance->map(function ($record) {
                return [
                    'teacher_id' => $record->teacher_id,
                    'teacher_name' => $record->teacher->full_name ?? 'Unknown',
                    'status' => $record->status,
                    'remarks' => $record->remarks,
                ];
            })
        ];

        return $summary;
    }

    public function getTeacherAttendanceSummary(int $teacherId, string $startDate, string $endDate): array
    {
        $attendance = $this->getAttendanceByTeacher($teacherId, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        $summary = [
            'teacher_id' => $teacherId,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'total_days' => $attendance->count(),
            'present_days' => $attendance->where('status', 'present')->count(),
            'absent_days' => $attendance->where('status', 'absent')->count(),
            'late_days' => $attendance->where('status', 'late')->count(),
            'attendance_rate' => $attendance->count() > 0 ? round(($attendance->where('status', 'present')->count() / $attendance->count()) * 100, 2) : 0,
            'subject_breakdown' => $attendance->groupBy('subject.name')
                ->map(function ($subjectAttendance) {
                    return [
                        'total' => $subjectAttendance->count(),
                        'present' => $subjectAttendance->where('status', 'present')->count(),
                        'absent' => $subjectAttendance->where('status', 'absent')->count(),
                        'late' => $subjectAttendance->where('status', 'late')->count(),
                        'rate' => $subjectAttendance->count() > 0 ? round(($subjectAttendance->where('status', 'present')->count() / $subjectAttendance->count()) * 100, 2) : 0,
                    ];
                })
        ];

        return $summary;
    }
} 