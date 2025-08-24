<?php

namespace App\Http\Controllers\Api\AdminAcademics;

use App\Http\Controllers\Controller;
use App\Models\AdminAcademics\GradingScheme;
use App\Models\AdminAcademics\School;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class GradingSchemeController extends Controller
{
    /**
     * Display a listing of grading schemes.
     */
    public function index(Request $request): JsonResponse
    {
        $query = GradingScheme::with(['school', 'gradeScales']);

        // Filter by school if provided
        if ($request->has('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Filter by active status if provided
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $gradingSchemes = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $gradingSchemes,
            'message' => 'Grading schemes retrieved successfully'
        ]);
    }

    /**
     * Store a newly created grading scheme.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'school_id' => 'required|exists:schools,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'passing_percentage' => 'required|numeric|min:0|max:100',
                'is_active' => 'boolean',
                'grade_scales' => 'required|array|min:1',
                'grade_scales.*.grade' => 'required|string|max:10',
                'grade_scales.*.min_percentage' => 'required|numeric|min:0|max:100',
                'grade_scales.*.max_percentage' => 'required|numeric|min:0|max:100',
                'grade_scales.*.grade_point' => 'required|numeric|min:0|max:10',
                'grade_scales.*.description' => 'nullable|string',
            ]);

            DB::transaction(function() use ($validated) {
                $gradingScheme = GradingScheme::create([
                    'school_id' => $validated['school_id'],
                    'name' => $validated['name'],
                    'description' => $validated['description'],
                    'passing_percentage' => $validated['passing_percentage'],
                    'is_active' => $validated['is_active'] ?? false,
                ]);

                // Create grade scales
                foreach ($validated['grade_scales'] as $gradeScale) {
                    $gradingScheme->gradeScales()->create($gradeScale);
                }
            });

            $gradingScheme = GradingScheme::with(['school', 'gradeScales'])
                                        ->where('school_id', $validated['school_id'])
                                        ->where('name', $validated['name'])
                                        ->first();

            return response()->json([
                'success' => true,
                'data' => $gradingScheme,
                'message' => 'Grading scheme created successfully'
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
                'message' => 'Failed to create grading scheme',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified grading scheme.
     */
    public function show(GradingScheme $gradingScheme): JsonResponse
    {
        $gradingScheme->load(['school', 'gradeScales']);

        return response()->json([
            'success' => true,
            'data' => $gradingScheme,
            'message' => 'Grading scheme retrieved successfully'
        ]);
    }

    /**
     * Update the specified grading scheme.
     */
    public function update(Request $request, GradingScheme $gradingScheme): JsonResponse
    {
        try {
            $validated = $request->validate([
                'school_id' => 'sometimes|required|exists:schools,id',
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'passing_percentage' => 'sometimes|required|numeric|min:0|max:100',
                'is_active' => 'sometimes|boolean',
                'grade_scales' => 'sometimes|array|min:1',
                'grade_scales.*.grade' => 'required_with:grade_scales|string|max:10',
                'grade_scales.*.min_percentage' => 'required_with:grade_scales|numeric|min:0|max:100',
                'grade_scales.*.max_percentage' => 'required_with:grade_scales|numeric|min:0|max:100',
                'grade_scales.*.grade_point' => 'required_with:grade_scales|numeric|min:0|max:10',
                'grade_scales.*.description' => 'nullable|string',
            ]);

            DB::transaction(function() use ($validated, $gradingScheme) {
                $gradingScheme->update([
                    'school_id' => $validated['school_id'] ?? $gradingScheme->school_id,
                    'name' => $validated['name'] ?? $gradingScheme->name,
                    'description' => $validated['description'] ?? $gradingScheme->description,
                    'passing_percentage' => $validated['passing_percentage'] ?? $gradingScheme->passing_percentage,
                    'is_active' => isset($validated['is_active']) ? $validated['is_active'] : $gradingScheme->is_active,
                ]);

                // Update grade scales if provided
                if (isset($validated['grade_scales'])) {
                    // Delete existing grade scales
                    $gradingScheme->gradeScales()->delete();

                    // Create new grade scales
                    foreach ($validated['grade_scales'] as $gradeScale) {
                        $gradingScheme->gradeScales()->create($gradeScale);
                    }
                }
            });

            return response()->json([
                'success' => true,
                'data' => $gradingScheme->fresh()->load(['school', 'gradeScales']),
                'message' => 'Grading scheme updated successfully'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update grading scheme',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified grading scheme.
     */
    public function destroy(GradingScheme $gradingScheme): JsonResponse
    {
        try {
            // Check if grading scheme is being used by any school
            if ($gradingScheme->school()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete grading scheme that is being used by a school'
                ], 422);
            }

            $gradingScheme->delete();

            return response()->json([
                'success' => true,
                'message' => 'Grading scheme deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete grading scheme',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activate a grading scheme (deactivate others in the same school).
     */
    public function activate(GradingScheme $gradingScheme): JsonResponse
    {
        try {
            DB::transaction(function() use ($gradingScheme) {
                // Deactivate all other grading schemes in the same school
                GradingScheme::where('school_id', $gradingScheme->school_id)
                    ->where('id', '!=', $gradingScheme->id)
                    ->update(['is_active' => false]);

                // Activate the selected grading scheme
                $gradingScheme->update(['is_active' => true]);
            });

            return response()->json([
                'success' => true,
                'data' => $gradingScheme->fresh()->load(['school', 'gradeScales']),
                'message' => 'Grading scheme activated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate grading scheme',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get grading schemes by school.
     */
    public function bySchool(School $school): JsonResponse
    {
        $gradingSchemes = $school->gradingSchemes()
                                ->with(['gradeScales'])
                                ->get();

        return response()->json([
            'success' => true,
            'data' => $gradingSchemes,
            'message' => 'Grading schemes retrieved successfully'
        ]);
    }

    /**
     * Calculate grade based on percentage.
     */
    public function calculateGrade(GradingScheme $gradingScheme, Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'percentage' => 'required|numeric|min:0|max:100',
            ]);

            $percentage = $validated['percentage'];
            $gradeScale = $gradingScheme->gradeScales()
                ->where('min_percentage', '<=', $percentage)
                ->where('max_percentage', '>=', $percentage)
                ->first();

            if (!$gradeScale) {
                return response()->json([
                    'success' => false,
                    'message' => 'No grade scale found for the given percentage'
                ], 404);
            }

            $result = [
                'percentage' => $percentage,
                'grade' => $gradeScale->grade,
                'grade_point' => $gradeScale->grade_point,
                'description' => $gradeScale->description,
                'is_passing' => $percentage >= $gradingScheme->passing_percentage,
            ];

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Grade calculated successfully'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate grade',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get grading scheme statistics.
     */
    public function statistics(GradingScheme $gradingScheme): JsonResponse
    {
        $stats = [
            'total_grade_scales' => $gradingScheme->gradeScales()->count(),
            'passing_percentage' => $gradingScheme->passing_percentage,
            'highest_grade_point' => $gradingScheme->gradeScales()->max('grade_point'),
            'lowest_grade_point' => $gradingScheme->gradeScales()->min('grade_point'),
            'is_active' => $gradingScheme->is_active,
            'school_name' => $gradingScheme->school->name,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Grading scheme statistics retrieved successfully'
        ]);
    }
} 