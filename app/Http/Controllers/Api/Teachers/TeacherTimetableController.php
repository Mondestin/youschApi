<?php

namespace App\Http\Controllers\Api\Teachers;

use App\Http\Controllers\Controller;
use App\Models\Teachers\TeacherTimetable;
use App\Repositories\Teachers\TeacherTimetableRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TeacherTimetableController extends Controller
{
    protected $timetableRepository;

    public function __construct(TeacherTimetableRepositoryInterface $timetableRepository)
    {
        $this->timetableRepository = $timetableRepository;
    }

    /**
     * Display a paginated list of teacher timetables
     *
     * @param Request $request
     * @return JsonResponse
     * @group Teachers
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['teacher_id', 'class_id', 'subject_id', 'day_of_week', 'academic_year_id', 'term_id', 'per_page']);
            $timetables = $this->timetableRepository->getAllTimetables($filters);
            
            return response()->json([
                'success' => true,
                'data' => $timetables,
                'message' => 'Teacher timetables retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve teacher timetables: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created teacher timetable entry
     *
     * @param Request $request
     * @return JsonResponse
     * @group Teachers
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'teacher_id' => 'required|exists:teachers,id',
                'class_id' => 'required|exists:classes,id',
                'subject_id' => 'required|exists:subjects,id',
                'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'venue_id' => 'nullable|exists:venues,id',
                'academic_year_id' => 'required|exists:academic_years,id',
                'term_id' => 'required|exists:terms,id',
                'is_active' => 'boolean',
                'notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check for time conflicts
            $conflicts = $this->timetableRepository->checkTimeConflicts(
                $request->teacher_id,
                $request->day_of_week,
                $request->start_time,
                $request->end_time,
                $request->academic_year_id,
                $request->term_id
            );

            if ($conflicts->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Time slot conflicts with existing timetable entries',
                    'conflicts' => $conflicts
                ], 422);
            }

            $timetable = $this->timetableRepository->createTimetable($request->all());
            
            return response()->json([
                'success' => true,
                'data' => $timetable,
                'message' => 'Timetable entry created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create timetable entry: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified teacher timetable entry
     *
     * @param int $id
     * @return JsonResponse
     * @group Teachers
     */
    public function show(int $id): JsonResponse
    {
        try {
            $timetable = $this->timetableRepository->getTimetableById($id);
            
            if (!$timetable) {
                return response()->json([
                    'success' => false,
                    'message' => 'Timetable entry not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $timetable,
                'message' => 'Timetable entry retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve timetable entry: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified teacher timetable entry
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @group Teachers
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $timetable = $this->timetableRepository->getTimetableById($id);
            
            if (!$timetable) {
                return response()->json([
                    'success' => false,
                    'message' => 'Timetable entry not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'class_id' => 'sometimes|required|exists:classes,id',
                'subject_id' => 'sometimes|required|exists:subjects,id',
                'day_of_week' => 'sometimes|required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
                'start_time' => 'sometimes|required|date_format:H:i',
                'end_time' => 'sometimes|required|date_format:H:i|after:start_time',
                'venue_id' => 'nullable|exists:venues,id',
                'is_active' => 'boolean',
                'notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check for time conflicts (excluding current entry)
            if ($request->has('day_of_week') || $request->has('start_time') || $request->has('end_time')) {
                $conflicts = $this->timetableRepository->checkTimeConflicts(
                    $timetable->teacher_id,
                    $request->get('day_of_week', $timetable->day_of_week),
                    $request->get('start_time', $timetable->start_time),
                    $request->get('end_time', $timetable->end_time),
                    $timetable->academic_year_id,
                    $timetable->term_id,
                    $id // Exclude current entry
                );

                if ($conflicts->count() > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Time slot conflicts with existing timetable entries',
                        'conflicts' => $conflicts
                    ], 422);
                }
            }

            $updated = $this->timetableRepository->updateTimetable($timetable, $request->all());
            
            if ($updated) {
                $timetable->refresh();
                return response()->json([
                    'success' => true,
                    'data' => $timetable,
                    'message' => 'Timetable entry updated successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update timetable entry'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update timetable entry: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified teacher timetable entry
     *
     * @param int $id
     * @return JsonResponse
     * @group Teachers
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $timetable = $this->timetableRepository->getTimetableById($id);
            
            if (!$timetable) {
                return response()->json([
                    'success' => false,
                    'message' => 'Timetable entry not found'
                ], 404);
            }

            $deleted = $this->timetableRepository->deleteTimetable($timetable);
            
            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Timetable entry deleted successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete timetable entry'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete timetable entry: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get timetable by teacher
     *
     * @param int $teacherId
     * @return JsonResponse
     * @group Teachers
     */
    public function getByTeacher(int $teacherId): JsonResponse
    {
        try {
            $timetables = $this->timetableRepository->getTimetablesByTeacher($teacherId);
            
            return response()->json([
                'success' => true,
                'data' => $timetables,
                'message' => 'Teacher timetables retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve teacher timetables: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get timetable by class
     *
     * @param int $classId
     * @return JsonResponse
     * @group Teachers
     */
    public function getByClass(int $classId): JsonResponse
    {
        try {
            $timetables = $this->timetableRepository->getTimetablesByClass($classId);
            
            return response()->json([
                'success' => true,
                'data' => $timetables,
                'message' => 'Class timetables retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve class timetables: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get timetable by subject
     *
     * @param int $subjectId
     * @return JsonResponse
     * @group Teachers
     */
    public function getBySubject(int $subjectId): JsonResponse
    {
        try {
            $timetables = $this->timetableRepository->getTimetablesBySubject($subjectId);
            
            return response()->json([
                'success' => true,
                'data' => $timetables,
                'message' => 'Subject timetables retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve subject timetables: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get timetable by day of week
     *
     * @param string $dayOfWeek
     * @return JsonResponse
     * @group Teachers
     */
    public function getByDay(string $dayOfWeek): JsonResponse
    {
        try {
            $validator = Validator::make(['day_of_week' => $dayOfWeek], [
                'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid day of week',
                    'errors' => $validator->errors()
                ], 422);
            }

            $timetables = $this->timetableRepository->getTimetablesByDay($dayOfWeek);
            
            return response()->json([
                'success' => true,
                'data' => $timetables,
                'message' => 'Day timetables retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve day timetables: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get timetable by academic year and term
     *
     * @param int $academicYearId
     * @param int $termId
     * @return JsonResponse
     * @group Teachers
     */
    public function getByAcademicYearAndTerm(int $academicYearId, int $termId): JsonResponse
    {
        try {
            $timetables = $this->timetableRepository->getTimetablesByAcademicYearAndTerm($academicYearId, $termId);
            
            return response()->json([
                'success' => true,
                'data' => $timetables,
                'message' => 'Academic year and term timetables retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve academic year and term timetables: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get teacher weekly schedule
     *
     * @param int $teacherId
     * @param int $academicYearId
     * @param int $termId
     * @return JsonResponse
     * @group Teachers
     */
    public function getWeeklySchedule(int $teacherId, int $academicYearId, int $termId): JsonResponse
    {
        try {
            $schedule = $this->timetableRepository->getTeacherWeeklySchedule($teacherId, $academicYearId, $termId);
            
            return response()->json([
                'success' => true,
                'data' => $schedule,
                'message' => 'Weekly schedule retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve weekly schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get class weekly schedule
     *
     * @param int $classId
     * @param int $academicYearId
     * @param int $termId
     * @return JsonResponse
     * @group Teachers
    */
    public function getClassWeeklySchedule(int $classId, int $academicYearId, int $termId): JsonResponse
    {
        try {
            $schedule = $this->timetableRepository->getClassWeeklySchedule($classId, $academicYearId, $termId);
            
            return response()->json([
                'success' => true,
                'data' => $schedule,
                'message' => 'Class weekly schedule retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve class weekly schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check time conflicts
     *
     * @param Request $request
     * @return JsonResponse
     * @group Teachers
    */
    public function checkConflicts(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'teacher_id' => 'required|exists:teachers,id',
                'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'academic_year_id' => 'required|exists:academic_years,id',
                'term_id' => 'required|exists:terms,id',
                'exclude_id' => 'nullable|integer|exists:teacher_timetables,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $conflicts = $this->timetableRepository->checkTimeConflicts(
                $request->teacher_id,
                $request->day_of_week,
                $request->start_time,
                $request->end_time,
                $request->academic_year_id,
                $request->term_id,
                $request->exclude_id
            );
            
            return response()->json([
                'success' => true,
                'data' => [
                    'has_conflicts' => $conflicts->count() > 0,
                    'conflicts' => $conflicts
                ],
                'message' => 'Time conflicts checked successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check time conflicts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get timetable statistics
     *
     * @return JsonResponse
     * @group Teachers
    */
    public function getStatistics(): JsonResponse
    {
        try {
            $statistics = $this->timetableRepository->getTimetableStatistics();
            
            return response()->json([
                'success' => true,
                'data' => $statistics,
                'message' => 'Timetable statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve timetable statistics: ' . $e->getMessage()
            ], 500);
        }
    }
} 