<?php

namespace App\Http\Controllers\Api\ExamsGradings;

use App\Http\Controllers\Controller;
use App\Models\ExamsGradings\ExamType;
use App\Repositories\ExamsGradings\ExamTypeRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ExamTypeController extends Controller
{
    public function __construct(
        private ExamTypeRepositoryInterface $examTypeRepository
    ) {}

    /**
     * Display a listing of exam types.
     * @group Exams & Gradings
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['name', 'weight', 'min_weight', 'max_weight']);
            $examTypes = $this->examTypeRepository->getAllExamTypes($filters);

            return response()->json([
                'success' => true,
                'data' => $examTypes,
                'message' => 'Exam types retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve exam types',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created exam type.
     * @group Exams & Gradings
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100|unique:exam_types,name',
                'description' => 'nullable|string',
                'weight' => 'required|numeric|min:0|max:999.99',
            ]);

            $examType = $this->examTypeRepository->createExamType($validated);

            return response()->json([
                'success' => true,
                'data' => $examType,
                'message' => 'Exam type created successfully'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create exam type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified exam type.
     * @group Exams & Gradings
     */
    public function show(ExamType $examType): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $examType,
                'message' => 'Exam type retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve exam type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified exam type.
     * @group Exams & Gradings
     */
    public function update(Request $request, ExamType $examType): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:100|unique:exam_types,name,' . $examType->id,
                'description' => 'nullable|string',
                'weight' => 'sometimes|required|numeric|min:0|max:999.99',
            ]);

            $updated = $this->examTypeRepository->updateExamType($examType, $validated);

            if ($updated) {
                $examType->refresh();
                return response()->json([
                    'success' => true,
                    'data' => $examType,
                    'message' => 'Exam type updated successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update exam type'
            ], 500);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update exam type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified exam type.
     * @group Exams & Gradings
     */
    public function destroy(ExamType $examType): JsonResponse
    {
        try {
            // Check if exam type is being used by any exams
            if ($examType->exams()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete exam type as it is being used by exams'
                ], 422);
            }

            $deleted = $this->examTypeRepository->deleteExamType($examType);

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Exam type deleted successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete exam type'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete exam type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all exam types for dropdown/select.
     * @group Exams & Gradings
     */
    public function getAll(): JsonResponse
    {
        try {
            $examTypes = $this->examTypeRepository->getAllExamTypes();

            return response()->json([
                'success' => true,
                'data' => $examTypes,
                'message' => 'All exam types retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve exam types',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get exam type statistics.
     * @group Exams & Gradings
     */
    public function statistics(): JsonResponse
    {
        try {
            $statistics = $this->examTypeRepository->getExamTypeStatistics();

            return response()->json([
                'success' => true,
                'data' => $statistics,
                'message' => 'Exam type statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve exam type statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get weighted exam types.
     * @group Exams & Gradings
     */
    public function weighted(): JsonResponse
    {
        try {
            $weightedTypes = $this->examTypeRepository->getWeightedExamTypes();

            return response()->json([
                'success' => true,
                'data' => $weightedTypes,
                'message' => 'Weighted exam types retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve weighted exam types',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 