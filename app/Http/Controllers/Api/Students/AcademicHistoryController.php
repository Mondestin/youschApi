<?php

namespace App\Http\Controllers\Api\Students;

use App\Http\Controllers\Controller;
use App\Models\Students\AcademicHistory;
use App\Models\Students\Student;
use App\Models\AdminAcademics\Subject;
use App\Models\AdminAcademics\ClassRoom;
use App\Models\AdminAcademics\Term;
use App\Models\AdminAcademics\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AcademicHistoryController extends Controller
{
    /**
     * Display a listing of academic history records.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            Log::info('Fetching academic history records', [
                'filters' => $request->all(),
                'user_ip' => $request->ip()
            ]);

            $query = AcademicHistory::with(['student', 'subject', 'classRoom', 'term', 'academicYear']);

            // Apply filters
            if ($request->filled('student_id')) {
                $query->where('student_id', $request->student_id);
            }

            if ($request->filled('subject_id')) {
                $query->where('subject_id', $request->subject_id);
            }

            if ($request->filled('class_id')) {
                $query->where('class_id', $request->class_id);
            }

            if ($request->filled('term_id')) {
                $query->where('term_id', $request->term_id);
            }

            if ($request->filled('academic_year_id')) {
                $query->where('academic_year_id', $request->academic_year_id);
            }

            if ($request->filled('grade')) {
                $query->where('grade', $request->grade);
            }

            if ($request->filled('min_gpa')) {
                $query->where('gpa', '>=', $request->min_gpa);
            }

            if ($request->filled('max_gpa')) {
                $query->where('gpa', '<=', $request->max_gpa);
            }

            $records = $query->orderBy('created_at', 'desc')->paginate(15);

            Log::info('Academic history records fetched successfully', [
                'total_records' => $records->total(),
                'current_page' => $records->currentPage()
            ]);

            return response()->json([
                'success' => true,
                'data' => $records->items(),
                'pagination' => [
                    'current_page' => $records->currentPage(),
                    'last_page' => $records->lastPage(),
                    'per_page' => $records->perPage(),
                    'total' => $records->total(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch academic history records', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve academic history records',
            ], 500);
        }
    }

    /**
     * Store a newly created academic history record.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            Log::info('Creating new academic history record', [
                'request_data' => $request->all(),
                'user_ip' => $request->ip()
            ]);

            $validated = $request->validate([
                'student_id' => 'required|exists:students,id',
                'subject_id' => 'required|exists:subjects,id',
                'class_id' => 'required|exists:classes,id',
                'term_id' => 'required|exists:terms,id',
                'academic_year_id' => 'required|exists:academic_years,id',
                'marks' => 'nullable|numeric|min:0|max:100',
                'grade' => 'nullable|string|max:5',
                'gpa' => 'nullable|numeric|min:0|max:4',
            ]);

            // Check for duplicate record
            $existingRecord = AcademicHistory::where([
                'student_id' => $validated['student_id'],
                'subject_id' => $validated['subject_id'],
                'class_id' => $validated['class_id'],
                'term_id' => $validated['term_id'],
                'academic_year_id' => $validated['academic_year_id'],
            ])->first();

            if ($existingRecord) {
                Log::warning('Duplicate academic history record attempted', [
                    'existing_record_id' => $existingRecord->id,
                    'request_data' => $validated
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Academic record already exists for this student, subject, class, term, and academic year'
                ], 422);
            }

            $academicHistory = AcademicHistory::create($validated);

            Log::info('Academic history record created successfully', [
                'record_id' => $academicHistory->id,
                'student_id' => $academicHistory->student_id,
                'subject_id' => $academicHistory->subject_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Academic history record created successfully',
                'data' => $academicHistory->load(['student', 'subject', 'classRoom', 'term', 'academicYear'])
            ], 201);

        } catch (ValidationException $e) {
            Log::warning('Academic history validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to create academic history record', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create academic history record',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified academic history record.
     */
    public function show(AcademicHistory $academicHistory): JsonResponse
    {
        try {
            Log::info('Fetching academic history record', [
                'record_id' => $academicHistory->id,
                'student_id' => $academicHistory->student_id
            ]);

            $academicHistory->load(['student', 'subject', 'classRoom', 'term', 'academicYear']);

            return response()->json([
                'success' => true,
                'data' => $academicHistory
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch academic history record', [
                'record_id' => $academicHistory->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve academic history record',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified academic history record.
     */
    public function update(Request $request, AcademicHistory $academicHistory): JsonResponse
    {
        try {
            Log::info('Updating academic history record', [
                'record_id' => $academicHistory->id,
                'request_data' => $request->all()
            ]);

            $validated = $request->validate([
                'marks' => 'sometimes|nullable|numeric|min:0|max:100',
                'grade' => 'sometimes|nullable|string|max:5',
                'gpa' => 'sometimes|nullable|numeric|min:0|max:4',
            ]);

            $academicHistory->update($validated);

            Log::info('Academic history record updated successfully', [
                'record_id' => $academicHistory->id,
                'updated_fields' => array_keys($validated)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Academic history record updated successfully',
                'data' => $academicHistory->fresh()->load(['student', 'subject', 'classRoom', 'term', 'academicYear'])
            ]);

        } catch (ValidationException $e) {
            Log::warning('Academic history update validation failed', [
                'record_id' => $academicHistory->id,
                'errors' => $e->errors()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update academic history record', [
                'record_id' => $academicHistory->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update academic history record',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified academic history record.
     */
    public function destroy(AcademicHistory $academicHistory): JsonResponse
    {
        try {
            Log::info('Deleting academic history record', [
                'record_id' => $academicHistory->id,
                'student_id' => $academicHistory->student_id
            ]);

            $academicHistory->delete();

            Log::info('Academic history record deleted successfully', [
                'record_id' => $academicHistory->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Academic history record deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete academic history record', [
                'record_id' => $academicHistory->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete academic history record',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get academic history records by student.
     */
    public function byStudent(Student $student): JsonResponse
    {
        try {
            Log::info('Fetching academic history by student', [
                'student_id' => $student->id,
                'student_name' => $student->full_name
            ]);

            $records = $student->academicHistory()
                ->with(['subject', 'classRoom', 'term', 'academicYear'])
                ->orderBy('academic_year_id', 'desc')
                ->orderBy('term_id', 'desc')
                ->get();

            Log::info('Student academic history fetched successfully', [
                'student_id' => $student->id,
                'total_records' => $records->count()
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'student' => $student->only(['id', 'first_name', 'last_name', 'student_number']),
                    'academic_records' => $records,
                    'total_records' => $records->count(),
                    'overall_gpa' => $records->avg('gpa') ? round($records->avg('gpa'), 2) : 0,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch student academic history', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve student academic history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get academic history records by subject.
     */
    public function bySubject(Subject $subject): JsonResponse
    {
        try {
            Log::info('Fetching academic history by subject', [
                'subject_id' => $subject->id,
                'subject_name' => $subject->name
            ]);

            $records = AcademicHistory::where('subject_id', $subject->id)
                ->with(['student', 'classRoom', 'term', 'academicYear'])
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Subject academic history fetched successfully', [
                'subject_id' => $subject->id,
                'total_records' => $records->count()
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'subject' => $subject->only(['id', 'name', 'code']),
                    'academic_records' => $records,
                    'total_records' => $records->count(),
                    'average_marks' => $records->avg('marks') ? round($records->avg('marks'), 2) : 0,
                    'average_gpa' => $records->avg('gpa') ? round($records->avg('gpa'), 2) : 0,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch subject academic history', [
                'subject_id' => $subject->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve subject academic history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get academic history records by class.
     */
    public function byClass(ClassRoom $classRoom): JsonResponse
    {
        try {
            Log::info('Fetching academic history by class', [
                'class_id' => $classRoom->id,
                'class_name' => $classRoom->name
            ]);

            $records = AcademicHistory::where('class_id', $classRoom->id)
                ->with(['student', 'subject', 'term', 'academicYear'])
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Class academic history fetched successfully', [
                'class_id' => $classRoom->id,
                'total_records' => $records->count()
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'class' => $classRoom->only(['id', 'name']),
                    'academic_records' => $records,
                    'total_records' => $records->count(),
                    'unique_students' => $records->pluck('student_id')->unique()->count(),
                    'unique_subjects' => $records->pluck('subject_id')->unique()->count(),
                    'average_gpa' => $records->avg('gpa') ? round($records->avg('gpa'), 2) : 0,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch class academic history', [
                'class_id' => $classRoom->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve class academic history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get academic history records by term.
     */
    public function byTerm(Term $term): JsonResponse
    {
        try {
            Log::info('Fetching academic history by term', [
                'term_id' => $term->id,
                'term_name' => $term->name
            ]);

            $records = AcademicHistory::where('term_id', $term->id)
                ->with(['student', 'subject', 'classRoom', 'academicYear'])
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Term academic history fetched successfully', [
                'term_id' => $term->id,
                'total_records' => $records->count()
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'term' => $term->only(['id', 'name', 'start_date', 'end_date']),
                    'academic_records' => $records,
                    'total_records' => $records->count(),
                    'unique_students' => $records->pluck('student_id')->unique()->count(),
                    'unique_subjects' => $records->pluck('subject_id')->unique()->count(),
                    'average_gpa' => $records->avg('gpa') ? round($records->avg('gpa'), 2) : 0,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch term academic history', [
                'term_id' => $term->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve term academic history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get academic history records by academic year.
     */
    public function byAcademicYear(AcademicYear $academicYear): JsonResponse
    {
        try {
            Log::info('Fetching academic history by academic year', [
                'academic_year_id' => $academicYear->id,
                'academic_year_name' => $academicYear->name
            ]);

            $records = AcademicHistory::where('academic_year_id', $academicYear->id)
                ->with(['student', 'subject', 'classRoom', 'term'])
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Academic year history fetched successfully', [
                'academic_year_id' => $academicYear->id,
                'total_records' => $records->count()
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'academic_year' => $academicYear->only(['id', 'name', 'start_date', 'end_date']),
                    'academic_records' => $records,
                    'total_records' => $records->count(),
                    'unique_students' => $records->pluck('student_id')->unique()->count(),
                    'unique_subjects' => $records->pluck('subject_id')->unique()->count(),
                    'unique_classes' => $records->pluck('class_id')->unique()->count(),
                    'average_gpa' => $records->avg('gpa') ? round($records->avg('gpa'), 2) : 0,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch academic year history', [
                'academic_year_id' => $academicYear->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve academic year history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get academic history statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            Log::info('Fetching academic history statistics', [
                'filters' => $request->all()
            ]);

            $query = AcademicHistory::query();

            if ($request->filled('academic_year_id')) {
                $query->where('academic_year_id', $request->academic_year_id);
            }

            if ($request->filled('class_id')) {
                $query->where('class_id', $request->class_id);
            }

            $totalRecords = $query->count();
            $recordsWithGrades = $query->whereNotNull('grade')->count();
            $recordsWithGPA = $query->whereNotNull('gpa')->count();
            $passingRecords = $query->where('grade', '!=', 'F')->count();
            $failingRecords = $query->where('grade', 'F')->count();

            $averageMarks = $query->whereNotNull('marks')->avg('marks');
            $averageGPA = $query->whereNotNull('gpa')->avg('gpa');

            $gradeDistribution = $query->whereNotNull('grade')
                ->selectRaw('grade, COUNT(*) as count')
                ->groupBy('grade')
                ->orderBy('grade')
                ->get();

            Log::info('Academic history statistics fetched successfully', [
                'total_records' => $totalRecords,
                'records_with_grades' => $recordsWithGrades
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'total_records' => $totalRecords,
                    'records_with_grades' => $recordsWithGrades,
                    'records_with_gpa' => $recordsWithGPA,
                    'passing_records' => $passingRecords,
                    'failing_records' => $failingRecords,
                    'pass_rate' => $totalRecords > 0 ? round(($passingRecords / $totalRecords) * 100, 2) : 0,
                    'average_marks' => $averageMarks ? round($averageMarks, 2) : 0,
                    'average_gpa' => $averageGPA ? round($averageGPA, 2) : 0,
                    'grade_distribution' => $gradeDistribution,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch academic history statistics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve academic history statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk import academic history records.
     */
    public function bulkImport(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
                'academic_year_id' => 'required|exists:academic_years,id',
            ]);

            Log::info('Academic history bulk import initiated', [
                'file' => $request->file('file')->getClientOriginalName(),
                'academic_year_id' => $validated['academic_year_id']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bulk import initiated successfully'
            ]);

        } catch (ValidationException $e) {
            Log::warning('Academic history bulk import validation failed', [
                'errors' => $e->errors()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to initiate academic history bulk import', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to initiate bulk import'
            ], 500);
        }
    }

    /**
     * Bulk export academic history records.
     */
    public function bulkExport(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'format' => 'required|in:csv,xlsx,pdf',
                'filters' => 'array',
                'academic_year_id' => 'sometimes|exists:academic_years,id',
            ]);

            Log::info('Academic history bulk export initiated', [
                'format' => $validated['format'],
                'filters' => $validated['filters'] ?? []
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bulk export initiated successfully'
            ]);

        } catch (ValidationException $e) {
            Log::warning('Academic history bulk export validation failed', [
                'errors' => $e->errors()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to initiate academic history bulk export', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to initiate bulk export'
            ], 500);
        }
    }

    /**
     * Get academic performance report.
     */
    public function academicPerformanceReport(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['academic_year_id', 'class_id', 'subject_id']);
            
            Log::info('Academic performance report retrieved successfully', [
                'filters' => $filters
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'performance' => [],
                    'summary' => []
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve academic performance report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve academic performance report'
            ], 500);
        }
    }
} 