<?php

namespace App\Repositories\Teachers;

use App\Models\Teachers\TeacherDocument;
use App\Repositories\BaseRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class TeacherDocumentRepository extends BaseRepository implements TeacherDocumentRepositoryInterface
{
    public function __construct(TeacherDocument $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated documents with filters
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPaginatedDocuments(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['teacher']);

        // Apply filters
        if (isset($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        if (isset($filters['document_type'])) {
            $query->where('document_type', $filters['document_type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get document by ID
     *
     * @param int $id
     * @return TeacherDocument|null
     */
    public function getDocumentById(int $id): ?TeacherDocument
    {
        return $this->model->with(['teacher'])->find($id);
    }

    /**
     * Create a new document
     *
     * @param array $data
     * @return TeacherDocument
     */
    public function createDocument(array $data): TeacherDocument
    {
        $data['status'] = $data['status'] ?? 'pending';
        $data['uploaded_at'] = now();
        
        return $this->model->create($data);
    }

    /**
     * Update document
     *
     * @param TeacherDocument $document
     * @param array $data
     * @return bool
     */
    public function updateDocument(TeacherDocument $document, array $data): bool
    {
        return $document->update($data);
    }

    /**
     * Delete document
     *
     * @param TeacherDocument $document
     * @return bool
     */
    public function deleteDocument(TeacherDocument $document): bool
    {
        return $document->delete();
    }

    /**
     * Get documents by teacher
     *
     * @param int $teacherId
     * @return Collection
     */
    public function getDocumentsByTeacher(int $teacherId): Collection
    {
        return $this->model->with(['teacher'])
            ->where('teacher_id', $teacherId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get documents by type
     *
     * @param string $type
     * @return Collection
     */
    public function getDocumentsByType(string $type): Collection
    {
        return $this->model->with(['teacher'])
            ->where('document_type', $type)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get documents by status
     *
     * @param string $status
     * @return Collection
     */
    public function getDocumentsByStatus(string $status): Collection
    {
        return $this->model->with(['teacher'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get pending documents
     *
     * @return Collection
     */
    public function getPendingDocuments(): Collection
    {
        return $this->model->with(['teacher'])
            ->where('status', 'pending')
            ->orderBy('uploaded_at', 'asc')
            ->get();
    }

    /**
     * Get expired documents
     *
     * @return Collection
     */
    public function getExpiredDocuments(): Collection
    {
        $today = Carbon::today()->toDateString();
        
        return $this->model->with(['teacher'])
            ->where('expiry_date', '<', $today)
            ->where('expiry_date', '!=', null)
            ->orderBy('expiry_date', 'asc')
            ->get();
    }

    /**
     * Get document statistics
     *
     * @return array
     */
    public function getDocumentStatistics(): array
    {
        $totalDocuments = $this->model->count();
        $pendingDocuments = $this->model->where('status', 'pending')->count();
        $approvedDocuments = $this->model->where('status', 'approved')->count();
        $rejectedDocuments = $this->model->where('status', 'rejected')->count();
        $expiredDocuments = $this->model->where('expiry_date', '<', now())->count();

        $documentTypes = $this->model->selectRaw('document_type, COUNT(*) as count')
            ->groupBy('document_type')
            ->pluck('count', 'document_type')
            ->toArray();

        $monthlyStats = $this->model->selectRaw('MONTH(uploaded_at) as month, COUNT(*) as count')
            ->whereYear('uploaded_at', date('Y'))
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $totalFileSize = $this->model->sum('file_size');

        return [
            'total_documents' => $totalDocuments,
            'pending_documents' => $pendingDocuments,
            'approved_documents' => $approvedDocuments,
            'rejected_documents' => $rejectedDocuments,
            'expired_documents' => $expiredDocuments,
            'document_types' => $documentTypes,
            'monthly_stats' => $monthlyStats,
            'total_file_size' => $totalFileSize,
            'approval_rate' => $totalDocuments > 0 ? round(($approvedDocuments / $totalDocuments) * 100, 2) : 0
        ];
    }
} 