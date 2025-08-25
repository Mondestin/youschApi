<?php

namespace App\Http\Controllers\Api\Students;

use App\Http\Controllers\Controller;
use App\Models\Students\StudentGraduation;
use App\Models\Students\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentGraduationController extends Controller
{
    /**
     * Display a listing of student graduations.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = StudentGraduation::with(['student']);

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('student_id')) {
                $query->where('student_id', $request->student_id);
            }

            if ($request->filled('graduation_date_from')) {
                $query->whereDate('graduation_date', '>=', $request->graduation_date_from);
            }

            if ($request->filled('graduation_date_to')) {
                $query->whereDate('graduation_date', '<=', $request->graduation_date_to);
            }

            if ($request->filled('diploma_number')) {
                $query->where('diploma_number', 'like', '%' . $request->diploma_number . '%');
            }

            $graduations = $query->orderBy('graduation_date', 'desc')->paginate(15);

            Log::info('Student graduations retrieved successfully', [
                'count' => $graduations->count(),
                'filters' => $request->only(['status', 'student_id', 'graduation_date_from', 'graduation_date_to'])
            ]);

            return response()->json([
                'success' => true,
                'data' => $graduations->items(),
                'pagination' => [
                    'current_page' => $graduations->currentPage(),
                    'last_page' => $graduations->lastPage(),
                    'per_page' => $graduations->perPage(),
                    'total' => $graduations->total(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve student graduations', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve graduation records'
            ], 500);
        }
    }

    /**
     * Store a newly created student graduation.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'student_id' => 'required|exists:students,id',
                'graduation_date' => 'required|date',
                'diploma_number' => 'required|string|max:50|unique:student_graduation',
                'status' => 'sometimes|in:pending,issued',
            ]);

            // Check if student already has a graduation record
            $existingGraduation = StudentGraduation::where('student_id', $validated['student_id'])->exists();
            if ($existingGraduation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student already has a graduation record'
                ], 422);
            }

            // Check if student is eligible for graduation (status should be active)
            $student = Student::find($validated['student_id']);
            if ($student->status !== Student::STATUS_ACTIVE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student is not eligible for graduation'
                ], 422);
            }

            $graduation = StudentGraduation::create($validated);

            Log::info('Student graduation created successfully', [
                'graduation_id' => $graduation->id,
                'student_id' => $graduation->student_id,
                'diploma_number' => $graduation->diploma_number,
                'graduation_date' => $graduation->graduation_date
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Graduation record created successfully',
                'data' => $graduation->load(['student'])
            ], 201);

        } catch (ValidationException $e) {
            Log::warning('Student graduation validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to create student graduation', [
                'error' => $e->getMessage(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to create graduation record'
            ], 500);
        }
    }

    /**
     * Display the specified student graduation.
     */
    public function show(StudentGraduation $graduation): JsonResponse
    {
        try {
            $graduation->load(['student']);

            Log::info('Student graduation retrieved successfully', [
                'graduation_id' => $graduation->id,
                'student_id' => $graduation->student_id
            ]);

            return response()->json([
                'success' => true,
                'data' => $graduation
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve student graduation', [
                'graduation_id' => $graduation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve graduation record'
            ], 500);
        }
    }

    /**
     * Update the specified student graduation.
     */
    public function update(Request $request, StudentGraduation $graduation): JsonResponse
    {
        try {
            $validated = $request->validate([
                'graduation_date' => 'sometimes|required|date',
                'diploma_number' => 'sometimes|required|string|max:50|unique:student_graduation,diploma_number,' . $graduation->id,
                'status' => 'sometimes|in:pending,issued',
            ]);

            $graduation->update($validated);

            Log::info('Student graduation updated successfully', [
                'graduation_id' => $graduation->id,
                'student_id' => $graduation->student_id,
                'changes' => $validated
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Graduation updated successfully',
                'data' => $graduation->fresh()->load(['student'])
            ]);

        } catch (ValidationException $e) {
            Log::warning('Student graduation update validation failed', [
                'graduation_id' => $graduation->id,
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update student graduation', [
                'graduation_id' => $graduation->id,
                'error' => $e->getMessage(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to update graduation record'
            ], 500);
        }
    }

    /**
     * Remove the specified student graduation.
     */
    public function destroy(StudentGraduation $graduation): JsonResponse
    {
        try {
            $graduationId = $graduation->id;
            $studentId = $graduation->student_id;
            
            $graduation->delete();

            Log::info('Student graduation deleted successfully', [
                'graduation_id' => $graduationId,
                'student_id' => $studentId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Graduation deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete student graduation', [
                'graduation_id' => $graduation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to delete graduation record'
            ], 500);
        }
    }

    /**
     * Issue the diploma.
     */
    public function issue(Request $request, StudentGraduation $graduation): JsonResponse
    {
        try {
            if (!$graduation->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Diploma is already issued'
                ], 422);
            }

            DB::transaction(function() use ($graduation) {
                // Issue the diploma
                $graduation->issue();

                // Update student status to graduated
                $student = $graduation->student;
                $student->graduate();
            });

            Log::info('Student diploma issued successfully', [
                'graduation_id' => $graduation->id,
                'student_id' => $graduation->student_id,
                'diploma_number' => $graduation->diploma_number
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Diploma issued successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to issue student diploma', [
                'graduation_id' => $graduation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to issue diploma'
            ], 500);
        }
    }

    /**
     * Get graduations by student.
     */
    public function byStudent(int $studentId): JsonResponse
    {
        try {
            $graduations = StudentGraduation::where('student_id', $studentId)
                ->orderBy('graduation_date', 'desc')
                ->get();

            Log::info('Student graduations retrieved by student', [
                'student_id' => $studentId,
                'count' => $graduations->count()
            ]);

            return response()->json([
                'success' => true,
                'data' => $graduations
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve student graduations by student', [
                'student_id' => $studentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve graduation records'
            ], 500);
        }
    }

    /**
     * Get graduations by date range.
     */
    public function byDateRange(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $graduations = StudentGraduation::whereBetween('graduation_date', [
                $validated['start_date'],
                $validated['end_date']
            ])
            ->with(['student'])
            ->orderBy('graduation_date', 'desc')
            ->get();

            Log::info('Student graduations retrieved by date range', [
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'count' => $graduations->count()
            ]);

            return response()->json([
                'success' => true,
                'data' => $graduations
            ]);

        } catch (ValidationException $e) {
            Log::warning('Student graduation date range validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve student graduations by date range', [
                'error' => $e->getMessage(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve graduation records'
            ], 500);
        }
    }

    /**
     * Get graduation statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $query = StudentGraduation::query();

            if ($request->filled('school_id')) {
                $query->whereHas('student', function($q) use ($request) {
                    $q->where('school_id', $request->school_id);
                });
            }

            $total = $query->count();
            $pending = $query->where('status', StudentGraduation::STATUS_PENDING)->count();
            $issued = $query->where('status', StudentGraduation::STATUS_ISSUED)->count();

            // Recent graduations (last 30 days)
            $recent = $query->where('graduation_date', '>=', now()->subDays(30))->count();

            // Graduations by year
            $byYear = $query->selectRaw('YEAR(graduation_date) as year, COUNT(*) as count')
                ->groupBy('year')
                ->orderBy('year', 'desc')
                ->get();

            Log::info('Graduation statistics retrieved successfully', [
                'total' => $total,
                'pending' => $pending,
                'issued' => $issued,
                'recent' => $recent
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'total_graduations' => $total,
                    'pending_graduations' => $pending,
                    'issued_graduations' => $issued,
                    'recent_graduations' => $recent,
                    'issuance_rate' => $total > 0 ? round(($issued / $total) * 100, 2) : 0,
                    'graduations_by_year' => $byYear,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve graduation statistics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve statistics'
            ], 500);
        }
    }

    /**
     * Get graduation analysis report.
     */
    public function graduationAnalysis(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['school_id', 'date_from', 'date_to']);
            
            Log::info('Graduation analysis report retrieved successfully', [
                'filters' => $filters
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'analysis' => [],
                    'summary' => []
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve graduation analysis report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve graduation analysis report'
            ], 500);
        }
    }
} 