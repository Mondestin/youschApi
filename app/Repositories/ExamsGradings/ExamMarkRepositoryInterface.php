<?php

namespace App\Repositories\ExamsGradings;

use App\Models\ExamsGradings\ExamMark;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ExamMarkRepositoryInterface
{
    public function getPaginatedExamMarks(array $filters): LengthAwarePaginator;
    public function getExamMarkById(int $id): ?ExamMark;
    public function createExamMark(array $data): ExamMark;
    public function updateExamMark(ExamMark $examMark, array $data): bool;
    public function deleteExamMark(ExamMark $examMark): bool;
    public function getExamMarksByExam(int $examId): Collection;
    public function getExamMarksByStudent(int $studentId): Collection;
    public function getExamMarksByExamAndStudent(int $examId, int $studentId): ?ExamMark;
    public function bulkCreateExamMarks(array $examMarksData): array;
    public function bulkUpdateExamMarks(array $examMarksData): array;
    public function getExamMarkStatistics(array $filters = []): array;
    public function getStudentPerformanceInExam(int $examId, int $studentId): array;
    public function getExamResults(int $examId): array;
} 