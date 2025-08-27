<?php

namespace App\Http\Controllers\Api\Teachers;

use App\Http\Controllers\Controller;
use App\Models\Teachers\TeacherDocument;
use App\Repositories\Teachers\TeacherDocumentRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class TeacherDocumentController extends Controller
{
    protected $documentRepository;

    public function __construct(TeacherDocumentRepositoryInterface $documentRepository)
    {
        $this->documentRepository = $documentRepository;
    }

    /**
     * Display a paginated list of teacher documents
     *
     * @param Request $request
     * @return JsonResponse
     * @group Teachers
    */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['teacher_id', 'document_type', 'status', 'per_page']);
            $documents = $this->documentRepository->getPaginatedDocuments($filters);
            
            return response()->json([
                'success' => true,
                'data' => $documents,
                'message' => 'Teacher documents retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve teacher documents: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created teacher document
     *
     * @param Request $request
     * @return JsonResponse
     * @group Teachers
    */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'teacher_id' => 'required|exists:teachers,id',
                'document_type' => 'required|in:cv,contract,certificate,id_card,degree,transcript,other',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // 10MB max
                'expiry_date' => 'nullable|date|after:today',
                'is_required' => 'boolean',
                'status' => 'in:pending,approved,rejected,expired'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Handle file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('teacher_documents', $fileName, 'public');
                
                $documentData = $request->except('file');
                $documentData['file_path'] = $filePath;
                $documentData['file_name'] = $fileName;
                $documentData['file_size'] = $file->getSize();
                $documentData['mime_type'] = $file->getMimeType();
                
                $document = $this->documentRepository->createDocument($documentData);
                
                return response()->json([
                    'success' => true,
                    'data' => $document,
                    'message' => 'Document uploaded successfully'
                ], 201);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No file provided'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified teacher document
     *
     * @param int $id
     * @return JsonResponse
     * @group Teachers
    */
    public function show(int $id): JsonResponse
    {
        try {
            $document = $this->documentRepository->getDocumentById($id);
            
            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $document,
                'message' => 'Document retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified teacher document
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @group Teachers
    */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $document = $this->documentRepository->getDocumentById($id);
            
            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'expiry_date' => 'nullable|date|after:today',
                'is_required' => 'boolean',
                'status' => 'in:pending,approved,rejected,expired'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updated = $this->documentRepository->updateDocument($document, $request->all());
            
            if ($updated) {
                $document->refresh();
                return response()->json([
                    'success' => true,
                    'data' => $document,
                    'message' => 'Document updated successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update document'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified teacher document
     *
     * @param int $id
     * @return JsonResponse
     * @group Teachers
    */
    public function destroy(int $id): JsonResponse
    {
        try {
            $document = $this->documentRepository->getDocumentById($id);
            
            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found'
                ], 404);
            }

            // Delete the physical file
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            $deleted = $this->documentRepository->deleteDocument($document);
            
            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Document deleted successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download the specified teacher document
     *
     * @param int $id
     * @return JsonResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     * @group Teachers
    */
    public function download(int $id)
    {
        try {
            $document = $this->documentRepository->getDocumentById($id);
            
            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found'
                ], 404);
            }

            if (!Storage::disk('public')->exists($document->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found on server'
                ], 404);
            }

            return Storage::disk('public')->download(
                $document->file_path,
                $document->file_name
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve a teacher document
     *
     * @param int $id
     * @return JsonResponse
     * @group Teachers
    */
    public function approve(int $id): JsonResponse
    {
        try {
            $document = $this->documentRepository->getDocumentById($id);
            
            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found'
                ], 404);
            }

            if ($document->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Document is not pending approval'
                ], 422);
            }

            $updated = $this->documentRepository->updateDocument($document, ['status' => 'approved']);
            
            if ($updated) {
                $document->refresh();
                return response()->json([
                    'success' => true,
                    'data' => $document,
                    'message' => 'Document approved successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve document'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a teacher document
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @group Teachers
    */
    public function reject(Request $request, int $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'rejection_reason' => 'required|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $document = $this->documentRepository->getDocumentById($id);
            
            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found'
                ], 404);
            }

            if ($document->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Document is not pending approval'
                ], 422);
            }

            $updated = $this->documentRepository->updateDocument($document, [
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason
            ]);
            
            if ($updated) {
                $document->refresh();
                return response()->json([
                    'success' => true,
                    'data' => $document,
                    'message' => 'Document rejected successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject document'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get documents by teacher
     *
     * @param int $teacherId
     * @return JsonResponse
     * @group Teachers
    */
    public function getByTeacher(int $teacherId): JsonResponse
    {
        try {
            $documents = $this->documentRepository->getDocumentsByTeacher($teacherId);
            
            return response()->json([
                'success' => true,
                'data' => $documents,
                'message' => 'Teacher documents retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve teacher documents: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get documents by type
     *
     * @param string $type
     * @return JsonResponse
     * @group Teachers
    */
    public function getByType(string $type): JsonResponse
    {
        try {
            $documents = $this->documentRepository->getDocumentsByType($type);
            
            return response()->json([
                'success' => true,
                'data' => $documents,
                'message' => 'Documents retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve documents: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get documents by status
     *
     * @param string $status
     * @return JsonResponse
     * @group Teachers
    */
    public function getByStatus(string $status): JsonResponse
    {
        try {
            $documents = $this->documentRepository->getDocumentsByStatus($status);
            
            return response()->json([
                'success' => true,
                'data' => $documents,
                'message' => 'Documents retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve documents: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending documents
     *
     * @return JsonResponse
     * @group Teachers
    */
    public function getPending(): JsonResponse
    {
        try {
            $documents = $this->documentRepository->getPendingDocuments();
            
            return response()->json([
                'success' => true,
                'data' => $documents,
                'message' => 'Pending documents retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve pending documents: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get expired documents
     *
     * @return JsonResponse
     * @group Teachers
    */
    public function getExpired(): JsonResponse
    {
        try {
            $documents = $this->documentRepository->getExpiredDocuments();
            
            return response()->json([
                'success' => true,
                'data' => $documents,
                'message' => 'Expired documents retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve expired documents: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get document statistics
     *
     * @return JsonResponse
     * @group Teachers
    */
    public function getStatistics(): JsonResponse
    {
        try {
            $statistics = $this->documentRepository->getDocumentStatistics();
            
            return response()->json([
                'success' => true,
                'data' => $statistics,
                'message' => 'Document statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve document statistics: ' . $e->getMessage()
            ], 500);
        }
    }
} 