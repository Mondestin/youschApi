<?php

namespace App\Repositories\Attendance;

use App\Models\Attendance\StudentAttendance;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface StudentAttendanceRepositoryInterface
{
    public function getPaginatedAttendance(array $filters): LengthAwarePaginator;
    public function getAttendanceById(int $id): ?StudentAttendance;
    public function createAttendance(array $data): StudentAttendance;
    public function updateAttendance(StudentAttendance $attendance, array $data): bool;
    public function deleteAttendance(StudentAttendance $attendance): bool;
    
    public function getAttendanceByStudent(int $studentId, array $filters = []): Collection;
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
    public function getAttendanceTrends(int $studentId, string $startDate, string $endDate): array;
    public function getClassAttendanceSummary(int $classId, string $date): array;
    public function getStudentAttendanceSummary(int $studentId, string $startDate, string $endDate): array;
} 