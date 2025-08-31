<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance\StudentAttendance;
use App\Repositories\Attendance\StudentAttendanceRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StudentAttendanceController extends Controller
{
    public function __construct(
        private StudentAttendanceRepositoryInterface $attendanceRepository
    ) {}

    /**
     * Display a listing of student attendance records.
     * @group Attendance
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'student_id', 'class_id', 'subject_id', 'date', 'status', 'term', 'academic_year'
        ]);

        $attendance = $this->attendanceRepository->getPaginatedAttendance($filters);

        return response()->json([
            'success' => true,
            'data' => $attendance
        ]);
    }

    /**
     * Store a newly created student attendance record.
     * @group Attendance
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'lab_id' => 'nullable|exists:labs,id',
            'timetable_id' => 'required|exists:teacher_timetables,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late,excused',
            'remarks' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $attendance = $this->attendanceRepository->createAttendance($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Student attendance record created successfully',
            'data' => $attendance->load(['student', 'class', 'subject', 'lab', 'timetable'])
        ], 201);
    }

    /**
     * Display the specified student attendance record.
     * @group Attendance
     */
    public function show(StudentAttendance $attendance): JsonResponse
    {
        $attendance->load(['student', 'class', 'subject', 'lab', 'timetable']);

        return response()->json([
            'success' => true,
            'data' => $attendance
        ]);
    }

    /**
     * Update the specified student attendance record.
     * @group Attendance
     */
    public function update(Request $request, StudentAttendance $attendance): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:present,absent,late,excused',
            'remarks' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $updated = $this->attendanceRepository->updateAttendance($attendance, $request->all());

        if ($updated) {
            $attendance->refresh();
            $attendance->load(['student', 'class', 'subject', 'lab', 'timetable']);

            return response()->json([
                'success' => true,
                'message' => 'Student attendance record updated successfully',
                'data' => $attendance
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to update student attendance record'
        ], 500);
    }

    /**
     * Remove the specified student attendance record.
     * @group Attendance
     */
    public function destroy(StudentAttendance $attendance): JsonResponse
    {
        $deleted = $this->attendanceRepository->deleteAttendance($attendance);

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Student attendance record deleted successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to delete student attendance record'
        ], 500);
    }

    /**
     * Get attendance by student.
     * @group Attendance
     */
    public function byStudent(Request $request, int $studentId): JsonResponse
    {
        $filters = $request->only(['start_date', 'end_date', 'status']);
        
        $attendance = $this->attendanceRepository->getAttendanceByStudent($studentId, $filters);

        return response()->json([
            'success' => true,
            'data' => $attendance
        ]);
    }

    /**
     * Get attendance by class.
     * @group Attendance
     */
    public function byClass(Request $request, int $classId): JsonResponse
    {
        $filters = $request->only(['date', 'status']);
        
        $attendance = $this->attendanceRepository->getAttendanceByClass($classId, $filters);

        return response()->json([
            'success' => true,
            'data' => $attendance
        ]);
    }

    /**
     * Get attendance by subject.
     * @group Attendance
     */
    public function bySubject(Request $request, int $subjectId): JsonResponse
    {
        $filters = $request->only(['date', 'status']);
        
        $attendance = $this->attendanceRepository->getAttendanceBySubject($subjectId, $filters);

        return response()->json([
            'success' => true,
            'data' => $attendance
        ]);
    }

    /**
     * Get attendance by date.
     * @group Attendance
     */
    public function byDate(Request $request, string $date): JsonResponse
    {
        $filters = $request->only(['class_id', 'subject_id', 'status']);
        
        $attendance = $this->attendanceRepository->getAttendanceByDate($date, $filters);

        return response()->json([
            'success' => true,
            'data' => $attendance
        ]);
    }

    /**
     * Get attendance by date range.
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

        $filters = $request->only(['student_id', 'class_id', 'subject_id', 'status']);
        
        $attendance = $this->attendanceRepository->getAttendanceByDateRange(
            $request->start_date,
            $request->end_date,
            $filters
        );

        return response()->json([
            'success' => true,
            'data' => $attendance
        ]);
    }

    /**
     * Bulk create attendance records.
     * @group Attendance
     */
    public function bulkCreate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'attendance_data' => 'required|array|min:1',
            'attendance_data.*.student_id' => 'required|exists:students,id',
            'attendance_data.*.class_id' => 'required|exists:classes,id',
            'attendance_data.*.subject_id' => 'required|exists:subjects,id',
            'attendance_data.*.timetable_id' => 'required|exists:teacher_timetables,id',
            'attendance_data.*.date' => 'required|date',
            'attendance_data.*.status' => 'required|in:present,absent,late,excused',
            'attendance_data.*.remarks' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $results = $this->attendanceRepository->bulkCreateAttendance($request->attendance_data);

        return response()->json([
            'success' => true,
            'message' => 'Bulk attendance creation completed',
            'data' => $results
        ]);
    }

    /**
     * Bulk update attendance records.
     * @group Attendance
    */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'attendance_data' => 'required|array|min:1',
            'attendance_data.*.id' => 'required|exists:student_attendance,id',
            'attendance_data.*.status' => 'sometimes|in:present,absent,late,excused',
            'attendance_data.*.remarks' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $results = $this->attendanceRepository->bulkUpdateAttendance($request->attendance_data);

        return response()->json([
            'success' => true,
            'message' => 'Bulk attendance update completed',
            'data' => $results
        ]);
    }

    /**
     * Get attendance statistics.
     * @group Attendance
    */
    public function statistics(Request $request): JsonResponse
    {
        $filters = $request->only(['start_date', 'end_date', 'class_id', 'subject_id']);
        
        $statistics = $this->attendanceRepository->getAttendanceStatistics($filters);

        return response()->json([
            'success' => true,
            'data' => $statistics
        ]);
    }

    /**
     * Generate attendance report.
     * @group Attendance
    */
    public function report(Request $request): JsonResponse
    {
        $filters = $request->only(['start_date', 'end_date', 'class_id', 'subject_id']);
        
        $report = $this->attendanceRepository->generateAttendanceReport($filters);

        return response()->json([
            'success' => true,
            'data' => $report
        ]);
    }

    /**
     * Get attendance trends for a student.
     * @group Attendance
    */
    public function trends(Request $request, int $studentId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $trends = $this->attendanceRepository->getAttendanceTrends(
            $studentId,
            $request->start_date,
            $request->end_date
        );

        return response()->json([
            'success' => true,
            'data' => $trends
        ]);
    }

    /**
     * Get class attendance summary for a specific date.
     * @group Attendance
    */
    public function classSummary(Request $request, int $classId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $summary = $this->attendanceRepository->getClassAttendanceSummary($classId, $request->date);

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }

    /**
     * Get student attendance summary for a date range.
     * @group Attendance
    */
    public function studentSummary(Request $request, int $studentId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $summary = $this->attendanceRepository->getStudentAttendanceSummary(
            $studentId,
            $request->start_date,
            $request->end_date
        );

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }
} 