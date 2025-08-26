<?php

namespace App\Http\Controllers\Api\Teachers;

use App\Http\Controllers\Controller;
use App\Models\Teachers\TeacherLeave;
use App\Repositories\Teachers\TeacherLeaveRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class TeacherLeaveController extends Controller
{
    protected $leaveRepository;

    public function __construct(TeacherLeaveRepositoryInterface $leaveRepository)
    {
        $this->leaveRepository = $leaveRepository;
    }

    /**
     * Display a paginated list of teacher leaves
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['teacher_id', 'type', 'status', 'start_date', 'end_date', 'per_page']);
            $leaves = $this->leaveRepository->getPaginatedLeaves($filters);
            
            return response()->json([
                'success' => true,
                'data' => $leaves,
                'message' => 'Teacher leaves retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve teacher leaves: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created leave request
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'teacher_id' => 'required|exists:teachers,id',
                'leave_type' => 'required|in:sick,vacation,personal,maternity,paternity,study,other',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after:start_date',
                'reason' => 'required|string|max:1000',
                'emergency_contact' => 'nullable|string|max:255',
                'emergency_phone' => 'nullable|string|max:20',
                'attachments' => 'nullable|array',
                'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if teacher is already on leave during the requested period
            $existingLeaves = $this->leaveRepository->getLeavesByDateRange(
                $request->start_date,
                $request->end_date
            );
            
            $hasConflict = $existingLeaves->where('teacher_id', $request->teacher_id)
                ->whereIn('status', ['approved', 'pending'])
                ->count() > 0;

            if ($hasConflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher already has leave requests or approved leaves during this period'
                ], 422);
            }

            $leave = $this->leaveRepository->createLeave($request->all());
            
            return response()->json([
                'success' => true,
                'data' => $leave,
                'message' => 'Leave request submitted successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit leave request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified leave request
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $leave = $this->leaveRepository->getLeaveById($id);
            
            if (!$leave) {
                return response()->json([
                    'success' => false,
                    'message' => 'Leave request not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $leave,
                'message' => 'Leave request retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve leave request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified leave request
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $leave = $this->leaveRepository->getLeaveById($id);
            
            if (!$leave) {
                return response()->json([
                    'success' => false,
                    'message' => 'Leave request not found'
                ], 404);
            }

            // Only allow updates if leave is still pending
            if ($leave->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update leave request that is not pending'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'start_date' => 'sometimes|required|date|after_or_equal:today',
                'end_date' => 'sometimes|required|date|after:start_date',
                'reason' => 'sometimes|required|string|max:1000',
                'emergency_contact' => 'nullable|string|max:255',
                'emergency_phone' => 'nullable|string|max:20'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updated = $this->leaveRepository->updateLeave($leave, $request->all());
            
            if ($updated) {
                $leave->refresh();
                return response()->json([
                    'success' => true,
                    'data' => $leave,
                    'message' => 'Leave request updated successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update leave request'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update leave request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified leave request
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $leave = $this->leaveRepository->getLeaveById($id);
            
            if (!$leave) {
                return response()->json([
                    'success' => false,
                    'message' => 'Leave request not found'
                ], 404);
            }

            // Only allow deletion if leave is still pending
            if ($leave->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete leave request that is not pending'
                ], 422);
            }

            $deleted = $this->leaveRepository->deleteLeave($leave);
            
            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Leave request deleted successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete leave request'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete leave request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve a leave request
     *
     * @param int $id
     * @return JsonResponse
     */
    public function approve(int $id): JsonResponse
    {
        try {
            $leave = $this->leaveRepository->getLeaveById($id);
            
            if (!$leave) {
                return response()->json([
                    'success' => false,
                    'message' => 'Leave request not found'
                ], 404);
            }

            if ($leave->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Leave request is not pending'
                ], 422);
            }

            $reviewerId = Auth::id();
            $approved = $this->leaveRepository->approveLeave($id, $reviewerId);
            
            if ($approved) {
                $leave->refresh();
                return response()->json([
                    'success' => true,
                    'data' => $leave,
                    'message' => 'Leave request approved successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve leave request'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve leave request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a leave request
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'rejection_reason' => 'required|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $leave = $this->leaveRepository->getLeaveById($id);
            
            if (!$leave) {
                return response()->json([
                    'success' => false,
                    'message' => 'Leave request not found'
                ], 404);
            }

            if ($leave->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Leave request is not pending'
                ], 422);
            }

            $reviewerId = Auth::id();
            $rejected = $this->leaveRepository->rejectLeave($id, $reviewerId);
            
            if ($rejected) {
                // Update with rejection reason
                $leave->update(['rejection_reason' => $request->rejection_reason]);
                $leave->refresh();
                
                return response()->json([
                    'success' => true,
                    'data' => $leave,
                    'message' => 'Leave request rejected successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject leave request'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject leave request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get leaves by teacher
     *
     * @param int $teacherId
     * @return JsonResponse
     */
    public function getByTeacher(int $teacherId): JsonResponse
    {
        try {
            $leaves = $this->leaveRepository->getLeavesByTeacher($teacherId);
            
            return response()->json([
                'success' => true,
                'data' => $leaves,
                'message' => 'Teacher leaves retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve teacher leaves: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get leaves by type
     *
     * @param string $type
     * @return JsonResponse
     */
    public function getByType(string $type): JsonResponse
    {
        try {
            $leaves = $this->leaveRepository->getLeavesByType($type);
            
            return response()->json([
                'success' => true,
                'data' => $leaves,
                'message' => 'Leaves retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve leaves: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get leaves by status
     *
     * @param string $status
     * @return JsonResponse
     */
    public function getByStatus(string $status): JsonResponse
    {
        try {
            $leaves = $this->leaveRepository->getLeavesByStatus($status);
            
            return response()->json([
                'success' => true,
                'data' => $leaves,
                'message' => 'Leaves retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve leaves: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending leaves
     *
     * @return JsonResponse
     */
    public function getPending(): JsonResponse
    {
        try {
            $leaves = $this->leaveRepository->getPendingLeaves();
            
            return response()->json([
                'success' => true,
                'data' => $leaves,
                'message' => 'Pending leaves retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve pending leaves: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get leave statistics
     *
     * @return JsonResponse
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $statistics = $this->leaveRepository->getLeaveStatistics();
            
            return response()->json([
                'success' => true,
                'data' => $statistics,
                'message' => 'Leave statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve leave statistics: ' . $e->getMessage()
            ], 500);
        }
    }
} 