<?php

namespace App\Http\Controllers\Api\Teachers;

use App\Http\Controllers\Controller;
use App\Models\Teachers\TeacherAssignment;
use App\Models\Teachers\Teacher;
use App\Models\AdminAcademics\ClassRoom as AcademicClass;
use App\Models\AdminAcademics\Subject;
use App\Models\User;
use App\Repositories\Teachers\TeacherAssignmentRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;




class TeacherAssignmentController extends Controller
{
    public function __construct(
        private TeacherAssignmentRepositoryInterface $assignmentRepository
    ) {}

    /**
     * Display a listing of teacher assignments.
     * @group Teachers
    */  
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'teacher_id', 
            'class_id', 
            'subject_id', 
            'academic_year_id', 
            'role',
            'is_primary',
            'assigned_by',
            'school_id',
            'assignment_date_from',
            'assignment_date_to',
            'end_date_from',
            'end_date_to',
            'weekly_hours_min',
            'weekly_hours_max',
            'is_active',
            'notes_search'
        ]);
        
        $assignments = $this->assignmentRepository->getAllAssignments($filters);
        
        return response()->json([
            'success' => true,
            'data' => $assignments,
            'message' => 'Teacher assignments retrieved successfully'
        ]);
    }

    /**
     * Store a newly created teacher assignment.
     * @group Teachers
    */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'teacher_id' => 'required|exists:teachers,id',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'role' => 'required|string|max:50',
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
            'assigned_by' => 'required|email|exists:users,email',
            'assignment_date' => 'required|date',
            'end_date' => 'nullable|date|after:assignment_date',
            'weekly_hours' => 'required|integer|min:0|max:40',
            'notes' => 'nullable|string|max:500',
            'school_id' => 'required|exists:schools,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check for conflicts
        $conflicts = $this->assignmentRepository->checkAssignmentConflicts(
            $request->teacher_id,
            $request->class_id,
            $request->subject_id,
            $request->academic_year_id,
            $request->role,
            $request->assignment_date,
            $request->end_date
        );

        if (!empty($conflicts)) {
            return response()->json([
                'success' => false,
                'message' => 'Assignment conflicts detected',
                'conflicts' => $conflicts
            ], 409);
        }

        // Convert assigned_by email to user ID
        $data = $request->all();
        if (isset($data['assigned_by'])) {
            $data['assigned_by'] = $this->getUserIdByEmail($data['assigned_by']);
        }

        $assignment = $this->assignmentRepository->createAssignment($data);

        return response()->json([
            'success' => true,
            'message' => 'Enseignant assigné avec succès',
            'data' => $assignment
        ], 201);
    }

    /**
     * Display the specified teacher assignment.
     * @group Teachers
    */
    public function show(TeacherAssignment $assignment): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $assignment->load(['teacher', 'class', 'subject', 'assignedBy'])
        ]);
    }

    /**
     * Update the specified teacher assignment.
     * @group Teachers
    */
    public function update(Request $request, TeacherAssignment $assignment): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'teacher_id' => 'sometimes|exists:teachers,id',
            'class_id' => 'sometimes|exists:classes,id',
            'subject_id' => 'sometimes|exists:subjects,id',
            'academic_year_id' => 'sometimes|exists:academic_years,id',
            'role' => 'sometimes|string|max:50',
            'is_primary' => 'sometimes|boolean',
            'assigned_by' => 'sometimes|email|exists:users,email',
            'assignment_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:assignment_date',
            'weekly_hours' => 'sometimes|integer|min:0|max:40',
            'is_active' => 'sometimes|boolean',
            'notes' => 'nullable|string|max:500',
            'school_id' => 'sometimes|exists:schools,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check for conflicts if changing key fields
        if ($request->hasAny(['teacher_id', 'class_id', 'subject_id', 'academic_year_id', 'role', 'assignment_date', 'end_date'])) {
            $conflicts = $this->assignmentRepository->checkAssignmentConflicts(
                $request->get('teacher_id', $assignment->teacher_id),
                $request->get('class_id', $assignment->class_id),
                $request->get('subject_id', $assignment->subject_id),
                $request->get('academic_year_id', $assignment->academic_year_id),
                $request->get('role', $assignment->role),
                $request->get('assignment_date', $assignment->assignment_date),
                $request->get('end_date', $assignment->end_date),
                $assignment->id // Exclude current assignment from conflict check
            );

            if (!empty($conflicts)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Assignment conflicts detected',
                    'conflicts' => $conflicts
                ], 409);
            }
        }

        // Convert assigned_by email to user ID if provided
        $data = $request->all();
        if (isset($data['assigned_by'])) {
            $data['assigned_by'] = $this->getUserIdByEmail($data['assigned_by']);
        }

        $updated = $this->assignmentRepository->updateAssignment($assignment, $data);

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update teacher assignment'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Teacher assignment updated successfully',
            'data' => $assignment->fresh()->load(['teacher', 'class', 'subject', 'assignedBy'])
        ]);
    }

    /**
     * Remove the specified teacher assignment.
     * @group Teachers
    */
    public function destroy(TeacherAssignment $assignment): JsonResponse
    {
        $deleted = $this->assignmentRepository->deleteAssignment($assignment);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete teacher assignment'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Teacher assignment deleted successfully'
        ]);
    }

    /**
     * Get assignments by teacher.
     * @group Teachers
    */
    public function byTeacher(Teacher $teacher): JsonResponse
    {
        $assignments = $this->assignmentRepository->getAssignmentsByTeacher($teacher->id);

        return response()->json([
            'success' => true,
            'data' => $assignments->load(['class', 'subject', 'assignedBy'])
        ]);
    }

    /**
     * Get assignments by class.
     * @group Teachers
    */
    public function byClass(AcademicClass $class): JsonResponse
    {
        $assignments = $this->assignmentRepository->getAssignmentsByClass($class->id);

        return response()->json([
            'success' => true,
            'data' => $assignments->load(['teacher', 'subject', 'assignedBy'])
        ]);
    }

    /**
     * Get assignments by subject.
     * @group Teachers
    */
    public function bySubject(Subject $subject): JsonResponse
    {
        $assignments = $this->assignmentRepository->getAssignmentsBySubject($subject->id);

        return response()->json([
            'success' => true,
            'data' => $assignments->load(['teacher', 'class', 'assignedBy'])
        ]);
    }

    /**
     * Bulk import assignments.
     * @group Teachers
    */
    public function bulkImport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'assignments' => 'required|array|min:1',
            'assignments.*.teacher_id' => 'required|exists:teachers,id',
            'assignments.*.class_id' => 'required|exists:classes,id',
            'assignments.*.subject_id' => 'required|exists:subjects,id',
            'assignments.*.academic_year_id' => 'required|exists:academic_years,id',
            'assignments.*.role' => 'required|string|max:50',
            'assignments.*.is_primary' => 'boolean',
            'assignments.*.assigned_by' => 'required|email|exists:users,email',
            'assignments.*.assignment_date' => 'required|date',
            'assignments.*.end_date' => 'nullable|date|after:assignments.*.assignment_date',
            'assignments.*.weekly_hours' => 'required|integer|min:0|max:40',
            'assignments.*.is_active' => 'boolean',
            'assignments.*.notes' => 'nullable|string|max:500',
            'assignments.*.school_id' => 'required|exists:schools,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Convert assigned_by emails to user IDs for all assignments
        $assignments = $request->assignments;
        foreach ($assignments as &$assignment) {
            if (isset($assignment['assigned_by'])) {
                $assignment['assigned_by'] = $this->getUserIdByEmail($assignment['assigned_by']);
            }
        }

        $results = $this->assignmentRepository->bulkImportAssignments($assignments);

        return response()->json([
            'success' => true,
            'message' => 'Bulk import completed',
            'data' => $results
        ]);
    }

    /**
     * Bulk export assignments.
     * @group Teachers
    */
    public function bulkExport(Request $request): JsonResponse
    {
        $filters = $request->only([
            'teacher_id', 
            'class_id', 
            'subject_id', 
            'academic_year_id', 
            'role',
            'is_primary',
            'assigned_by',
            'school_id',
            'assignment_date_from',
            'assignment_date_to',
            'end_date_from',
            'end_date_to',
            'weekly_hours_min',
            'weekly_hours_max',
            'is_active',
            'notes_search'
        ]);
        
        $assignments = $this->assignmentRepository->getAllAssignments($filters);

        return response()->json([
            'success' => true,
            'data' => $assignments->load(['teacher', 'class', 'subject', 'assignedBy']),
            'export_format' => 'json'
        ]);
    }

    /**
     * Get assignment statistics.
     * @group Teachers
    */
    public function statistics(): JsonResponse
    {
        $stats = $this->assignmentRepository->getAssignmentStatistics();

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Generate assignment analysis report.
     * @group Teachers
    */
    public function assignmentReport(Request $request): JsonResponse
    {
        $filters = $request->only([
            'academic_year_id', 
            'role',
            'is_primary',
            'assigned_by',
            'school_id',
            'assignment_date_from',
            'assignment_date_to',
            'end_date_from',
            'end_date_to',
            'weekly_hours_min',
            'weekly_hours_max',
            'is_active',
            'department_id', 
            'faculty_id'
        ]);
        
        $report = $this->assignmentRepository->generateAssignmentReport($filters);

        return response()->json([
            'success' => true,
            'data' => $report
        ]);
    }

    /**
     * Get user ID by email address
     * @param string $email The user's email address
     * @return int The user's ID
     * @throws \Exception If user not found
     */
    private function getUserIdByEmail(string $email): int
    {
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            throw new \Exception("User with email '{$email}' not found.");
        }
        
        return $user->id;
    }
} 