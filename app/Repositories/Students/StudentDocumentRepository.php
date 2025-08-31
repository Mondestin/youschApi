<?php

namespace App\Repositories\Students;

use App\Models\Students\StudentDocument;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class StudentDocumentRepository extends BaseRepository
{
    public function __construct(StudentDocument $model)
    {
        parent::__construct($model);
    }

    public function getPaginatedDocuments(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['student']);

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (isset($filters['document_type'])) {
            $query->where('document_type', $filters['document_type']);
        }

        if (isset($filters['uploaded_date_from'])) {
            $query->whereDate('uploaded_at', '>=', $filters['uploaded_date_from']);
        }

        if (isset($filters['uploaded_date_to'])) {
            $query->whereDate('uploaded_at', '<=', $filters['uploaded_date_to']);
        }

        if (isset($filters['mime_type'])) {
            $query->where('mime_type', 'like', '%' . $filters['mime_type'] . '%');
        }

        return $query->orderBy('uploaded_at', 'desc')->paginate($perPage);
    }

    public function getDocumentById(int $id, array $relationships = []): ?StudentDocument
    {
        $query = $this->model->where('id', $id);
        
        if (!empty($relationships)) {
            $query->with($relationships);
        }
        
        return $query->first();
    }

    public function createDocument(array $data): StudentDocument
    {
        return $this->model->create($data);
    }

    public function updateDocument(StudentDocument $document, array $data): bool
    {
        return $document->update($data);
    }

    public function deleteDocument(StudentDocument $document): bool
    {
        $documentPath = $document->document_path;
        
        if (Storage::disk('public')->exists($documentPath)) {
            Storage::disk('public')->delete($documentPath);
        }
        
        return $document->delete();
    }

    public function getDocumentsByStudent(int $studentId): Collection
    {
        return $this->model->where('student_id', $studentId)
            ->orderBy('uploaded_at', 'desc')
            ->get();
    }

    public function getDocumentsByType(string $type): Collection
    {
        return $this->model->where('document_type', $type)
            ->with(['student'])
            ->orderBy('uploaded_at', 'desc')
            ->get();
    }

    public function getDocumentStatistics(array $filters = []): array
    {
        $query = $this->model->query();

        if (isset($filters['school_id'])) {
            $query->whereHas('student', function($q) use ($filters) {
                $q->where('school_id', $filters['school_id']);
            });
        }

        $total = $query->count();
        $totalSize = $query->sum('file_size');

        $byType = $query->selectRaw('document_type, COUNT(*) as count, SUM(file_size) as total_size')
            ->groupBy('document_type')
            ->orderBy('count', 'desc')
            ->get();

        $recent = $query->where('uploaded_at', '>=', now()->subDays(30))->count();

        $avgFileSize = $total > 0 ? round($totalSize / $total, 2) : 0;

        return [
            'total_documents' => $total,
            'total_size_bytes' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'recent_uploads' => $recent,
            'average_file_size' => $avgFileSize,
            'documents_by_type' => $byType,
        ];
    }

    public function isFileExists(string $filePath): bool
    {
        return Storage::disk('public')->exists($filePath);
    }

    public function getFileUrl(string $filePath): string
    {
        return Storage::disk('public')->url($filePath);
    }

    public function getDocumentsByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->model->whereBetween('uploaded_at', [$startDate, $endDate])
            ->with(['student'])
            ->orderBy('uploaded_at', 'desc')
            ->get();
    }
} 