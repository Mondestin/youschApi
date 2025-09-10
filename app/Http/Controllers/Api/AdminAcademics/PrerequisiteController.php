<?php

namespace App\Http\Controllers\Api\AdminAcademics;

use App\Http\Controllers\Controller;
use App\Models\AdminAcademics\SubjectPrerequisite;
use App\Models\AdminAcademics\Subject;
use App\Repositories\AdminAcademics\PrerequisiteRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PrerequisiteController extends Controller
{
    public function __construct(
        private PrerequisiteRepositoryInterface $prerequisiteRepository
    ) {}

    /**
     * Display a listing of prerequisites.
     * @group Prerequisites
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'subject_id',
            'prerequisite_id'
        ]);
        
        $prerequisites = $this->prerequisiteRepository->getAllPrerequisites($filters);
        
        return response()->json([
            'success' => true,
            'data' => $prerequisites,
            'message' => 'Prerequisites retrieved successfully'
        ]);
    }

    /**
     * Store a newly created prerequisite.
     * @group Prerequisites
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'subject_id' => 'required|exists:subjects,id',
            'prerequisite_id' => 'required|exists:subjects,id|different:subject_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if prerequisite already exists
        $existing = $this->prerequisiteRepository->getPrerequisiteBySubjectAndPrerequisite(
            $request->subject_id, 
            $request->prerequisite_id
        );

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'This prerequisite relationship already exists'
            ], 409);
        }

        // Check for circular dependencies
        $circular = $this->prerequisiteRepository->checkCircularDependency(
            $request->subject_id, 
            $request->prerequisite_id
        );

        if ($circular) {
            return response()->json([
                'success' => false,
                'message' => 'Circular dependency detected. This would create an infinite prerequisite chain.'
            ], 409);
        }

        $prerequisite = $this->prerequisiteRepository->createPrerequisite($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Prerequisite created successfully',
            'data' => $prerequisite
        ], 201);
    }

    /**
     * Display the specified prerequisite.
     * @group Prerequisites
     */
    public function show(SubjectPrerequisite $prerequisite): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $prerequisite->load(['subject.course', 'subject.coordinator', 'prerequisite.course', 'prerequisite.coordinator'])
        ]);
    }

    /**
     * Update the specified prerequisite.
     * @group Prerequisites
     */
    public function update(Request $request, SubjectPrerequisite $prerequisite): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'subject_id' => 'sometimes|exists:subjects,id',
            'prerequisite_id' => 'sometimes|exists:subjects,id|different:subject_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if prerequisite already exists (excluding current one)
        if ($request->has('subject_id') || $request->has('prerequisite_id')) {
            $subjectId = $request->get('subject_id', $prerequisite->subject_id);
            $prerequisiteId = $request->get('prerequisite_id', $prerequisite->prerequisite_id);
            
            $existing = $this->prerequisiteRepository->getPrerequisiteBySubjectAndPrerequisite(
                $subjectId, 
                $prerequisiteId,
                $prerequisite->id
            );

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'This prerequisite relationship already exists'
                ], 409);
            }

            // Check for circular dependencies
            $circular = $this->prerequisiteRepository->checkCircularDependency(
                $subjectId, 
                $prerequisiteId
            );

            if ($circular) {
                return response()->json([
                    'success' => false,
                    'message' => 'Circular dependency detected. This would create an infinite prerequisite chain.'
                ], 409);
            }
        }

        $updated = $this->prerequisiteRepository->updatePrerequisite($prerequisite, $request->all());

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update prerequisite'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Prerequisite updated successfully',
            'data' => $prerequisite->fresh()->load(['subject.course', 'subject.coordinator', 'prerequisite.course', 'prerequisite.coordinator'])
        ]);
    }

    /**
     * Remove the specified prerequisite.
     * @group Prerequisites
     */
    public function destroy(SubjectPrerequisite $prerequisite): JsonResponse
    {
        $deleted = $this->prerequisiteRepository->deletePrerequisite($prerequisite);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete prerequisite'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Prerequisite deleted successfully'
        ]);
    }

    /**
     * Get prerequisites for a specific subject.
     * @group Prerequisites
     */
    public function bySubject(Subject $subject): JsonResponse
    {
        $prerequisites = $this->prerequisiteRepository->getPrerequisitesBySubject($subject->id);

        return response()->json([
            'success' => true,
            'data' => $prerequisites
        ]);
    }

    /**
     * Get subjects that require a specific subject as prerequisite.
     * @group Prerequisites
     */
    public function requiredBy(Subject $subject): JsonResponse
    {
        $subjects = $this->prerequisiteRepository->getSubjectsRequiringPrerequisite($subject->id);

        return response()->json([
            'success' => true,
            'data' => $subjects
        ]);
    }

    /**
     * Bulk import prerequisites.
     * @group Prerequisites
     */
    public function bulkImport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'prerequisites' => 'required|array|min:1',
            'prerequisites.*.subject_id' => 'required|exists:subjects,id',
            'prerequisites.*.prerequisite_id' => 'required|exists:subjects,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $results = $this->prerequisiteRepository->bulkImportPrerequisites($request->prerequisites);

        return response()->json([
            'success' => true,
            'message' => 'Bulk import completed',
            'data' => $results
        ]);
    }

    /**
     * Bulk export prerequisites.
     * @group Prerequisites
     */
    public function bulkExport(Request $request): JsonResponse
    {
        $filters = $request->only([
            'subject_id',
            'prerequisite_id'
        ]);
        
        $prerequisites = $this->prerequisiteRepository->getAllPrerequisites($filters);

        return response()->json([
            'success' => true,
            'data' => $prerequisites->load(['subject.course', 'subject.coordinator', 'prerequisite.course', 'prerequisite.coordinator']),
            'export_format' => 'json'
        ]);
    }

    /**
     * Get prerequisite chain for a subject.
     * @group Prerequisites
     */
    public function prerequisiteChain(Subject $subject): JsonResponse
    {
        $chain = $this->prerequisiteRepository->getPrerequisiteChain($subject->id);

        return response()->json([
            'success' => true,
            'data' => $chain
        ]);
    }
}