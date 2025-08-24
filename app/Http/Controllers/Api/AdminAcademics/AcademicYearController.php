<?php

namespace App\Http\Controllers\Api\AdminAcademics;

use App\Http\Controllers\Controller;
use App\Models\AdminAcademics\AcademicYear;
use App\Models\AdminAcademics\School;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of academic years.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AcademicYear::with(['school', 'terms']);

        // Filter by school if provided
        if ($request->has('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Filter by active status if provided
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $academicYears = $query->orderBy('start_date', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $academicYears,
            'message' => 'Academic years retrieved successfully'
        ]);
    }

    /**
     * Store a newly created academic year.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'school_id' => 'required|exists:schools,id',
                'name' => 'required|string|max:50',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'is_active' => 'boolean',
            ]);

            // Check for overlapping dates with existing academic years
            $overlapping = AcademicYear::where('school_id', $validated['school_id'])
                ->where(function($query) use ($validated) {
                    $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                          ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                          ->orWhere(function($q) use ($validated) {
                              $q->where('start_date', '<=', $validated['start_date'])
                                ->where('end_date', '>=', $validated['end_date']);
                          });
                })
                ->exists();

            if ($overlapping) {
                return response()->json([
                    'success' => false,
                    'message' => 'Academic year dates overlap with existing academic year'
                ], 422);
            }

            $academicYear = AcademicYear::create($validated);

            return response()->json([
                'success' => true,
                'data' => $academicYear->load(['school', 'terms']),
                'message' => 'Academic year created successfully'
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
                'message' => 'Failed to create academic year',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified academic year.
     */
    public function show(AcademicYear $academicYear): JsonResponse
    {
        $academicYear->load([
            'school',
            'terms',
            'studentEnrollments.student',
            'studentGrades.student',
            'teacherAssignments.teacher'
        ]);

        return response()->json([
            'success' => true,
            'data' => $academicYear,
            'message' => 'Academic year retrieved successfully'
        ]);
    }

    /**
     * Update the specified academic year.
     */
    public function update(Request $request, AcademicYear $academicYear): JsonResponse
    {
        try {
            $validated = $request->validate([
                'school_id' => 'sometimes|required|exists:schools,id',
                'name' => 'sometimes|required|string|max:50',
                'start_date' => 'sometimes|required|date',
                'end_date' => 'sometimes|required|date|after:start_date',
                'is_active' => 'sometimes|boolean',
            ]);

            // Check for overlapping dates with other academic years (excluding current)
            if (isset($validated['start_date']) || isset($validated['end_date'])) {
                $startDate = $validated['start_date'] ?? $academicYear->start_date;
                $endDate = $validated['end_date'] ?? $academicYear->end_date;

                $overlapping = AcademicYear::where('school_id', $academicYear->school_id)
                    ->where('id', '!=', $academicYear->id)
                    ->where(function($query) use ($startDate, $endDate) {
                        $query->whereBetween('start_date', [$startDate, $endDate])
                              ->orWhereBetween('end_date', [$startDate, $endDate])
                              ->orWhere(function($q) use ($startDate, $endDate) {
                                  $q->where('start_date', '<=', $startDate)
                                    ->where('end_date', '>=', $endDate);
                              });
                    })
                    ->exists();

                if ($overlapping) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Academic year dates overlap with existing academic year'
                    ], 422);
                }
            }

            $academicYear->update($validated);

            return response()->json([
                'success' => true,
                'data' => $academicYear->fresh()->load(['school', 'terms']),
                'message' => 'Academic year updated successfully'
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
                'message' => 'Failed to update academic year',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified academic year.
     */
    public function destroy(AcademicYear $academicYear): JsonResponse
    {
        try {
            // Check if academic year has any related data
            if ($academicYear->terms()->exists() || $academicYear->studentEnrollments()->exists() || 
                $academicYear->studentGrades()->exists() || $academicYear->teacherAssignments()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete academic year with related data. Please remove related records first.'
                ], 422);
            }

            $academicYear->delete();

            return response()->json([
                'success' => true,
                'message' => 'Academic year deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete academic year',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activate an academic year (deactivate others).
     */
    public function activate(AcademicYear $academicYear): JsonResponse
    {
        try {
            DB::transaction(function() use ($academicYear) {
                // Deactivate all other academic years in the same school
                AcademicYear::where('school_id', $academicYear->school_id)
                    ->where('id', '!=', $academicYear->id)
                    ->update(['is_active' => false]);

                // Activate the selected academic year
                $academicYear->update(['is_active' => true]);
            });

            return response()->json([
                'success' => true,
                'data' => $academicYear->fresh()->load(['school', 'terms']),
                'message' => 'Academic year activated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate academic year',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get academic year statistics.
     */
    public function statistics(AcademicYear $academicYear): JsonResponse
    {
        $stats = [
            'total_terms' => $academicYear->terms()->count(),
            'total_students' => $academicYear->studentEnrollments()->count(),
            'total_teachers' => $academicYear->teacherAssignments()->distinct('teacher_id')->count(),
            'total_classes' => $academicYear->studentEnrollments()->distinct('class_id')->count(),
            'total_subjects' => $academicYear->studentGrades()->distinct('subject_id')->count(),
            'is_current' => $academicYear->start_date <= now() && $academicYear->end_date >= now(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Academic year statistics retrieved successfully'
        ]);
    }
} 