<?php

namespace App\Repositories\ExamsGradings;

use App\Models\ExamsGradings\ReportCard;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ReportCardRepositoryInterface
{
    public function getPaginatedReportCards(array $filters): LengthAwarePaginator;
    public function getAllReportCards(array $filters): Collection;
    public function getReportCardById(int $id): ?ReportCard;
    public function createReportCard(array $data): ReportCard;
    public function updateReportCard(ReportCard $reportCard, array $data): bool;
    public function deleteReportCard(ReportCard $reportCard): bool;
    public function getReportCardsByStudent(int $studentId): Collection;
    public function getReportCardsByClass(int $classId): Collection;
    public function getReportCardsByTerm(int $termId): Collection;
    public function getReportCardsByAcademicYear(int $academicYearId): Collection;
    public function getReportCardByStudentAndTerm(int $studentId, int $termId, int $academicYearId): ?ReportCard;
    public function generateReportCard(int $studentId, int $classId, int $termId, int $academicYearId): ReportCard;
    public function generateClassReportCards(int $classId, int $termId, int $academicYearId): array;
    public function exportReportCardToPDF(int $reportCardId): string;
    public function getReportCardStatistics(array $filters = []): array;
    public function getReportCardTrends(int $studentId, int $academicYearId): array;
} 