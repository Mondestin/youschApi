<?php

namespace App\Repositories\Teachers;

use App\Models\Teachers\TeacherDocument;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TeacherDocumentRepositoryInterface
{
    public function getPaginatedDocuments(array $filters): LengthAwarePaginator;
    public function getDocumentById(int $id): ?TeacherDocument;
    public function createDocument(array $data): TeacherDocument;
    public function updateDocument(TeacherDocument $document, array $data): bool;
    public function deleteDocument(TeacherDocument $document): bool;
    public function getDocumentsByTeacher(int $teacherId): Collection;
    public function getDocumentsByType(string $type): Collection;
    public function getRecentDocuments(int $days = 30): Collection;
    public function getDocumentStatistics(): array;
} 