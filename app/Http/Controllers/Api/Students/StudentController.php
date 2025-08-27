<?php

namespace App\Http\Controllers\Api\Students;

use App\Http\Controllers\Controller;
use App\Models\Students\Student;
use App\Repositories\Students\StudentRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    protected $studentRepository;

    public function __construct(StudentRepository $studentRepository)
    {
        $this->studentRepository = $studentRepository;
    }

    /**
     * Display a listing of students.
     * @group Students
    */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'status', 'school_id', 'campus_id', 'class_id', 'gender', 
                'enrollment_date_from', 'enrollment_date_to', 'search'
            ]);

            $students = $this->studentRepository->getPaginatedStudents($filters);

            Log::info('Students retrieved successfully', [
                'count' => $students->count(),
                'filters' => $filters
            ]);

            return response()->json([
                'success' => true,
                'data' => $students->items(),
                'pagination' => [
                    'current_page' => $students->currentPage(),
                    'last_page' => $students->lastPage(),
                    'per_page' => $students->perPage(),
                    'total' => $students->total(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve students', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve students'
            ], 500);
        }
    }

    /**
     * Store a newly created student.
     * @group Students
    */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'school_id' => 'required|exists:schools,id',
                'campus_id' => 'required|exists:campuses,id',
                'class_id' => 'nullable|exists:classes,id',
                'student_number' => 'required|string|max:50|unique:students',
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'dob' => 'required|date|before:today',
                'gender' => 'required|in:male,female,other',
                'email' => 'nullable|email|max:255|unique:students',
                'phone' => 'nullable|string|max:20',
                'parent_name' => 'nullable|string|max:255',
                'parent_email' => 'nullable|email|max:255',
                'parent_phone' => 'nullable|string|max:20',
                'enrollment_date' => 'required|date',
                'status' => 'sometimes|in:active,graduated,transferred,suspended,inactive',
                'profile_picture' => 'nullable|string|max:255',
            ]);

            // Check if email is already used by another student
            if ($request->filled('email')) {
                if ($this->studentRepository->isEmailRegistered($request->email)) {
                    Log::warning('Student creation rejected - email already registered', [
                        'email' => $request->email,
                        'input' => $request->only(['first_name', 'last_name', 'school_id', 'campus_id'])
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Email is already registered'
                    ], 422);
                }
            }

            $student = $this->studentRepository->createStudent($validated);

            Log::info('Student created successfully', [
                'student_id' => $student->id,
                'student_name' => $student->first_name . ' ' . $student->last_name,
                'student_number' => $student->student_number,
                'school_id' => $student->school_id,
                'campus_id' => $student->campus_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Student created successfully',
                'data' => $student->load(['school', 'campus', 'classRoom'])
            ], 201);

        } catch (ValidationException $e) {
            Log::warning('Student creation validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to create student', [
                'error' => $e->getMessage(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to create student'
            ], 500);
        }
    }

    /**
     * Display the specified student.
     * @group Students
    */
    public function show(Student $student): JsonResponse
    {
        try {
            $student = $this->studentRepository->getStudentById($student->id, ['school', 'campus', 'classRoom', 'academicHistory', 'documents']);

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found'
                ], 404);
            }

            Log::info('Student retrieved successfully', [
                'student_id' => $student->id,
                'student_name' => $student->first_name . ' ' . $student->last_name,
                'student_number' => $student->student_number
            ]);

            return response()->json([
                'success' => true,
                'data' => $student
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve student', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve student'
            ], 500);
        }
    }

    /**
     * Update the specified student.
     * @group Students
    */
    public function update(Request $request, Student $student): JsonResponse
    {
        try {
            $validated = $request->validate([
                'class_id' => 'sometimes|nullable|exists:classes,id',
                'first_name' => 'sometimes|required|string|max:100',
                'last_name' => 'sometimes|required|string|max:100',
                'dob' => 'sometimes|required|date|before:today',
                'gender' => 'sometimes|required|in:male,female,other',
                'email' => 'nullable|email|max:255|unique:students,email,' . $student->id,
                'phone' => 'nullable|string|max:20',
                'parent_name' => 'nullable|string|max:255',
                'parent_email' => 'nullable|email|max:255',
                'parent_phone' => 'nullable|string|max:20',
                'enrollment_date' => 'sometimes|required|date',
                'status' => 'sometimes|in:active,graduated,transferred,suspended,inactive',
                'profile_picture' => 'nullable|string|max:255',
            ]);

            $this->studentRepository->updateStudent($student, $validated);

            Log::info('Student updated successfully', [
                'student_id' => $student->id,
                'student_name' => $student->first_name . ' ' . $student->last_name,
                'changes' => $validated
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Student updated successfully',
                'data' => $this->studentRepository->getStudentById($student->id, ['school', 'campus', 'classRoom'])
            ]);

        } catch (ValidationException $e) {
            Log::warning('Student update validation failed', [
                'student_id' => $student->id,
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update student', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to update student'
            ], 500);
        }
    }

    /**
     * Remove the specified student.
     * @group Students
    */
    public function destroy(Student $student): JsonResponse
    {
        try {
            // Check if student has related records
            $relatedRecords = $this->studentRepository->hasRelatedRecords($student);

            if ($relatedRecords['has_enrollments'] || $relatedRecords['has_grades'] || $relatedRecords['has_academic_history'] || $relatedRecords['has_documents']) {
                Log::warning('Attempted to delete student with related records', [
                    'student_id' => $student->id,
                    'related_records' => $relatedRecords
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete student with related records'
                ], 422);
            }

            $studentId = $student->id;
            $studentName = $student->first_name . ' ' . $student->last_name;
            $studentNumber = $student->student_number;
            
            $this->studentRepository->deleteStudent($student);

            Log::info('Student deleted successfully', [
                'student_id' => $studentId,
                'student_name' => $studentName,
                'student_number' => $studentNumber
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Student deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete student', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to delete student'
            ], 500);
        }
    }

    /**
     * Change student status.
     * @group Students
    */
    public function changeStatus(Request $request, Student $student): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:active,graduated,transferred,suspended,inactive',
            ]);

            $oldStatus = $student->status;
            $this->studentRepository->changeStudentStatus($student, $validated['status']);

            Log::info('Student status changed successfully', [
                'student_id' => $student->id,
                'student_name' => $student->first_name . ' ' . $student->last_name,
                'old_status' => $oldStatus,
                'new_status' => $validated['status']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Student status updated successfully',
                'data' => $this->studentRepository->getStudentById($student->id)
            ]);

        } catch (ValidationException $e) {
            Log::warning('Student status change validation failed', [
                'student_id' => $student->id,
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to change student status', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to update student status'
            ], 500);
        }
    }

    /**
     * Assign student to class.
     * @group Students
    */
    public function assignToClass(Request $request, Student $student): JsonResponse
    {
        try {
            $validated = $request->validate([
                'class_id' => 'required|exists:classes,id',
            ]);

            $oldClassId = $student->class_id;
            $this->studentRepository->assignStudentToClass($student, $validated['class_id']);

            Log::info('Student assigned to class successfully', [
                'student_id' => $student->id,
                'student_name' => $student->first_name . ' ' . $student->last_name,
                'old_class_id' => $oldClassId,
                'new_class_id' => $validated['class_id']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Student assigned to class successfully',
                'data' => $this->studentRepository->getStudentById($student->id, ['classRoom'])
            ]);

        } catch (ValidationException $e) {
            Log::warning('Student class assignment validation failed', [
                'student_id' => $student->id,
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to assign student to class', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to assign student to class'
            ], 500);
        }
    }

    /**
     * Get student academic performance.
     * @group Students
    */
    public function academicPerformance(Student $student): JsonResponse
    {
        try {
            $academicPerformance = $this->studentRepository->getStudentAcademicPerformance($student);

            Log::info('Student academic performance retrieved successfully', [
                'student_id' => $student->id,
                'student_name' => $student->first_name . ' ' . $student->last_name,
                'total_subjects' => $academicPerformance['performance_summary']['total_subjects'],
                'overall_gpa' => $academicPerformance['performance_summary']['overall_gpa']
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'student' => $this->studentRepository->getStudentById($student->id, ['school', 'campus', 'classRoom']),
                    'academic_history' => $academicPerformance['academic_history'],
                    'performance_summary' => $academicPerformance['performance_summary']
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve student academic performance', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve academic performance'
            ], 500);
        }
    }

    /**
     * Get student statistics.
     * @group Students
    */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['school_id', 'campus_id']);
            $statistics = $this->studentRepository->getStudentStatistics($filters);

            Log::info('Student statistics retrieved successfully', [
                'total' => $statistics['total_students'],
                'active' => $statistics['active_students'],
                'graduated' => $statistics['graduated_students'],
                'transferred' => $statistics['transferred_students'],
                'suspended' => $statistics['suspended_students'],
                'inactive' => $statistics['inactive_students'],
                'recent' => $statistics['recent_enrollments']
            ]);

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve student statistics', [
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
     * Bulk import students.
     * @group Students
    */
    public function bulkImport(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
                'school_id' => 'required|exists:schools,id',
                'campus_id' => 'required|exists:campuses,id',
            ]);

            // Process bulk import logic here
            Log::info('Student bulk import initiated', [
                'file' => $request->file('file')->getClientOriginalName(),
                'school_id' => $validated['school_id'],
                'campus_id' => $validated['campus_id']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bulk import initiated successfully'
            ]);

        } catch (ValidationException $e) {
            Log::warning('Student bulk import validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to initiate student bulk import', [
                'error' => $e->getMessage(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to initiate bulk import'
            ], 500);
        }
    }

    /**
     * Bulk export students.
     * @group Students
    */
    public function bulkExport(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'format' => 'required|in:csv,xlsx,pdf',
                'filters' => 'array',
                'school_id' => 'sometimes|exists:schools,id',
                'campus_id' => 'sometimes|exists:campuses,id',
            ]);

            // Process bulk export logic here
            Log::info('Student bulk export initiated', [
                'format' => $validated['format'],
                'filters' => $validated['filters'] ?? []
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bulk export initiated successfully'
            ]);

        } catch (ValidationException $e) {
            Log::warning('Student bulk export validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to initiate student bulk export', [
                'error' => $e->getMessage(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to initiate bulk export'
            ], 500);
        }
    }

    /**
     * Get enrollment trends report.
     * @group Students
    */
    public function enrollmentTrends(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['school_id', 'campus_id', 'date_from', 'date_to']);
            
            // Get enrollment trends logic here
            Log::info('Enrollment trends report retrieved successfully', [
                'filters' => $filters
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'trends' => [],
                    'summary' => []
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve enrollment trends', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve enrollment trends'
            ], 500);
        }
    }

    /**
     * Get student demographics report.
     * @group Students
    */
    public function demographicsReport(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['school_id', 'campus_id']);
            
            // Get demographics logic here
            Log::info('Student demographics report retrieved successfully', [
                'filters' => $filters
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'demographics' => [],
                    'summary' => []
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve student demographics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve demographics report'
            ], 500);
        }
    }
} 