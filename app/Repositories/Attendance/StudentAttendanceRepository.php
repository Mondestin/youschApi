<?php

namespace App\Repositories\Attendance;

use App\Models\Attendance\StudentAttendance;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentAttendanceRepository implements StudentAttendanceRepositoryInterface
{
    public function getPaginatedAttendance(array $filters): LengthAwarePaginator
    {
        $query = StudentAttendance::with(['student', 'class', 'subject', 'lab', 'timetable']);

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
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

    public function getAttendanceById(int $id): ?StudentAttendance
    {
        return StudentAttendance::with(['student', 'class', 'subject', 'lab', 'timetable'])->find($id);
    }

    public function createAttendance(array $data): StudentAttendance
    {
        return StudentAttendance::create($data);
    }

    public function updateAttendance(StudentAttendance $attendance, array $data): bool
    {
        return $attendance->update($data);
    }

    public function deleteAttendance(StudentAttendance $attendance): bool
    {
        return $attendance->delete();
    }

    public function getAttendanceByStudent(int $studentId, array $filters = []): Collection
    {
        $query = StudentAttendance::with(['class', 'subject', 'lab', 'timetable'])
            ->where('student_id', $studentId);

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
        $query = StudentAttendance::with(['student', 'subject', 'lab', 'timetable'])
            ->where('class_id', $classId);

        if (isset($filters['date'])) {
            $query->where('date', $filters['date']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('student_id')->get();
    }

    public function getAttendanceBySubject(int $subjectId, array $filters = []): Collection
    {
        $query = StudentAttendance::with(['student', 'class', 'lab', 'timetable'])
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
        $query = StudentAttendance::with(['student', 'class', 'subject', 'lab', 'timetable'])
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

        return $query->orderBy('class_id')->orderBy('student_id')->get();
    }

    public function getAttendanceByDateRange(string $startDate, string $endDate, array $filters = []): Collection
    {
        $query = StudentAttendance::with(['student', 'class', 'subject', 'lab', 'timetable'])
            ->whereBetween('date', [$startDate, $endDate]);

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
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
        $query = StudentAttendance::with(['student', 'class', 'subject', 'lab', 'timetable'])
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
        $query = StudentAttendance::with(['student', 'class', 'subject', 'lab', 'timetable'])
            ->whereHas('timetable', function ($q) use ($term) {
                $q->where('term', $term);
            });

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
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
        $query = StudentAttendance::with(['student', 'class', 'subject', 'lab', 'timetable'])
            ->whereHas('timetable', function ($q) use ($academicYear) {
                $q->where('academic_year', $academicYear);
            });

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
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
        $query = StudentAttendance::query();

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
        $excusedCount = (clone $query)->where('status', 'excused')->count();

        return [
            'total_records' => $totalRecords,
            'present_count' => $presentCount,
            'absent_count' => $absentCount,
            'late_count' => $lateCount,
            'excused_count' => $excusedCount,
            'present_percentage' => $totalRecords > 0 ? round(($presentCount / $totalRecords) * 100, 2) : 0,
            'absent_percentage' => $totalRecords > 0 ? round(($absentCount / $totalRecords) * 100, 2) : 0,
            'late_percentage' => $totalRecords > 0 ? round(($lateCount / $totalRecords) * 100, 2) : 0,
            'excused_percentage' => $totalRecords > 0 ? round(($excusedCount / $totalRecords) * 100, 2) : 0,
        ];
    }

    public function generateAttendanceReport(array $filters = []): array
    {
        $query = StudentAttendance::with(['student', 'class', 'subject', 'lab', 'timetable']);

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
            'attendance_by_class' => $attendance->groupBy('class.name')
                ->map(function ($classAttendance) {
                    return [
                        'total' => $classAttendance->count(),
                        'present' => $classAttendance->where('status', 'present')->count(),
                        'absent' => $classAttendance->where('status', 'absent')->count(),
                        'late' => $classAttendance->where('status', 'late')->count(),
                        'excused' => $classAttendance->where('status', 'excused')->count(),
                    ];
                }),
            'attendance_by_date' => $attendance->groupBy('date')
                ->map(function ($dateAttendance) {
                    return [
                        'total' => $dateAttendance->count(),
                        'present' => $dateAttendance->where('status', 'present')->count(),
                        'absent' => $dateAttendance->where('status', 'absent')->count(),
                        'late' => $dateAttendance->where('status', 'late')->count(),
                        'excused' => $dateAttendance->where('status', 'excused')->count(),
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

    public function getAttendanceTrends(int $studentId, string $startDate, string $endDate): array
    {
        $attendance = $this->getAttendanceByStudent($studentId, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        $trends = [
            'total_days' => $attendance->count(),
            'present_days' => $attendance->where('status', 'present')->count(),
            'absent_days' => $attendance->where('status', 'absent')->count(),
            'late_days' => $attendance->where('status', 'late')->count(),
            'excused_days' => $attendance->where('status', 'excused')->count(),
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
            'total_students' => $attendance->count(),
            'present_students' => $attendance->where('status', 'present')->count(),
            'absent_students' => $attendance->where('status', 'absent')->count(),
            'late_students' => $attendance->where('status', 'late')->count(),
            'excused_students' => $attendance->where('status', 'excused')->count(),
            'attendance_rate' => $attendance->count() > 0 ? round(($attendance->where('status', 'present')->count() / $attendance->count()) * 100, 2) : 0,
            'student_details' => $attendance->map(function ($record) {
                return [
                    'student_id' => $record->student_id,
                    'student_name' => $record->student->full_name ?? 'Unknown',
                    'status' => $record->status,
                    'remarks' => $record->remarks,
                ];
            })
        ];

        return $summary;
    }

    public function getStudentAttendanceSummary(int $studentId, string $startDate, string $endDate): array
    {
        $attendance = $this->getAttendanceByStudent($studentId, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        $summary = [
            'student_id' => $studentId,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'total_days' => $attendance->count(),
            'present_days' => $attendance->where('status', 'present')->count(),
            'absent_days' => $attendance->where('status', 'absent')->count(),
            'late_days' => $attendance->where('status', 'late')->count(),
            'excused_days' => $attendance->where('status', 'excused')->count(),
            'attendance_rate' => $attendance->count() > 0 ? round(($attendance->where('status', 'present')->count() / $attendance->count()) * 100, 2) : 0,
            'subject_breakdown' => $attendance->groupBy('subject.name')
                ->map(function ($subjectAttendance) {
                    return [
                        'total' => $subjectAttendance->count(),
                        'present' => $subjectAttendance->where('status', 'present')->count(),
                        'absent' => $subjectAttendance->where('status', 'absent')->count(),
                        'late' => $subjectAttendance->where('status', 'late')->count(),
                        'excused' => $subjectAttendance->where('status', 'excused')->count(),
                        'rate' => $subjectAttendance->count() > 0 ? round(($subjectAttendance->where('status', 'present')->count() / $subjectAttendance->count()) * 100, 2) : 0,
                    ];
                })
        ];

        return $summary;
    }
} 