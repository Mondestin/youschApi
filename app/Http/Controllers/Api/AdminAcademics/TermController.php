<?php

namespace App\Http\Controllers\Api\AdminAcademics;

use App\Http\Controllers\Controller;
use App\Models\AdminAcademics\Term;
use App\Models\AdminAcademics\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class TermController extends Controller
{
    /**
     * Display a listing of terms.
     * @group Admin Academics
    */
    public function index(Request $request): JsonResponse
    {
        $query = Term::with(['academicYear.school']);

        // Filter by academic year if provided
        if ($request->has('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        // Filter by school if provided
        if ($request->has('school_id')) {
            $query->whereHas('academicYear', function($q) use ($request) {
                $q->where('school_id', $request->school_id);
            });
        }

        // Filter by active status if provided
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $terms = $query->orderBy('start_date', 'asc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $terms,
            'message' => 'Terms retrieved successfully'
        ]);
    }

    /**
     * Store a newly created term.
     * @group Admin Academics
    */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'academic_year_id' => 'required|exists:academic_years,id',
                'name' => 'required|string|max:50',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'is_active' => 'boolean',
            ]);

            // Get the academic year to check date boundaries
            $academicYear = AcademicYear::findOrFail($validated['academic_year_id']);

            // Check if term dates are within academic year boundaries
            if ($validated['start_date'] < $academicYear->start_date || $validated['end_date'] > $academicYear->end_date) {
                return response()->json([
                    'success' => false,
                    'message' => 'Term dates must be within the academic year boundaries'
                ], 422);
            }

            // Check for overlapping dates with existing terms in the same academic year
            $overlapping = Term::where('academic_year_id', $validated['academic_year_id'])
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
                    'message' => 'Term dates overlap with existing term in this academic year'
                ], 422);
            }

            $term = Term::create($validated);

            return response()->json([
                'success' => true,
                'data' => $term->load(['academicYear.school']),
                'message' => 'Term created successfully'
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
                'message' => 'Failed to create term',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified term.
     * @group Admin Academics
    */
    public function show(Term $term): JsonResponse
    {
        $term->load([
            'academicYear.school',
            'studentGrades.student',
            'studentGrades.subject'
        ]);

        return response()->json([
            'success' => true,
            'data' => $term,
            'message' => 'Term retrieved successfully'
        ]);
    }

    /**
     * Update the specified term.
     * @group Admin Academics
    */
    public function update(Request $request, Term $term): JsonResponse
    {
        try {
            $validated = $request->validate([
                'academic_year_id' => 'sometimes|required|exists:academic_years,id',
                'name' => 'sometimes|required|string|max:50',
                'start_date' => 'sometimes|required|date',
                'end_date' => 'sometimes|required|date|after:start_date',
                'is_active' => 'sometimes|boolean',
            ]);

            // Get the academic year to check date boundaries
            $academicYearId = $validated['academic_year_id'] ?? $term->academic_year_id;
            $academicYear = AcademicYear::findOrFail($academicYearId);

            // Check if term dates are within academic year boundaries
            if (isset($validated['start_date']) || isset($validated['end_date'])) {
                $startDate = $validated['start_date'] ?? $term->start_date;
                $endDate = $validated['end_date'] ?? $term->end_date;

                if ($startDate < $academicYear->start_date || $endDate > $academicYear->end_date) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Term dates must be within the academic year boundaries'
                    ], 422);
                }

                // Check for overlapping dates with other terms in the same academic year
                $overlapping = Term::where('academic_year_id', $academicYearId)
                    ->where('id', '!=', $term->id)
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
                        'message' => 'Term dates overlap with existing term in this academic year'
                    ], 422);
                }
            }

            $term->update($validated);

            return response()->json([
                'success' => true,
                'data' => $term->fresh()->load(['academicYear.school']),
                'message' => 'Term updated successfully'
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
                'message' => 'Failed to update term',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified term.
     * @group Admin Academics
    */
    public function destroy(Term $term): JsonResponse
    {
        try {
            // Check if term has any related data
            if ($term->studentGrades()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete term with related student grades. Please remove related records first.'
                ], 422);
            }

            $term->delete();

            return response()->json([
                'success' => true,
                'message' => 'Term deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete term',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activate a term (deactivate others in the same academic year).
     * @group Admin Academics
    */
    public function activate(Term $term): JsonResponse
    {
        try {
            DB::transaction(function() use ($term) {
                // Deactivate all other terms in the same academic year
                Term::where('academic_year_id', $term->academic_year_id)
                    ->where('id', '!=', $term->id)
                    ->update(['is_active' => false]);

                // Activate the selected term
                $term->update(['is_active' => true]);
            });

            return response()->json([
                'success' => true,
                'data' => $term->fresh()->load(['academicYear.school']),
                'message' => 'Term activated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate term',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get terms by academic year.
     * @group Admin Academics
    */
    public function byAcademicYear(AcademicYear $academicYear): JsonResponse
    {
        $terms = $academicYear->terms()
                            ->orderBy('start_date', 'asc')
                            ->get();

        return response()->json([
            'success' => true,
            'data' => $terms,
            'message' => 'Terms retrieved successfully'
        ]);
    }

    /**
     * Get term statistics.
     * @group Admin Academics
    */
    public function statistics(Term $term): JsonResponse
    {
        $stats = [
            'total_students' => $term->studentGrades()->distinct('student_id')->count(),
            'total_subjects' => $term->studentGrades()->distinct('subject_id')->count(),
            'total_grades' => $term->studentGrades()->count(),
            'is_current' => $term->start_date <= now() && $term->end_date >= now(),
            'days_remaining' => max(0, $term->end_date->diffInDays(now())),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Term statistics retrieved successfully'
        ]);
    }
} 