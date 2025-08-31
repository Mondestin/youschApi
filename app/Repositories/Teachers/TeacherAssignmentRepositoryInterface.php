<?php

namespace App\Repositories\Teachers;

use App\Models\Teachers\TeacherAssignment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TeacherAssignmentRepositoryInterface
{
    public function getAssignmentById(int $id): ?TeacherAssignment;
    public function createAssignment(array $data): TeacherAssignment;
    public function updateAssignment(TeacherAssignment $assignment, array $data): bool;
    public function deleteAssignment(TeacherAssignment $assignment): bool;
    
    public function getAssignmentsByTeacher(int $teacherId): Collection;
    public function getAssignmentsByClass(int $classId): Collection;
    public function getAssignmentsBySubject(int $subjectId): Collection;
    public function getAssignmentsByAcademicYear(string $academicYear): Collection;
    public function getAssignmentsByTerm(string $term): Collection;
    public function getActiveAssignments(): Collection;
    public function getAssignmentsByDateRange(string $startDate, string $endDate): Collection;
    
    public function checkAssignmentConflicts(
        int $teacherId, 
        int $classId, 
        int $subjectId, 
        string $academicYear, 
        string $term, 
        string $startDate, 
        string $endDate, 
        ?int $excludeId = null
    ): array;
    
    public function bulkImportAssignments(array $assignments): array;
    public function getAllAssignments(array $filters = []): Collection;
    public function getAssignmentStatistics(): array;
    public function generateAssignmentReport(array $filters): array;
} 