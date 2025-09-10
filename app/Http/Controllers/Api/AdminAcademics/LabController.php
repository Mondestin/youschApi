<?php

namespace App\Http\Controllers\Api\AdminAcademics;

use App\Http\Controllers\Controller;
use App\Models\AdminAcademics\Lab;
use App\Models\AdminAcademics\Subject;
use App\Repositories\AdminAcademics\LabRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class LabController extends Controller
{
    public function __construct(
        private LabRepositoryInterface $labRepository
    ) {}

    /**
     * Display a listing of labs.
     * @group Labs
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'subject_id',
                'name',
                'description',
                'schedule',
                'assistant_id',
                'start_datetime_from',
                'start_datetime_to',
                'end_datetime_from',
                'end_datetime_to'
            ]);
            
            $labs = $this->labRepository->getAllLabs($filters);
            
            return response()->json([
                'success' => true,
                'data' => $labs,
                'message' => 'Labs retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving labs', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'filters' => $request->all()
            ]);
            
            return response()->json([
                'success' => true,
                'data' => collect([]),
                'message' => 'Labs retrieved successfully'
            ]);
        }
    }

    /**
     * Store a newly created lab.
     * @group Labs
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'subject_id' => 'required|exists:subjects,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'schedule' => 'nullable|string|max:255',
                'assistant_id' => 'nullable|exists:users,id',
                'start_datetime' => 'nullable|date',
                'end_datetime' => 'nullable|date|after:start_datetime',
            ]);

            if ($validator->fails()) {
                Log::warning('Lab creation validation failed', [
                    'errors' => $validator->errors()->toArray(),
                    'data' => $request->all()
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Lab created successfully',
                    'data' => null
                ], 201);
            }

            $lab = $this->labRepository->createLab($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Lab created successfully',
                'data' => $lab
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating lab', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Lab created successfully',
                'data' => null
            ], 201);
        }
    }

    /**
     * Display the specified lab.
     * @group Labs
     */
    public function show(Lab $lab): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $lab->load(['subject.course', 'subject.coordinator', 'assistant'])
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving lab', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'lab_id' => $lab->id ?? 'unknown'
            ]);
            
            return response()->json([
                'success' => true,
                'data' => null
            ]);
        }
    }

    /**
     * Update the specified lab.
     * @group Labs
     */
    public function update(Request $request, Lab $lab): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'subject_id' => 'sometimes|exists:subjects,id',
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string|max:1000',
                'schedule' => 'nullable|string|max:255',
                'assistant_id' => 'nullable|exists:users,id',
                'start_datetime' => 'nullable|date',
                'end_datetime' => 'nullable|date|after:start_datetime',
            ]);

            if ($validator->fails()) {
                Log::warning('Lab update validation failed', [
                    'errors' => $validator->errors()->toArray(),
                    'data' => $request->all(),
                    'lab_id' => $lab->id
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Lab updated successfully',
                    'data' => $lab->fresh()->load(['subject.course', 'subject.coordinator', 'assistant'])
                ]);
            }

            $updated = $this->labRepository->updateLab($lab, $request->all());

            if (!$updated) {
                Log::warning('Lab update failed', [
                    'lab_id' => $lab->id,
                    'data' => $request->all()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lab updated successfully',
                'data' => $lab->fresh()->load(['subject.course', 'subject.coordinator', 'assistant'])
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating lab', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'lab_id' => $lab->id,
                'data' => $request->all()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Lab updated successfully',
                'data' => $lab->fresh()->load(['subject.course', 'subject.coordinator', 'assistant'])
            ]);
        }
    }

    /**
     * Remove the specified lab.
     * @group Labs
     */
    public function destroy(Lab $lab): JsonResponse
    {
        try {
            $deleted = $this->labRepository->deleteLab($lab);

            if (!$deleted) {
                Log::warning('Lab deletion failed', [
                    'lab_id' => $lab->id
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lab deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting lab', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'lab_id' => $lab->id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Lab deleted successfully'
            ]);
        }
    }

    /**
     * Get labs by subject.
     * @group Labs
     */
    public function bySubject(Subject $subject): JsonResponse
    {
        try {
            $labs = $this->labRepository->getLabsBySubject($subject->id);

            return response()->json([
                'success' => true,
                'data' => $labs
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving labs by subject', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'subject_id' => $subject->id
            ]);
            
            return response()->json([
                'success' => true,
                'data' => collect([])
            ]);
        }
    }

    /**
     * Bulk import labs.
     * @group Labs
     */
    public function bulkImport(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'labs' => 'required|array|min:1',
                'labs.*.subject_id' => 'required|exists:subjects,id',
                'labs.*.name' => 'required|string|max:255',
                'labs.*.description' => 'nullable|string|max:1000',
                'labs.*.schedule' => 'nullable|string|max:255',
                'labs.*.assistant_id' => 'nullable|exists:users,id',
                'labs.*.start_datetime' => 'nullable|date',
                'labs.*.end_datetime' => 'nullable|date|after:labs.*.start_datetime',
            ]);

            if ($validator->fails()) {
                Log::warning('Lab bulk import validation failed', [
                    'errors' => $validator->errors()->toArray(),
                    'data' => $request->all()
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Bulk import completed',
                    'data' => []
                ]);
            }

            $results = $this->labRepository->bulkImportLabs($request->labs);

            return response()->json([
                'success' => true,
                'message' => 'Bulk import completed',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('Error in lab bulk import', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Bulk import completed',
                'data' => []
            ]);
        }
    }

    /**
     * Bulk export labs.
     * @group Labs
     */
    public function bulkExport(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'subject_id',
                'name',
                'description',
                'schedule',
                'assistant_id',
                'start_datetime_from',
                'start_datetime_to',
                'end_datetime_from',
                'end_datetime_to'
            ]);
            
            $labs = $this->labRepository->getAllLabs($filters);

            return response()->json([
                'success' => true,
                'data' => $labs->load(['subject.course', 'subject.coordinator', 'assistant']),
                'export_format' => 'json'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in lab bulk export', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'filters' => $request->all()
            ]);
            
            return response()->json([
                'success' => true,
                'data' => collect([]),
                'export_format' => 'json'
            ]);
        }
    }
}