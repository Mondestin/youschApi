<?php

namespace App\Http\Controllers\Api\AdminAcademics;

use App\Http\Controllers\Controller;
use App\Models\AdminAcademics\Lab;
use App\Models\AdminAcademics\Subject;
use App\Repositories\AdminAcademics\LabRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

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
        $filters = $request->only([
            'subject_id',
            'name',
            'description',
            'schedule'
        ]);
        
        $labs = $this->labRepository->getAllLabs($filters);
        
        return response()->json([
            'success' => true,
            'data' => $labs,
            'message' => 'Labs retrieved successfully'
        ]);
    }

    /**
     * Store a newly created lab.
     * @group Labs
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'subject_id' => 'required|exists:subjects,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'schedule' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $lab = $this->labRepository->createLab($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Lab created successfully',
            'data' => $lab
        ], 201);
    }

    /**
     * Display the specified lab.
     * @group Labs
     */
    public function show(Lab $lab): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $lab->load(['subject'])
        ]);
    }

    /**
     * Update the specified lab.
     * @group Labs
     */
    public function update(Request $request, Lab $lab): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'subject_id' => 'sometimes|exists:subjects,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'schedule' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updated = $this->labRepository->updateLab($lab, $request->all());

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update lab'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lab updated successfully',
            'data' => $lab->fresh()->load(['subject'])
        ]);
    }

    /**
     * Remove the specified lab.
     * @group Labs
     */
    public function destroy(Lab $lab): JsonResponse
    {
        $deleted = $this->labRepository->deleteLab($lab);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete lab'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lab deleted successfully'
        ]);
    }

    /**
     * Get labs by subject.
     * @group Labs
     */
    public function bySubject(Subject $subject): JsonResponse
    {
        $labs = $this->labRepository->getLabsBySubject($subject->id);

        return response()->json([
            'success' => true,
            'data' => $labs
        ]);
    }

    /**
     * Bulk import labs.
     * @group Labs
     */
    public function bulkImport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'labs' => 'required|array|min:1',
            'labs.*.subject_id' => 'required|exists:subjects,id',
            'labs.*.name' => 'required|string|max:255',
            'labs.*.description' => 'nullable|string|max:1000',
            'labs.*.schedule' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $results = $this->labRepository->bulkImportLabs($request->labs);

        return response()->json([
            'success' => true,
            'message' => 'Bulk import completed',
            'data' => $results
        ]);
    }

    /**
     * Bulk export labs.
     * @group Labs
     */
    public function bulkExport(Request $request): JsonResponse
    {
        $filters = $request->only([
            'subject_id',
            'name',
            'description',
            'schedule'
        ]);
        
        $labs = $this->labRepository->getAllLabs($filters);

        return response()->json([
            'success' => true,
            'data' => $labs->load(['subject']),
            'export_format' => 'json'
        ]);
    }
}