<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance\TeacherAttendanceExcuse;
use App\Repositories\Attendance\TeacherAttendanceExcuseRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class TeacherAttendanceExcuseController extends Controller
{
    public function __construct(
        private TeacherAttendanceExcuseRepositoryInterface $excuseRepository
    ) {}

    /**
     * Display a listing of teacher attendance excuses.
     * @group Attendance
    */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'teacher_id', 'class_id', 'subject_id', 'date', 'status'
        ]);

        $excuses = $this->excuseRepository->getPaginatedExcuses($filters);

        return response()->json([
            'success' => true,
            'data' => $excuses
        ]);
    }

    /**
     * Store a newly created teacher attendance excuse.
     * @group Attendance
    */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'teacher_id' => 'required|exists:teachers,id',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'lab_id' => 'nullable|exists:labs,id',
            'date' => 'required|date',
            'reason' => 'required|string|max:255',
            'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data = $request->except('document');
        
        // Handle document upload
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('attendance_excuses/teachers', 'public');
            $data['document_path'] = $documentPath;
        }

        $excuse = $this->excuseRepository->createExcuse($data);

        return response()->json([
            'success' => true,
            'message' => 'Teacher attendance excuse created successfully',
            'data' => $excuse->load(['teacher', 'class', 'subject', 'lab'])
        ], 201);
    }

    /**
     * Display the specified teacher attendance excuse.
     * @group Attendance
    */
    public function show(TeacherAttendanceExcuse $excuse): JsonResponse
    {
        $excuse->load(['teacher', 'class', 'subject', 'lab', 'reviewer']);

        return response()->json([
            'success' => true,
            'data' => $excuse
        ]);
    }

    /**
     * Update the specified teacher attendance excuse.
     * @group Attendance
    */
    public function update(Request $request, TeacherAttendanceExcuse $excuse): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'sometimes|string|max:255',
            'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data = $request->except('document');
        
        // Handle document upload
        if ($request->hasFile('document')) {
            // Delete old document if exists
            if ($excuse->document_path) {
                Storage::disk('public')->delete($excuse->document_path);
            }
            
            $documentPath = $request->file('document')->store('attendance_excuses/teachers', 'public');
            $data['document_path'] = $documentPath;
        }

        $updated = $this->excuseRepository->updateExcuse($excuse, $data);

        if ($updated) {
            $excuse->refresh();
            $excuse->load(['teacher', 'class', 'subject', 'lab', 'reviewer']);

            return response()->json([
                'success' => true,
                'message' => 'Teacher attendance excuse updated successfully',
                'data' => $excuse
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to update teacher attendance excuse'
        ], 500);
    }

    /**
     * Remove the specified teacher attendance excuse.
     * @group Attendance
    */
    public function destroy(TeacherAttendanceExcuse $excuse): JsonResponse
    {
        // Delete document if exists
        if ($excuse->document_path) {
            Storage::disk('public')->delete($excuse->document_path);
        }

        $deleted = $this->excuseRepository->deleteExcuse($excuse);

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Teacher attendance excuse deleted successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to delete teacher attendance excuse'
        ], 500);
    }

    /**
     * Get excuses by teacher.
     * @group Attendance
    */
    public function byTeacher(Request $request, int $teacherId): JsonResponse
    {
        $filters = $request->only(['start_date', 'end_date', 'status']);
        
        $excuses = $this->excuseRepository->getExcusesByTeacher($teacherId, $filters);

        return response()->json([
            'success' => true,
            'data' => $excuses
        ]);
    }

    /**
     * Get excuses by class.
     * @group Attendance
    */
    public function byClass(Request $request, int $classId): JsonResponse
    {
        $filters = $request->only(['date', 'status']);
        
        $excuses = $this->excuseRepository->getExcusesByClass($classId, $filters);

        return response()->json([
            'success' => true,
            'data' => $excuses
        ]);
    }

    /**
     * Get excuses by subject.
     * @group Attendance
    */
    public function bySubject(Request $request, int $subjectId): JsonResponse
    {
        $filters = $request->only(['date', 'status']);
        
        $excuses = $this->excuseRepository->getExcusesBySubject($subjectId, $filters);

        return response()->json([
            'success' => true,
            'data' => $excuses
        ]);
    }

    /**
     * Get excuses by date.
     * @group Attendance
    */
    public function byDate(Request $request, string $date): JsonResponse
    {
        $filters = $request->only(['class_id', 'subject_id', 'status']);
        
        $excuses = $this->excuseRepository->getExcusesByDate($date, $filters);

        return response()->json([
            'success' => true,
            'data' => $excuses
        ]);
    }

    /**
     * Get excuses by date range.
     * @group Attendance
    */
    public function byDateRange(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $filters = $request->only(['teacher_id', 'class_id', 'subject_id', 'status']);
        
        $excuses = $this->excuseRepository->getExcusesByDateRange(
            $request->start_date,
            $request->end_date,
            $filters
        );

        return response()->json([
            'success' => true,
            'data' => $excuses
        ]);
    }

    /**
     * Get pending excuses.
     * @group Attendance
    */
    public function pending(Request $request): JsonResponse
    {
        $filters = $request->only(['class_id', 'subject_id', 'date']);
        
        $excuses = $this->excuseRepository->getPendingExcuses($filters);

        return response()->json([
            'success' => true,
            'data' => $excuses
        ]);
    }

    /**
     * Get approved excuses.
     * @group Attendance
    */
    public function approved(Request $request): JsonResponse
    {
        $filters = $request->only(['class_id', 'subject_id', 'date']);
        
        $excuses = $this->excuseRepository->getApprovedExcuses($filters);

        return response()->json([
            'success' => true,
            'data' => $excuses
        ]);
    }

    /**
     * Get rejected excuses.
     * @group Attendance
    */
    public function rejected(Request $request): JsonResponse
    {
        $filters = $request->only(['class_id', 'subject_id', 'date']);
        
        $excuses = $this->excuseRepository->getRejectedExcuses($filters);

        return response()->json([
            'success' => true,
            'data' => $excuses
        ]);
    }

    /**
     * Approve an excuse request.
     * @group Attendance
    */
    public function approve(TeacherAttendanceExcuse $excuse): JsonResponse
    {
        $reviewerId = Auth::id();
        
        $approved = $this->excuseRepository->approveExcuse($excuse->id, $reviewerId);

        if ($approved) {
            $excuse->refresh();
            $excuse->load(['teacher', 'class', 'subject', 'lab', 'reviewer']);

            return response()->json([
                'success' => true,
                'message' => 'Teacher attendance excuse approved successfully',
                'data' => $excuse
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to approve teacher attendance excuse'
        ], 500);
    }

    /**
     * Reject an excuse request.
     * @group Attendance
    */
    public function reject(TeacherAttendanceExcuse $excuse): JsonResponse
    {
        $reviewerId = Auth::id();
        
        $rejected = $this->excuseRepository->rejectExcuse($excuse->id, $reviewerId);

        if ($rejected) {
            $excuse->refresh();
            $excuse->load(['teacher', 'class', 'subject', 'lab', 'reviewer']);

            return response()->json([
                'success' => true,
                'message' => 'Teacher attendance excuse rejected successfully',
                'data' => $excuse
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to reject teacher attendance excuse'
        ], 500);
    }

    /**
     * Get excuse statistics.
     * @group Attendance
    */
    public function statistics(Request $request): JsonResponse
    {
        $filters = $request->only(['start_date', 'end_date', 'class_id', 'subject_id']);
        
        $statistics = $this->excuseRepository->getExcuseStatistics($filters);

        return response()->json([
            'success' => true,
            'data' => $statistics
        ]);
    }

    /**
     * Generate excuse report.
     * @group Attendance
    */
    public function report(Request $request): JsonResponse
    {
        $filters = $request->only(['start_date', 'end_date', 'class_id', 'subject_id']);
        
        $report = $this->excuseRepository->generateExcuseReport($filters);

        return response()->json([
            'success' => true,
            'data' => $report
        ]);
    }

    /**
     * Get excuse trends for a teacher.
     * @group Attendance
    */
    public function trends(Request $request, int $teacherId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $trends = $this->excuseRepository->getExcuseTrends(
            $teacherId,
            $request->start_date,
            $request->end_date
        );

        return response()->json([
            'success' => true,
            'data' => $trends
        ]);
    }
} 