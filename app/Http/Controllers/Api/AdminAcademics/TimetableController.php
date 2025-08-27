<?php

namespace App\Http\Controllers\Api\AdminAcademics;

use App\Http\Controllers\Controller;
use App\Models\AdminAcademics\Timetable;
use App\Models\AdminAcademics\ClassRoom;
use App\Models\AdminAcademics\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class TimetableController extends Controller
{
    /**
     * Display a listing of timetables.
     * @group Admin Academics
    */
    public function index(Request $request): JsonResponse
    {
        $query = Timetable::with(['classRoom.campus.school', 'subject.course', 'teacher']);

        // Filter by class if provided
        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by subject if provided
        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Filter by teacher if provided
        if ($request->has('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        // Filter by date range if provided
        if ($request->has('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        // Filter by school if provided
        if ($request->has('school_id')) {
            $query->whereHas('classRoom.campus', function($q) use ($request) {
                $q->where('school_id', $request->school_id);
            });
        }

        $timetables = $query->orderBy('date', 'asc')
                           ->orderBy('start_time', 'asc')
                           ->get();

        return response()->json([
            'success' => true,
            'data' => $timetables,
            'message' => 'Timetables retrieved successfully'
        ]);
    }

    /**
     * Store a newly created timetable entry.
     * @group Admin Academics
    */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'class_id' => 'required|exists:classes,id',
                'subject_id' => 'required|exists:subjects,id',
                'teacher_id' => 'required|exists:users,id',
                'date' => 'required|date',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'room' => 'nullable|string|max:50',
            ]);

            // Check for time conflicts in the same class on the same date
            $timeConflict = Timetable::where('class_id', $validated['class_id'])
                ->where('date', $validated['date'])
                ->where(function($query) use ($validated) {
                    $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                          ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                          ->orWhere(function($q) use ($validated) {
                              $q->where('start_time', '<=', $validated['start_time'])
                                ->where('end_time', '>=', $validated['end_time']);
                          });
                })
                ->exists();

            if ($timeConflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'Time slot conflicts with existing timetable entry for this class'
                ], 422);
            }

            // Check for teacher conflicts on the same date and time
            $teacherConflict = Timetable::where('teacher_id', $validated['teacher_id'])
                ->where('date', $validated['date'])
                ->where(function($query) use ($validated) {
                    $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                          ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                          ->orWhere(function($q) use ($validated) {
                              $q->where('start_time', '<=', $validated['start_time'])
                                ->where('end_time', '>=', $validated['end_time']);
                          });
                })
                ->exists();

            if ($teacherConflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher has a conflicting timetable entry at this time'
                ], 422);
            }

            $timetable = Timetable::create($validated);

            return response()->json([
                'success' => true,
                'data' => $timetable->load(['classRoom.campus.school', 'subject.course', 'teacher']),
                'message' => 'Timetable entry created successfully'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create timetable entry',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified timetable entry.
     * @group Admin Academics
    */
    public function show(Timetable $timetable): JsonResponse
    {
        $timetable->load([
            'classRoom.campus.school',
            'subject.course.department.faculty',
            'teacher'
        ]);

        return response()->json([
            'success' => true,
            'data' => $timetable,
            'message' => 'Timetable entry retrieved successfully'
        ]);
    }

    /**
     * Update the specified timetable entry.
     * @group Admin Academics
    */
    public function update(Request $request, Timetable $timetable): JsonResponse
    {
        try {
            $validated = $request->validate([
                'class_id' => 'sometimes|required|exists:classes,id',
                'subject_id' => 'sometimes|required|exists:subjects,id',
                'teacher_id' => 'sometimes|required|exists:users,id',
                'date' => 'sometimes|required|date',
                'start_time' => 'sometimes|required|date_format:H:i',
                'end_time' => 'sometimes|required|date_format:H:i|after:start_time',
                'room' => 'nullable|string|max:50',
            ]);

            // Check for conflicts if time/date/class/teacher is being changed
            if (isset($validated['date']) || isset($validated['start_time']) || isset($validated['end_time']) || 
                isset($validated['class_id']) || isset($validated['teacher_id'])) {
                
                $date = $validated['date'] ?? $timetable->date;
                $startTime = $validated['start_time'] ?? $timetable->start_time;
                $endTime = $validated['end_time'] ?? $timetable->end_time;
                $classId = $validated['class_id'] ?? $timetable->class_id;
                $teacherId = $validated['teacher_id'] ?? $timetable->teacher_id;

                // Check for time conflicts in the same class on the same date
                $timeConflict = Timetable::where('class_id', $classId)
                    ->where('date', $date)
                    ->where('id', '!=', $timetable->id)
                    ->where(function($query) use ($startTime, $endTime) {
                        $query->whereBetween('start_time', [$startTime, $endTime])
                              ->orWhereBetween('end_time', [$startTime, $endTime])
                              ->orWhere(function($q) use ($startTime, $endTime) {
                                  $q->where('start_time', '<=', $startTime)
                                    ->where('end_time', '>=', $endTime);
                              });
                    })
                    ->exists();

                if ($timeConflict) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Time slot conflicts with existing timetable entry for this class'
                    ], 422);
                }

                // Check for teacher conflicts on the same date and time
                $teacherConflict = Timetable::where('teacher_id', $teacherId)
                    ->where('date', $date)
                    ->where('id', '!=', $timetable->id)
                    ->where(function($query) use ($startTime, $endTime) {
                        $query->whereBetween('start_time', [$startTime, $endTime])
                              ->orWhereBetween('end_time', [$startTime, $endTime])
                              ->orWhere(function($q) use ($startTime, $endTime) {
                                  $q->where('start_time', '<=', $startTime)
                                    ->where('end_time', '>=', $endTime);
                              });
                    })
                    ->exists();

                if ($teacherConflict) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Teacher has a conflicting timetable entry at this time'
                    ], 422);
                }
            }

            $timetable->update($validated);

            return response()->json([
                'success' => true,
                'data' => $timetable->fresh()->load(['classRoom.campus.school', 'subject.course', 'teacher']),
                'message' => 'Timetable entry updated successfully'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update timetable entry',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified timetable entry.
     * @group Admin Academics
    */
    public function destroy(Timetable $timetable): JsonResponse
    {
        try {
            $timetable->delete();

            return response()->json([
                'success' => true,
                'message' => 'Timetable entry deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete timetable entry',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get class timetable.
     * @group Admin Academics
    */
    public function classTimetable(ClassRoom $class, Request $request): JsonResponse
    {
        $query = $class->timetables()
                      ->with(['subject.course', 'teacher']);

        // Filter by date range if provided
        if ($request->has('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        $timetables = $query->orderBy('date', 'asc')
                           ->orderBy('start_time', 'asc')
                           ->get();

        return response()->json([
            'success' => true,
            'data' => $timetables,
            'message' => 'Class timetable retrieved successfully'
        ]);
    }

    /**
     * Get teacher timetable.
     * @group Admin Academics
    */
    public function teacherTimetable(User $teacher, Request $request): JsonResponse
    {
        $query = Timetable::where('teacher_id', $teacher->id)
                         ->with(['classRoom.campus.school', 'subject.course']);

        // Filter by date range if provided
        if ($request->has('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        $timetables = $query->orderBy('date', 'asc')
                           ->orderBy('start_time', 'asc')
                           ->get();

        return response()->json([
            'success' => true,
            'data' => $timetables,
            'message' => 'Teacher timetable retrieved successfully'
        ]);
    }

    /**
     * Generate weekly timetable for a class.
     * @group Admin Academics
    */
    public function generateWeekly(ClassRoom $class, Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'week_start_date' => 'required|date',
                'subjects' => 'required|array',
                'subjects.*.subject_id' => 'required|exists:subjects,id',
                'subjects.*.teacher_id' => 'required|exists:users,id',
                'subjects.*.day_of_week' => 'required|integer|between:1,7',
                'subjects.*.start_time' => 'required|date_format:H:i',
                'subjects.*.end_time' => 'required|date_format:H:i|after:subjects.*.start_time',
                'subjects.*.room' => 'nullable|string|max:50',
            ]);

            $weekStart = \Carbon\Carbon::parse($validated['week_start_date']);
            $generatedEntries = [];

            DB::transaction(function() use ($validated, $class, $weekStart, &$generatedEntries) {
                foreach ($validated['subjects'] as $subjectData) {
                    $date = $weekStart->copy()->addDays($subjectData['day_of_week'] - 1);

                    // Check for conflicts
                    $conflict = Timetable::where('class_id', $class->id)
                        ->where('date', $date)
                        ->where(function($query) use ($subjectData) {
                            $query->whereBetween('start_time', [$subjectData['start_time'], $subjectData['end_time']])
                                  ->orWhereBetween('end_time', [$subjectData['start_time'], $subjectData['end_time']]);
                        })
                        ->exists();

                    if (!$conflict) {
                        $timetable = Timetable::create([
                            'class_id' => $class->id,
                            'subject_id' => $subjectData['subject_id'],
                            'teacher_id' => $subjectData['teacher_id'],
                            'date' => $date,
                            'start_time' => $subjectData['start_time'],
                            'end_time' => $subjectData['end_time'],
                            'room' => $subjectData['room'] ?? null,
                        ]);

                        $generatedEntries[] = $timetable;
                    }
                }
            });

            return response()->json([
                'success' => true,
                'data' => $generatedEntries,
                'message' => 'Weekly timetable generated successfully'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate weekly timetable',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 