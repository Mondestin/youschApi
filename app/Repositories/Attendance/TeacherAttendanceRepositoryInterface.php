<?php

namespace App\Repositories\Attendance;

use App\Models\Attendance\TeacherAttendance;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TeacherAttendanceRepositoryInterface
{
    public function getPaginatedAttendance(array $filters): LengthAwarePaginator;
    public function getAttendanceById(int $id): ?TeacherAttendance;
    public function createAttendance(array $data): TeacherAttendance;
    public function updateAttendance(TeacherAttendance $attendance, array $data): bool;
    public function deleteAttendance(TeacherAttendance $attendance): bool;
    
    public function getAttendanceByTeacher(int $teacherId, array $filters = []): Collection;
    public function getAttendanceByClass(int $classId, array $filters = []): Collection;
    public function getAttendanceBySubject(int $subjectId, array $filters = []): Collection;
    public function getAttendanceByDate(string $date, array $filters = []): Collection;
    public function getAttendanceByDateRange(string $startDate, string $endDate, array $filters = []): Collection;
    public function getAttendanceByStatus(string $status, array $filters = []): Collection;
    public function getAttendanceByTerm(string $term, array $filters = []): Collection;
    public function getAttendanceByAcademicYear(string $academicYear, array $filters = []): Collection;
    
    public function bulkCreateAttendance(array $attendanceData): array;
    public function bulkUpdateAttendance(array $attendanceData): array;
    public function getAttendanceStatistics(array $filters = []): array;
    public function generateAttendanceReport(array $filters = []): array;
    public function getAttendanceTrends(int $teacherId, string $startDate, string $endDate): array;
    public function getClassAttendanceSummary(int $classId, string $date): array;
    public function getTeacherAttendanceSummary(int $teacherId, string $startDate, string $endDate): array;
} 