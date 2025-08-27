<?php

namespace App\Http\Controllers\Api\Students;

use App\Http\Controllers\Controller;
use App\Models\Students\StudentTransfer;
use App\Repositories\Students\StudentTransferRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class StudentTransferController extends Controller
{
    protected $transferRepository;

    public function __construct(StudentTransferRepository $transferRepository)
    {
        $this->transferRepository = $transferRepository;
    }

    /**
     * Display a listing of student transfers.
     * @group Students
    */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'status', 'student_id', 'from_campus_id', 'to_campus_id', 
                'reviewer_id', 'date_from', 'date_to'
            ]);

            $transfers = $this->transferRepository->getPaginatedTransfers($filters);

            Log::info('Student transfers retrieved successfully', [
                'count' => $transfers->count(),
                'filters' => $filters
            ]);

            return response()->json([
                'success' => true,
                'data' => $transfers->items(),
                'pagination' => [
                    'current_page' => $transfers->currentPage(),
                    'last_page' => $transfers->lastPage(),
                    'per_page' => $transfers->perPage(),
                    'total' => $transfers->total(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve student transfers', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve transfer records'
            ], 500);
        }
    }

    /**
     * Store a newly created student transfer.
     * @group Students
    */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'student_id' => 'required|exists:students,id',
                'from_campus_id' => 'required|exists:campuses,id',
                'to_campus_id' => 'required|exists:campuses,id|different:from_campus_id',
                'request_date' => 'required|date',
                'status' => 'sometimes|in:pending,approved,rejected',
            ]);

            // Check if student already has a pending transfer
            if ($this->transferRepository->hasPendingTransfer($validated['student_id'])) {
                Log::warning('Transfer creation rejected - student already has pending transfer', [
                    'student_id' => $validated['student_id'],
                    'input' => $validated
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Student already has a pending transfer request'
                ], 422);
            }

            $transfer = $this->transferRepository->createTransfer($validated);

            Log::info('Student transfer created successfully', [
                'transfer_id' => $transfer->id,
                'student_id' => $transfer->student_id,
                'from_campus_id' => $transfer->from_campus_id,
                'to_campus_id' => $transfer->to_campus_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transfer request submitted successfully',
                'data' => $transfer->load(['student', 'fromCampus', 'toCampus'])
            ], 201);

        } catch (ValidationException $e) {
            Log::warning('Transfer creation validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to create transfer', [
                'error' => $e->getMessage(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to submit transfer request'
            ], 500);
        }
    }

    /**
     * Display the specified student transfer.
     * @group Students
    */
    public function show(StudentTransfer $transfer): JsonResponse
    {
        try {
            $transfer->load(['student', 'fromCampus', 'toCampus', 'reviewer']);

            Log::info('Student transfer retrieved successfully', [
                'transfer_id' => $transfer->id,
                'student_id' => $transfer->student_id
            ]);

            return response()->json([
                'success' => true,
                'data' => $transfer
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve student transfer', [
                'transfer_id' => $transfer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve transfer record'
            ], 500);
        }
    }

    /**
     * Update the specified student transfer.
     * @group Students
    */
    public function update(Request $request, StudentTransfer $transfer): JsonResponse
    {
        try {
            // Only allow updates for pending transfers
            if (!$transfer->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update non-pending transfer'
                ], 422);
            }

            $validated = $request->validate([
                'from_campus_id' => 'sometimes|required|exists:campuses,id',
                'to_campus_id' => 'sometimes|required|exists:campuses,id|different:from_campus_id',
                'request_date' => 'sometimes|required|date',
            ]);

            $transfer->update($validated);

            Log::info('Student transfer updated successfully', [
                'transfer_id' => $transfer->id,
                'student_id' => $transfer->student_id,
                'changes' => $validated
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transfer updated successfully',
                'data' => $transfer->fresh()->load(['student', 'fromCampus', 'toCampus'])
            ]);

        } catch (ValidationException $e) {
            Log::warning('Student transfer update validation failed', [
                'transfer_id' => $transfer->id,
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update student transfer', [
                'transfer_id' => $transfer->id,
                'error' => $e->getMessage(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to update transfer'
            ], 500);
        }
    }

    /**
     * Remove the specified student transfer.
     * @group Students
    */
    public function destroy(StudentTransfer $transfer): JsonResponse
    {
        try {
            // Only allow deletion of pending transfers
            if (!$transfer->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete non-pending transfer'
                ], 422);
            }

            $transferId = $transfer->id;
            $studentId = $transfer->student_id;
            
            $transfer->delete();

            Log::info('Student transfer deleted successfully', [
                'transfer_id' => $transferId,
                'student_id' => $studentId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transfer deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete student transfer', [
                'transfer_id' => $transfer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to delete transfer'
            ], 500);
        }
    }

    /**
     * Approve a student transfer.
     * @group Students
    */
    public function approve(Request $request, StudentTransfer $transfer): JsonResponse
    {
        try {
            $validated = $request->validate([
                'reviewer_id' => 'required|exists:users,id',
            ]);

            if ($transfer->status !== StudentTransfer::STATUS_PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transfer is not pending approval'
                ], 422);
            }

            $this->transferRepository->approveTransfer($transfer, $validated['reviewer_id']);

            Log::info('Student transfer approved successfully', [
                'transfer_id' => $transfer->id,
                'student_id' => $transfer->student_id,
                'reviewer_id' => $validated['reviewer_id']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transfer approved successfully'
            ]);

        } catch (ValidationException $e) {
            Log::warning('Transfer approval validation failed', [
                'transfer_id' => $transfer->id,
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to approve transfer', [
                'transfer_id' => $transfer->id,
                'error' => $e->getMessage(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to approve transfer'
            ], 500);
        }
    }

    /**
     * Reject a student transfer.
     * @group Students
    */
    public function reject(Request $request, StudentTransfer $transfer): JsonResponse
    {
        try {
            if (!$transfer->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transfer is not pending'
                ], 422);
            }

            $reviewerId = $request->user()->id ?? 1;
            $transfer->reject($reviewerId);

            Log::info('Student transfer rejected successfully', [
                'transfer_id' => $transfer->id,
                'student_id' => $transfer->student_id,
                'reviewer_id' => $reviewerId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transfer rejected successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to reject student transfer', [
                'transfer_id' => $transfer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to reject transfer'
            ], 500);
        }
    }

    /**
     * Get transfers by student.
     * @group Students
    */
    public function byStudent(int $studentId): JsonResponse
    {
        try {
            $transfers = StudentTransfer::where('student_id', $studentId)
                ->with(['fromCampus', 'toCampus', 'reviewer'])
                ->orderBy('request_date', 'desc')
                ->get();

            Log::info('Student transfers retrieved by student', [
                'student_id' => $studentId,
                'count' => $transfers->count()
            ]);

            return response()->json([
                'success' => true,
                'data' => $transfers
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve student transfers by student', [
                'student_id' => $studentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve transfer records'
            ], 500);
        }
    }

    /**
     * Get transfers by campus.
     * @group Students
    */
    public function byCampus(int $campusId): JsonResponse
    {
        try {
            $transfers = StudentTransfer::where(function($query) use ($campusId) {
                $query->where('from_campus_id', $campusId)
                      ->orWhere('to_campus_id', $campusId);
            })
            ->with(['student', 'fromCampus', 'toCampus', 'reviewer'])
            ->orderBy('request_date', 'desc')
            ->get();

            Log::info('Student transfers retrieved by campus', [
                'campus_id' => $campusId,
                'count' => $transfers->count()
            ]);

            return response()->json([
                'success' => true,
                'data' => $transfers
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve student transfers by campus', [
                'campus_id' => $campusId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve transfer records'
            ], 500);
        }
    }

    /**
     * Get transfer statistics.
     * @group Students
    */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['school_id']);
            $statistics = $this->transferRepository->getTransferStatistics($filters);

            Log::info('Transfer statistics retrieved successfully', [
                'total' => $statistics['total_transfers'],
                'pending' => $statistics['pending_transfers'],
                'approved' => $statistics['approved_transfers'],
                'rejected' => $statistics['rejected_transfers']
            ]);

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve transfer statistics', [
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
     * Get transfer analysis report.
     * @group Students
    */
    public function transferAnalysis(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['school_id', 'date_from', 'date_to']);
            
            Log::info('Transfer analysis report retrieved successfully', [
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
            Log::error('Failed to retrieve transfer analysis report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve transfer analysis report'
            ], 500);
        }
    }
} 