<?php

namespace App\Http\Controllers\Api\Students;

use App\Http\Controllers\Controller;
use App\Models\Students\StudentApplication;
use App\Repositories\Students\StudentApplicationRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Models\Students\Student;

class StudentApplicationController extends Controller
{
    protected $applicationRepository;

    public function __construct(StudentApplicationRepository $applicationRepository)
    {
        $this->applicationRepository = $applicationRepository;
    }

    /**
     * Display a listing of student applications.
     * @group Students
    */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'status', 'school_id', 'campus_id', 'reviewer_id', 
                'date_from', 'date_to', 'search'
            ]);

            $applications = $this->applicationRepository->getAllApplications($filters);

            Log::info('Student applications retrieved successfully', [
                'count' => $applications->count(),
                'filters' => $filters
            ]);

            return response()->json([
                'success' => true,
                'data' => $applications,
                'message' => 'Student applications retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve student applications', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve applications'
            ], 500);
        }
    }

    /**
     * Store a newly created student application.
     * @group Students
    */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'school_id' => 'required|exists:schools,id',
                'campus_id' => 'required|exists:campuses,id',
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'dob' => 'required|date|before:today',
                'gender' => 'required|in:male,female,other',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'parent_name' => 'nullable|string|max:255',
                'parent_email' => 'nullable|email|max:255',
                'parent_phone' => 'nullable|string|max:20',
            ]);

            // Check if email is already registered
            if ($request->filled('email')) {
                if ($this->applicationRepository->isEmailRegistered($request->email)) {
                    Log::warning('Application creation rejected - email already registered', [
                        'email' => $request->email,
                        'input' => $request->only(['first_name', 'last_name', 'school_id', 'campus_id'])
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Email is already registered'
                    ], 422);
                }
            }

            $application = $this->applicationRepository->createApplication($validated);

            Log::info('Student application created successfully', [
                'application_id' => $application->id,
                'applicant_name' => $application->first_name . ' ' . $application->last_name,
                'school_id' => $application->school_id,
                'campus_id' => $application->campus_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application submitted successfully',
                'data' => $application->load(['school', 'campus'])
            ], 201);

        } catch (ValidationException $e) {
            Log::warning('Application creation validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to create application', [
                'error' => $e->getMessage(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to submit application'
            ], 500);
        }
    }

    /**
     * Display the specified student application.
     * @group Students
    */
    public function show(StudentApplication $application): JsonResponse
    {
        try {
            $application = $this->applicationRepository->getApplicationById($application->id, ['school', 'campus', 'reviewer']);

            if (!$application) {
                return response()->json([
                    'success' => false,
                    'message' => 'Application not found'
                ], 404);
            }

            Log::info('Student application retrieved successfully', [
                'application_id' => $application->id,
                'applicant_name' => $application->first_name . ' ' . $application->last_name
            ]);

            return response()->json([
                'success' => true,
                'data' => $application
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve application', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve application'
            ], 500);
        }
    }

    /**
     * Update the specified student application.
     * @group Students
    */
    public function update(Request $request, StudentApplication $application): JsonResponse
    {
        try {
            // Only allow updates for pending applications
            if (!$application->isPending()) {
                Log::warning('Attempted to update non-pending application', [
                    'application_id' => $application->id,
                    'current_status' => $application->status
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update non-pending application'
                ], 422);
            }

            $validated = $request->validate([
                'first_name' => 'sometimes|required|string|max:100',
                'last_name' => 'sometimes|required|string|max:100',
                'dob' => 'sometimes|required|date|before:today',
                'gender' => 'sometimes|required|in:male,female,other',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'parent_name' => 'nullable|string|max:255',
                'parent_email' => 'nullable|email|max:255',
                'parent_phone' => 'nullable|string|max:20',
            ]);

            $application->update($validated);

            Log::info('Student application updated successfully', [
                'application_id' => $application->id,
                'student_name' => $application->first_name . ' ' . $application->last_name,
                'changes' => $validated
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application updated successfully',
                'data' => $application->fresh()->load(['school', 'campus'])
            ]);

        } catch (ValidationException $e) {
            Log::warning('Student application update validation failed', [
                'application_id' => $application->id,
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update student application', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to update application'
            ], 500);
        }
    }

    /**
     * Remove the specified student application.
     * @group Students
    */
    public function destroy(StudentApplication $application): JsonResponse
    {
        try {
            // Only allow deletion of pending applications
            if (!$application->isPending()) {
                Log::warning('Attempted to delete non-pending application', [
                    'application_id' => $application->id,
                    'current_status' => $application->status
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete non-pending application'
                ], 422);
            }

            $applicationId = $application->id;
            $studentName = $application->first_name . ' ' . $application->last_name;
            
            $application->delete();

            Log::info('Student application deleted successfully', [
                'application_id' => $applicationId,
                'student_name' => $studentName
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete student application', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to delete application'
            ], 500);
        }
    }

    /**
     * Approve a student application.
     * @group Students
    */
    public function approve(Request $request, StudentApplication $application): JsonResponse
    {
        try {
            $validated = $request->validate([
                'reviewer_id' => 'required|exists:users,id',
            ]);

            if ($application->status !== StudentApplication::STATUS_PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => 'Application is not pending approval'
                ], 422);
            }

            $this->applicationRepository->approveApplication($application, $validated['reviewer_id']);

            Log::info('Student application approved successfully', [
                'application_id' => $application->id,
                'applicant_name' => $application->first_name . ' ' . $application->last_name,
                'reviewer_id' => $validated['reviewer_id']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application approved successfully'
            ]);

        } catch (ValidationException $e) {
            Log::warning('Application approval validation failed', [
                'application_id' => $application->id,
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to approve application', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to approve application'
            ], 500);
        }
    }

    /**
     * Reject a student application.
     * @group Students
    */
    public function reject(Request $request, StudentApplication $application): JsonResponse
    {
        try {
            $validated = $request->validate([
                'reviewer_id' => 'required|exists:users,id',
                'rejection_reason' => 'nullable|string|max:500',
            ]);

            if ($application->status !== StudentApplication::STATUS_PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => 'Application is not pending approval'
                ], 422);
            }

            $this->applicationRepository->rejectApplication($application, $validated['reviewer_id']);

            Log::info('Student application rejected successfully', [
                'application_id' => $application->id,
                'applicant_name' => $application->first_name . ' ' . $application->last_name,
                'reviewer_id' => $validated['reviewer_id'],
                'rejection_reason' => $validated['rejection_reason'] ?? null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application rejected successfully'
            ]);

        } catch (ValidationException $e) {
            Log::warning('Application rejection validation failed', [
                'application_id' => $application->id,
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to reject application', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to reject application'
            ], 500);
        }
    }

    /**
     * Get application statistics.
     * @group Students
    */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['school_id', 'campus_id']);
            $statistics = $this->applicationRepository->getApplicationStatistics($filters);

            Log::info('Application statistics retrieved successfully', [
                'total' => $statistics['total_applications'],
                'pending' => $statistics['pending_applications'],
                'approved' => $statistics['approved_applications'],
                'rejected' => $statistics['rejected_applications']
            ]);

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve application statistics', [
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
     * Generate a unique student number.
     * @group Students
    */
    private function generateStudentNumber(int $schoolId): string
    {
        $prefix = config('students.student_number.prefix', 'STU');
        $year = date(config('students.student_number.year_format', 'Y'));
        $separator = config('students.student_number.separator', '');
        $sequenceLength = config('students.student_number.sequence_length', 4);

        // Get the last student number for this school
        $lastStudent = Student::where('school_id', $schoolId)
            ->where('student_number', 'like', $prefix . $separator . $year . '%')
            ->orderBy('student_number', 'desc')
            ->first();

        if ($lastStudent) {
            $lastSequence = (int) substr($lastStudent->student_number, -$sequenceLength);
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }

        return $prefix . $separator . $year . str_pad($newSequence, $sequenceLength, '0', STR_PAD_LEFT);
    }
} 