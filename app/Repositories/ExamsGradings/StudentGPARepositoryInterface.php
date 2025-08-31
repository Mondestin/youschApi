<?php

namespace App\Repositories\ExamsGradings;

use App\Models\ExamsGradings\StudentGPA;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface StudentGPARepositoryInterface
{
    public function getPaginatedStudentGPAs(array $filters): LengthAwarePaginator;
    public function getAllStudentGPAs(array $filters): Collection;
    public function getStudentGPAById(int $id): ?StudentGPA;
    public function createStudentGPA(array $data): StudentGPA;
    public function updateStudentGPA(StudentGPA $studentGPA, array $data): bool;
    public function deleteStudentGPA(StudentGPA $studentGPA): bool;
    public function getStudentGPAByStudent(int $studentId): Collection;
    public function getStudentGPAByTerm(int $termId): Collection;
    public function getStudentGPAByAcademicYear(int $academicYearId): Collection;
    public function getStudentGPAByStudentAndTerm(int $studentId, int $termId): ?StudentGPA;
    public function getStudentGPAByStudentAndAcademicYear(int $studentId, int $academicYearId): ?StudentGPA;
    public function calculateStudentGPA(int $studentId, int $termId, int $academicYearId): float;
    public function calculateStudentCGPA(int $studentId, int $academicYearId): float;
    public function getTopPerformers(int $termId, int $academicYearId, int $limit = 10): Collection;
    public function getLowPerformers(int $termId, int $academicYearId, int $limit = 10): Collection;
    public function getGPADistribution(int $termId, int $academicYearId): array;
    public function getGPAStatistics(array $filters = []): array;
} 