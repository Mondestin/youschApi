<?php

namespace App\Http\Controllers\Api\Students;

use App\Http\Controllers\Controller;
use App\Models\Students\StudentDocument;
use App\Models\Students\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StudentDocumentController extends Controller
{
    /**
     * Display a listing of student documents.
     * @group Students
    */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = StudentDocument::with(['student']);

            // Apply filters
            if ($request->filled('student_id')) {
                $query->where('student_id', $request->student_id);
            }

            if ($request->filled('document_type')) {
                $query->where('document_type', $request->document_type);
            }

            if ($request->filled('uploaded_date_from')) {
                $query->whereDate('uploaded_at', '>=', $request->uploaded_date_from);
            }

            if ($request->filled('uploaded_date_to')) {
                $query->whereDate('uploaded_at', '<=', $request->uploaded_date_to);
            }

            if ($request->filled('mime_type')) {
                $query->where('mime_type', 'like', '%' . $request->mime_type . '%');
            }

            $documents = $query->orderBy('uploaded_at', 'desc')->paginate(15);

            Log::info('Student documents retrieved successfully', [
                'count' => $documents->count(),
                'filters' => $request->only(['student_id', 'document_type', 'uploaded_date_from', 'uploaded_date_to'])
            ]);

            return response()->json([
                'success' => true,
                'data' => $documents->items(),
                'pagination' => [
                    'current_page' => $documents->currentPage(),
                    'last_page' => $documents->lastPage(),
                    'per_page' => $documents->perPage(),
                    'total' => $documents->total(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve student documents', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve document records'
            ], 500);
        }
    }

    /**
     * Store a newly created student document.
     * @group Students
    */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'student_id' => 'required|exists:students,id',
                'document_type' => 'required|string|max:100',
                'document_path' => 'required|string',
                'original_filename' => 'sometimes|string|max:255',
                'mime_type' => 'sometimes|string|max:100',
                'file_size' => 'sometimes|integer|min:0',
            ]);

            $document = StudentDocument::create($validated);

            Log::info('Student document created successfully', [
                'document_id' => $document->id,
                'student_id' => $document->student_id,
                'document_type' => $document->document_type,
                'file_size' => $document->file_size
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document record created successfully',
                'data' => $document->load(['student'])
            ], 201);

        } catch (ValidationException $e) {
            Log::warning('Student document validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to create student document', [
                'error' => $e->getMessage(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to create document record'
            ], 500);
        }
    }

    /**
     * Display the specified student document.
     * @group Students
    */
    public function show(StudentDocument $document): JsonResponse
    {
        try {
            $document->load(['student']);

            Log::info('Student document retrieved successfully', [
                'document_id' => $document->id,
                'student_id' => $document->student_id,
                'document_type' => $document->document_type
            ]);

            return response()->json([
                'success' => true,
                'data' => $document
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve student document', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve document record'
            ], 500);
        }
    }

    /**
     * Update the specified student document.
     * @group Students
    */
    public function update(Request $request, StudentDocument $document): JsonResponse
    {
        try {
            $validated = $request->validate([
                'document_type' => 'sometimes|required|string|max:100',
                'original_filename' => 'sometimes|string|max:255',
                'mime_type' => 'sometimes|string|max:100',
                'file_size' => 'sometimes|integer|min:0',
            ]);

            $document->update($validated);

            Log::info('Student document updated successfully', [
                'document_id' => $document->id,
                'student_id' => $document->student_id,
                'changes' => $validated
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document updated successfully',
                'data' => $document->fresh()->load(['student'])
            ]);

        } catch (ValidationException $e) {
            Log::warning('Student document update validation failed', [
                'document_id' => $document->id,
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update student document', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to update document record'
            ], 500);
        }
    }

    /**
     * Remove the specified student document.
     * @group Students
    */
    public function destroy(StudentDocument $document): JsonResponse
    {
        try {
            $documentId = $document->id;
            $studentId = $document->student_id;
            $documentPath = $document->document_path;
            
            // Delete the physical file if it exists
            if (Storage::disk('public')->exists($documentPath)) {
                Storage::disk('public')->delete($documentPath);
            }
            
            $document->delete();

            Log::info('Student document deleted successfully', [
                'document_id' => $documentId,
                'student_id' => $studentId,
                'file_path' => $documentPath
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete student document', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to delete document record'
            ], 500);
        }
    }

    /**
     * Upload a new document file.
     * @group Students
    */
    public function upload(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'student_id' => 'required|exists:students,id',
                'document_type' => 'required|string|max:100',
                'document' => 'required|file|max:10240', // 10MB max
            ]);

            $file = $request->file('document');
            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getMimeType();
            $fileSize = $file->getSize();

            // Generate unique filename
            $extension = $file->getClientOriginalExtension();
            $filename = Str::uuid() . '.' . $extension;
            $path = config('students.file_upload.storage_path') . '/' . $filename;

            // Store the file
            $file->storeAs(
                config('students.file_upload.storage_path'),
                $filename,
                config('students.file_upload.disk', 'public')
            );

            // Create document record
            $document = StudentDocument::create([
                'student_id' => $validated['student_id'],
                'document_type' => $validated['document_type'],
                'document_path' => $path,
                'original_filename' => $originalName,
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
            ]);

            Log::info('Student document uploaded successfully', [
                'document_id' => $document->id,
                'student_id' => $document->student_id,
                'document_type' => $document->document_type,
                'file_size' => $document->file_size,
                'file_path' => $path
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'data' => $document->load(['student'])
            ], 201);

        } catch (ValidationException $e) {
            Log::warning('Student document upload validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to upload student document', [
                'error' => $e->getMessage(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to upload document'
            ], 500);
        }
    }

    /**
     * Download the specified document.
     * @group Students
    */
    public function download(StudentDocument $document): JsonResponse
    {
        try {
            if (!Storage::disk('public')->exists($document->document_path)) {
                Log::warning('Document file not found for download', [
                    'document_id' => $document->id,
                    'file_path' => $document->document_path
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Document file not found'
                ], 404);
            }

            $filePath = Storage::disk('public')->path($document->document_path);
            $fileName = $document->original_filename ?: basename($document->document_path);

            Log::info('Student document download initiated', [
                'document_id' => $document->id,
                'student_id' => $document->student_id,
                'file_path' => $document->document_path
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'download_url' => Storage::disk('public')->url($document->document_path),
                    'file_name' => $fileName,
                    'mime_type' => $document->mime_type,
                    'file_size' => $document->file_size
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to prepare document download', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to prepare document download'
            ], 500);
        }
    }

    /**
     * Get documents by student.
     * @group Students
    */
    public function byStudent(int $studentId): JsonResponse
    {
        try {
            $documents = StudentDocument::where('student_id', $studentId)
                ->orderBy('uploaded_at', 'desc')
                ->get();

            Log::info('Student documents retrieved by student', [
                'student_id' => $studentId,
                'count' => $documents->count()
            ]);

            return response()->json([
                'success' => true,
                'data' => $documents
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve student documents by student', [
                'student_id' => $studentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve document records'
            ], 500);
        }
    }

    /**
     * Get documents by type.
     * @group Students
    */
    public function byType(string $type): JsonResponse
    {
        try {
            $documents = StudentDocument::where('document_type', $type)
                ->with(['student'])
                ->orderBy('uploaded_at', 'desc')
                ->get();

            Log::info('Student documents retrieved by type', [
                'document_type' => $type,
                'count' => $documents->count()
            ]);

            return response()->json([
                'success' => true,
                'data' => $documents
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve student documents by type', [
                'document_type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve document records'
            ], 500);
        }
    }

    /**
     * Get document statistics.
     * @group Students
    */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $query = StudentDocument::query();

            if ($request->filled('school_id')) {
                $query->whereHas('student', function($q) use ($request) {
                    $q->where('school_id', $request->school_id);
                });
            }

            $total = $query->count();
            $totalSize = $query->sum('file_size');

            // Documents by type
            $byType = $query->selectRaw('document_type, COUNT(*) as count, SUM(file_size) as total_size')
                ->groupBy('document_type')
                ->orderBy('count', 'desc')
                ->get();

            // Recent uploads (last 30 days)
            $recent = $query->where('uploaded_at', '>=', now()->subDays(30))->count();

            // Average file size
            $avgFileSize = $total > 0 ? round($totalSize / $total, 2) : 0;

            Log::info('Document statistics retrieved successfully', [
                'total_documents' => $total,
                'total_size' => $totalSize,
                'recent_uploads' => $recent
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'total_documents' => $total,
                    'total_size_bytes' => $totalSize,
                    'total_size_mb' => round($totalSize / 1024 / 1024, 2),
                    'recent_uploads' => $recent,
                    'average_file_size' => $avgFileSize,
                    'documents_by_type' => $byType,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve document statistics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve statistics'
            ], 500);
        }
    }
} 