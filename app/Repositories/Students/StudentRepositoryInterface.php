<?php

namespace App\Repositories\Students;

use App\Models\Students\Student;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface StudentRepositoryInterface
{
    /**
     * Get paginated students with filters
     */
    public function getPaginatedStudents(array $filters, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get student by ID with relationships
     */
    public function getStudentById(int $id, array $relationships = []): ?Student;

    /**
     * Create a new student
     */
    public function createStudent(array $data): Student;

    /**
     * Update student
     */
    public function updateStudent(Student $student, array $data): bool;

    /**
     * Delete student
     */
    public function deleteStudent(Student $student): bool;

    /**
     * Change student status
     */
    public function changeStudentStatus(Student $student, string $status): bool;

    /**
     * Assign student to class
     */
    public function assignStudentToClass(Student $student, int $classId): bool;

    /**
     * Get students by school
     */
    public function getStudentsBySchool(int $schoolId): Collection;

    /**
     * Get students by campus
     */
    public function getStudentsByCampus(int $campusId): Collection;

    /**
     * Get students by class
     */
    public function getStudentsByClass(int $classId): Collection;

    /**
     * Get students by status
     */
    public function getStudentsByStatus(string $status): Collection;

    /**
     * Search students
     */
    public function searchStudents(string $searchTerm): Collection;

    /**
     * Get student statistics
     */
    public function getStudentStatistics(array $filters = []): array;

    /**
     * Check if student has related records
     */
    public function hasRelatedRecords(Student $student): array;

    /**
     * Get student academic performance
     */
    public function getStudentAcademicPerformance(Student $student): array;
} 