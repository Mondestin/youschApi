<?php

namespace App\Http\Controllers\Api\AdminAcademics;

use App\Http\Controllers\Controller;
use App\Models\AdminAcademics\Faculty;
use App\Models\AdminAcademics\School;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class FacultyController extends Controller
{
    /**
     * Display a listing of faculties.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Faculty::with(['school', 'departments']);

        // Filter by school if provided
        if ($request->has('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        $faculties = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $faculties,
            'message' => 'Faculties retrieved successfully'
        ]);
    }

    /**
     * Store a newly created faculty.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'school_id' => 'required|exists:schools,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            $faculty = Faculty::create($validated);

            return response()->json([
                'success' => true,
                'data' => $faculty->load(['school', 'departments']),
                'message' => 'Faculty created successfully'
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
                'message' => 'Failed to create faculty',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified faculty.
     */
    public function show(Faculty $faculty): JsonResponse
    {
        $faculty->load(['school', 'departments.head', 'departments.courses']);

        return response()->json([
            'success' => true,
            'data' => $faculty,
            'message' => 'Faculty retrieved successfully'
        ]);
    }

    /**
     * Update the specified faculty.
     */
    public function update(Request $request, Faculty $faculty): JsonResponse
    {
        try {
            $validated = $request->validate([
                'school_id' => 'sometimes|required|exists:schools,id',
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
            ]);

            $faculty->update($validated);

            return response()->json([
                'success' => true,
                'data' => $faculty->fresh()->load(['school', 'departments']),
                'message' => 'Faculty updated successfully'
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
                'message' => 'Failed to update faculty',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified faculty.
     */
    public function destroy(Faculty $faculty): JsonResponse
    {
        try {
            // Check if faculty has any related data
            if ($faculty->departments()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete faculty with related departments. Please remove related records first.'
                ], 422);
            }

            $faculty->delete();

            return response()->json([
                'success' => true,
                'message' => 'Faculty deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete faculty',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get faculties by school.
     */
    public function bySchool(School $school): JsonResponse
    {
        $faculties = $school->faculties()
                           ->with(['departments.courses'])
                           ->get();

        return response()->json([
            'success' => true,
            'data' => $faculties,
            'message' => 'Faculties retrieved successfully'
        ]);
    }
} 