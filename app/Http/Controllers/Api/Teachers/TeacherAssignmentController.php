<?php

namespace App\Http\Controllers\Api\Teachers;

use App\Http\Controllers\Controller;
use App\Models\Teachers\TeacherAssignment;
use App\Models\Teachers\Teacher;
use App\Models\Academic\Class as AcademicClass;
use App\Models\Academic\Subject;
use App\Repositories\Teachers\TeacherAssignmentRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
        $filters = $request->only(['teacher_id', 'class_id', 'subject_id', 'academic_year', 'term']);
        
        $assignments = $this->assignmentRepository->getPaginatedAssignments($filters);
        
        return response()->json([
            'success' => true,
            'data' => $assignments->items(),
            'pagination' => [
                'current_page' => $assignments->currentPage(),
                'last_page' => $assignments->lastPage(),
                'per_page' => $assignments->perPage(),
                'total' => $assignments->total(),
            ]
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
            'academic_year' => 'required|string|max:9',
            'term' => 'required|string|max:20',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'hours_per_week' => 'required|integer|min:1|max:40',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:500',
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
            $request->academic_year,
            $request->term,
            $request->start_date,
            $request->end_date
        );

        if (!empty($conflicts)) {
            return response()->json([
                'success' => false,
                'message' => 'Assignment conflicts detected',
                'conflicts' => $conflicts
            ], 409);
        }

        $assignment = $this->assignmentRepository->createAssignment($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Teacher assignment created successfully',
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
            'data' => $assignment->load(['teacher', 'class', 'subject'])
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
            'academic_year' => 'sometimes|string|max:9',
            'term' => 'sometimes|string|max:20',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'hours_per_week' => 'sometimes|integer|min:1|max:40',
            'is_active' => 'sometimes|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check for conflicts if changing key fields
        if ($request->hasAny(['teacher_id', 'class_id', 'subject_id', 'academic_year', 'term', 'start_date', 'end_date'])) {
            $conflicts = $this->assignmentRepository->checkAssignmentConflicts(
                $request->get('teacher_id', $assignment->teacher_id),
                $request->get('class_id', $assignment->class_id),
                $request->get('subject_id', $assignment->subject_id),
                $request->get('academic_year', $assignment->academic_year),
                $request->get('term', $assignment->term),
                $request->get('start_date', $assignment->start_date),
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

        $updated = $this->assignmentRepository->updateAssignment($assignment, $request->all());

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update teacher assignment'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Teacher assignment updated successfully',
            'data' => $assignment->fresh()->load(['teacher', 'class', 'subject'])
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
            'data' => $assignments->load(['class', 'subject'])
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
            'data' => $assignments->load(['teacher', 'subject'])
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
            'data' => $assignments->load(['teacher', 'class'])
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
            'assignments.*.academic_year' => 'required|string|max:9',
            'assignments.*.term' => 'required|string|max:20',
            'assignments.*.start_date' => 'required|date',
            'assignments.*.end_date' => 'required|date|after:assignments.*.start_date',
            'assignments.*.hours_per_week' => 'required|integer|min:1|max:40',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $results = $this->assignmentRepository->bulkImportAssignments($request->assignments);

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
        $filters = $request->only(['teacher_id', 'class_id', 'subject_id', 'academic_year', 'term']);
        
        $assignments = $this->assignmentRepository->getAllAssignments($filters);

        return response()->json([
            'success' => true,
            'data' => $assignments->load(['teacher', 'class', 'subject']),
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
        $filters = $request->only(['academic_year', 'term', 'department_id', 'faculty_id']);
        
        $report = $this->assignmentRepository->generateAssignmentReport($filters);

        return response()->json([
            'success' => true,
            'data' => $report
        ]);
    }
} 