<?php

namespace App\Http\Controllers\Api\AdminAcademics;

use App\Http\Controllers\Controller;
use App\Models\AdminAcademics\StudentGrade;
use App\Models\AdminAcademics\Subject;
use App\Models\AdminAcademics\ClassRoom;
use App\Models\AdminAcademics\Exam;
use App\Models\AdminAcademics\AcademicYear;
use App\Models\AdminAcademics\Term;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class StudentGradeController extends Controller
{
    /**
     * Display a listing of student grades.
     */
    public function index(Request $request): JsonResponse
    {
        $query = StudentGrade::with([
            'student',
            'subject.course.department.faculty.school',
            'classRoom.campus',
            'exam',
            'academicYear',
            'term'
        ]);

        // Filter by student if provided
        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Filter by subject if provided
        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Filter by class if provided
        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by exam if provided
        if ($request->has('exam_id')) {
            $query->where('exam_id', $request->exam_id);
        }

        // Filter by academic year if provided
        if ($request->has('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        // Filter by term if provided
        if ($request->has('term_id')) {
            $query->where('term_id', $request->term_id);
        }

        // Filter by school if provided
        if ($request->has('school_id')) {
            $query->whereHas('subject.course.department.faculty', function($q) use ($request) {
                $q->where('school_id', $request->school_id);
            });
        }

        $grades = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $grades,
            'message' => 'Student grades retrieved successfully'
        ]);
    }

    /**
     * Store a newly created student grade.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'student_id' => 'required|exists:users,id',
                'subject_id' => 'required|exists:subjects,id',
                'class_id' => 'required|exists:classes,id',
                'exam_id' => 'nullable|exists:exams,id',
                'academic_year_id' => 'required|exists:academic_years,id',
                'term_id' => 'nullable|exists:terms,id',
                'score' => 'required|numeric|min:0',
                'max_score' => 'required|numeric|min:0',
                'remarks' => 'nullable|string',
            ]);

            // Check if student is enrolled in the class
            $enrollment = DB::table('student_enrollments')
                ->where('student_id', $validated['student_id'])
                ->where('class_id', $validated['class_id'])
                ->where('academic_year_id', $validated['academic_year_id'])
                ->where('status', 'enrolled')
                ->exists();

            if (!$enrollment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student is not enrolled in this class for the specified academic year'
                ], 422);
            }

            // Check if grade already exists for this student, subject, class, and academic year
            $existingGrade = StudentGrade::where('student_id', $validated['student_id'])
                ->where('subject_id', $validated['subject_id'])
                ->where('class_id', $validated['class_id'])
                ->where('academic_year_id', $validated['academic_year_id'])
                ->where('term_id', $validated['term_id'])
                ->exists();

            if ($existingGrade) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grade already exists for this student, subject, class, and academic year'
                ], 422);
            }

            // Calculate percentage
            $percentage = ($validated['score'] / $validated['max_score']) * 100;

            // Create the grade record
            $grade = StudentGrade::create([
                'student_id' => $validated['student_id'],
                'subject_id' => $validated['subject_id'],
                'class_id' => $validated['class_id'],
                'exam_id' => $validated['exam_id'],
                'academic_year_id' => $validated['academic_year_id'],
                'term_id' => $validated['term_id'],
                'score' => $validated['score'],
                'max_score' => $validated['max_score'],
                'percentage' => $percentage,
                'remarks' => $validated['remarks'],
            ]);

            return response()->json([
                'success' => true,
                'data' => $grade->load([
                    'student',
                    'subject.course.department.faculty.school',
                    'classRoom.campus',
                    'exam',
                    'academicYear',
                    'term'
                ]),
                'message' => 'Student grade created successfully'
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
                'message' => 'Failed to create student grade',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified student grade.
     */
    public function show(StudentGrade $grade): JsonResponse
    {
        $grade->load([
            'student',
            'subject.course.department.faculty.school',
            'classRoom.campus',
            'exam',
            'academicYear',
            'term'
        ]);

        return response()->json([
            'success' => true,
            'data' => $grade,
            'message' => 'Student grade retrieved successfully'
        ]);
    }

    /**
     * Update the specified student grade.
     */
    public function update(Request $request, StudentGrade $grade): JsonResponse
    {
        try {
            $validated = $request->validate([
                'student_id' => 'sometimes|required|exists:users,id',
                'subject_id' => 'sometimes|required|exists:subjects,id',
                'class_id' => 'sometimes|required|exists:classes,id',
                'exam_id' => 'nullable|exists:exams,id',
                'academic_year_id' => 'sometimes|required|exists:academic_years,id',
                'term_id' => 'nullable|exists:terms,id',
                'score' => 'sometimes|required|numeric|min:0',
                'max_score' => 'sometimes|required|numeric|min:0',
                'remarks' => 'nullable|string',
            ]);

            // Check for conflicts if key fields are being changed
            if (isset($validated['student_id']) || isset($validated['subject_id']) || 
                isset($validated['class_id']) || isset($validated['academic_year_id']) || 
                isset($validated['term_id'])) {
                
                $studentId = $validated['student_id'] ?? $grade->student_id;
                $subjectId = $validated['subject_id'] ?? $grade->subject_id;
                $classId = $validated['class_id'] ?? $grade->class_id;
                $academicYearId = $validated['academic_year_id'] ?? $grade->academic_year_id;
                $termId = $validated['term_id'] ?? $grade->term_id;

                $existingGrade = StudentGrade::where('student_id', $studentId)
                    ->where('subject_id', $subjectId)
                    ->where('class_id', $classId)
                    ->where('academic_year_id', $academicYearId)
                    ->where('term_id', $termId)
                    ->where('id', '!=', $grade->id)
                    ->exists();

                if ($existingGrade) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Grade already exists for this student, subject, class, and academic year'
                    ], 422);
                }
            }

            // Recalculate percentage if score or max_score is changed
            if (isset($validated['score']) || isset($validated['max_score'])) {
                $score = $validated['score'] ?? $grade->score;
                $maxScore = $validated['max_score'] ?? $grade->max_score;
                $validated['percentage'] = ($score / $maxScore) * 100;
            }

            $grade->update($validated);

            return response()->json([
                'success' => true,
                'data' => $grade->fresh()->load([
                    'student',
                    'subject.course.department.faculty.school',
                    'classRoom.campus',
                    'exam',
                    'academicYear',
                    'term'
                ]),
                'message' => 'Student grade updated successfully'
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
                'message' => 'Failed to update student grade',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified student grade.
     */
    public function destroy(StudentGrade $grade): JsonResponse
    {
        try {
            $grade->delete();

            return response()->json([
                'success' => true,
                'message' => 'Student grade deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete student grade',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk create grades.
     */
    public function bulkCreate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'grades' => 'required|array|min:1',
                'grades.*.student_id' => 'required|exists:users,id',
                'grades.*.subject_id' => 'required|exists:subjects,id',
                'grades.*.class_id' => 'required|exists:classes,id',
                'grades.*.exam_id' => 'nullable|exists:exams,id',
                'grades.*.academic_year_id' => 'required|exists:academic_years,id',
                'grades.*.term_id' => 'nullable|exists:terms,id',
                'grades.*.score' => 'required|numeric|min:0',
                'grades.*.max_score' => 'required|numeric|min:0',
                'grades.*.remarks' => 'nullable|string',
            ]);

            $createdGrades = [];
            $errors = [];

            DB::transaction(function() use ($validated, &$createdGrades, &$errors) {
                foreach ($validated['grades'] as $gradeData) {
                    try {
                        // Check if student is enrolled
                        $enrollment = DB::table('student_enrollments')
                            ->where('student_id', $gradeData['student_id'])
                            ->where('class_id', $gradeData['class_id'])
                            ->where('academic_year_id', $gradeData['academic_year_id'])
                            ->where('status', 'enrolled')
                            ->exists();

                        if (!$enrollment) {
                            $errors[] = "Student ID {$gradeData['student_id']} is not enrolled in class {$gradeData['class_id']}";
                            continue;
                        }

                        // Check if grade already exists
                        $existingGrade = StudentGrade::where('student_id', $gradeData['student_id'])
                            ->where('subject_id', $gradeData['subject_id'])
                            ->where('class_id', $gradeData['class_id'])
                            ->where('academic_year_id', $gradeData['academic_year_id'])
                            ->where('term_id', $gradeData['term_id'])
                            ->exists();

                        if ($existingGrade) {
                            $errors[] = "Grade already exists for student {$gradeData['student_id']} in subject {$gradeData['subject_id']}";
                            continue;
                        }

                        // Calculate percentage
                        $percentage = ($gradeData['score'] / $gradeData['max_score']) * 100;

                        $grade = StudentGrade::create([
                            'student_id' => $gradeData['student_id'],
                            'subject_id' => $gradeData['subject_id'],
                            'class_id' => $gradeData['class_id'],
                            'exam_id' => $gradeData['exam_id'] ?? null,
                            'academic_year_id' => $gradeData['academic_year_id'],
                            'term_id' => $gradeData['term_id'] ?? null,
                            'score' => $gradeData['score'],
                            'max_score' => $gradeData['max_score'],
                            'percentage' => $percentage,
                            'remarks' => $gradeData['remarks'] ?? null,
                        ]);

                        $createdGrades[] = $grade;

                    } catch (\Exception $e) {
                        $errors[] = "Failed to create grade for student {$gradeData['student_id']}: " . $e->getMessage();
                    }
                }
            });

            $response = [
                'success' => true,
                'data' => [
                    'grades' => $createdGrades,
                    'errors' => $errors
                ],
                'message' => 'Bulk grade creation completed'
            ];

            if (!empty($errors)) {
                $response['message'] .= ' with some errors';
            }

            return response()->json($response, 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to perform bulk grade creation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get grades by student.
     */
    public function byStudent(User $student, Request $request): JsonResponse
    {
        $query = StudentGrade::where('student_id', $student->id)
                            ->with([
                                'subject.course.department.faculty.school',
                                'classRoom.campus',
                                'exam',
                                'academicYear',
                                'term'
                            ]);

        // Filter by academic year if provided
        if ($request->has('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        // Filter by term if provided
        if ($request->has('term_id')) {
            $query->where('term_id', $request->term_id);
        }

        // Filter by subject if provided
        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        $grades = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $grades,
            'message' => 'Student grades retrieved successfully'
        ]);
    }

    /**
     * Get grades by class.
     */
    public function byClass(ClassRoom $class, Request $request): JsonResponse
    {
        $query = $class->studentGrades()
                      ->with([
                          'student',
                          'subject.course',
                          'exam',
                          'academicYear',
                          'term'
                      ]);

        // Filter by academic year if provided
        if ($request->has('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        // Filter by term if provided
        if ($request->has('term_id')) {
            $query->where('term_id', $request->term_id);
        }

        // Filter by subject if provided
        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        $grades = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $grades,
            'message' => 'Class grades retrieved successfully'
        ]);
    }

    /**
     * Get student transcript.
     */
    public function transcript(User $student, Request $request): JsonResponse
    {
        $query = StudentGrade::where('student_id', $student->id)
                            ->with([
                                'subject.course.department.faculty.school',
                                'classRoom.campus',
                                'exam',
                                'academicYear',
                                'term'
                            ]);

        // Filter by academic year if provided
        if ($request->has('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        $grades = $query->orderBy('academic_year_id', 'desc')
                       ->orderBy('term_id', 'asc')
                       ->orderBy('subject_id', 'asc')
                       ->get();

        // Group by academic year and term
        $transcript = $grades->groupBy('academic_year_id')
                           ->map(function($yearGrades) {
                               return $yearGrades->groupBy('term_id');
                           });

        return response()->json([
            'success' => true,
            'data' => [
                'student' => $student,
                'transcript' => $transcript,
                'summary' => [
                    'total_subjects' => $grades->unique('subject_id')->count(),
                    'total_grades' => $grades->count(),
                    'average_percentage' => $grades->avg('percentage'),
                    'highest_percentage' => $grades->max('percentage'),
                    'lowest_percentage' => $grades->min('percentage'),
                ]
            ],
            'message' => 'Student transcript retrieved successfully'
        ]);
    }
} 