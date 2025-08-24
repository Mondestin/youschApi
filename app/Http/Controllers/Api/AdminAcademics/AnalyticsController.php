<?php

namespace App\Http\Controllers\Api\AdminAcademics;

use App\Http\Controllers\Controller;
use App\Models\AdminAcademics\School;
use App\Models\AdminAcademics\Campus;
use App\Models\AdminAcademics\Faculty;
use App\Models\AdminAcademics\Department;
use App\Models\AdminAcademics\Course;
use App\Models\AdminAcademics\Subject;
use App\Models\AdminAcademics\ClassRoom;
use App\Models\AdminAcademics\AcademicYear;
use App\Models\AdminAcademics\Term;
use App\Models\AdminAcademics\StudentEnrollment;
use App\Models\AdminAcademics\StudentGrade;
use App\Models\AdminAcademics\TeacherAssignment;
use App\Models\AdminAcademics\Timetable;
use App\Models\AdminAcademics\Exam;
use App\Models\AdminAcademics\Announcement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Get comprehensive school analytics.
     */
    public function schoolAnalytics(School $school, Request $request): JsonResponse
    {
        try {
            $academicYearId = $request->get('academic_year_id');
            $termId = $request->get('term_id');

            $analytics = [
                'school_info' => [
                    'name' => $school->name,
                    'total_campuses' => $school->campuses()->count(),
                    'total_faculties' => $school->faculties()->count(),
                    'total_departments' => $school->departments()->count(),
                ],
                'academic_overview' => [
                    'total_courses' => $school->courses()->count(),
                    'total_subjects' => $school->subjects()->count(),
                    'total_classes' => $school->classes()->count(),
                    'total_students' => $school->studentEnrollments()
                        ->when($academicYearId, function($query) use ($academicYearId) {
                            return $query->where('academic_year_id', $academicYearId);
                        })
                        ->where('status', 'enrolled')
                        ->count(),
                    'total_teachers' => $school->teacherAssignments()
                        ->when($academicYearId, function($query) use ($academicYearId) {
                            return $query->where('academic_year_id', $academicYearId);
                        })
                        ->where('is_active', true)
                        ->distinct('teacher_id')
                        ->count(),
                ],
                'enrollment_trends' => $this->getEnrollmentTrends($school, $academicYearId),
                'performance_metrics' => $this->getPerformanceMetrics($school, $academicYearId, $termId),
                'faculty_distribution' => $this->getFacultyDistribution($school),
                'department_performance' => $this->getDepartmentPerformance($school, $academicYearId),
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics,
                'message' => 'School analytics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve school analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get campus analytics.
     */
    public function campusAnalytics(Campus $campus, Request $request): JsonResponse
    {
        try {
            $academicYearId = $request->get('academic_year_id');

            $analytics = [
                'campus_info' => [
                    'name' => $campus->name,
                    'school' => $campus->school->name,
                ],
                'academic_overview' => [
                    'total_classes' => $campus->classes()->count(),
                    'total_students' => $campus->studentEnrollments()
                        ->when($academicYearId, function($query) use ($academicYearId) {
                            return $query->where('academic_year_id', $academicYearId);
                        })
                        ->where('status', 'enrolled')
                        ->count(),
                    'total_teachers' => $campus->teacherAssignments()
                        ->when($academicYearId, function($query) use ($academicYearId) {
                            return $query->where('academic_year_id', $academicYearId);
                        })
                        ->where('is_active', true)
                        ->distinct('teacher_id')
                        ->count(),
                ],
                'class_distribution' => $this->getClassDistribution($campus),
                'enrollment_capacity' => $this->getEnrollmentCapacity($campus, $academicYearId),
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics,
                'message' => 'Campus analytics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve campus analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get faculty analytics.
     */
    public function facultyAnalytics(Faculty $faculty, Request $request): JsonResponse
    {
        try {
            $academicYearId = $request->get('academic_year_id');

            $analytics = [
                'faculty_info' => [
                    'name' => $faculty->name,
                    'school' => $faculty->school->name,
                ],
                'academic_overview' => [
                    'total_departments' => $faculty->departments()->count(),
                    'total_courses' => $faculty->courses()->count(),
                    'total_subjects' => $faculty->subjects()->count(),
                    'total_classes' => $faculty->classes()->count(),
                    'total_students' => $faculty->studentEnrollments()
                        ->when($academicYearId, function($query) use ($academicYearId) {
                            return $query->where('academic_year_id', $academicYearId);
                        })
                        ->where('status', 'enrolled')
                        ->count(),
                ],
                'department_distribution' => $this->getDepartmentDistribution($faculty),
                'course_enrollment' => $this->getCourseEnrollment($faculty, $academicYearId),
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics,
                'message' => 'Faculty analytics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve faculty analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get department analytics.
     */
    public function departmentAnalytics(Department $department, Request $request): JsonResponse
    {
        try {
            $academicYearId = $request->get('academic_year_id');

            $analytics = [
                'department_info' => [
                    'name' => $department->name,
                    'faculty' => $department->faculty->name,
                    'school' => $department->faculty->school->name,
                    'head' => $department->head ? $department->head->name : 'Not Assigned',
                ],
                'academic_overview' => [
                    'total_courses' => $department->courses()->count(),
                    'total_subjects' => $department->subjects()->count(),
                    'total_classes' => $department->classes()->count(),
                    'total_students' => $department->studentEnrollments()
                        ->when($academicYearId, function($query) use ($academicYearId) {
                            return $query->where('academic_year_id', $academicYearId);
                        })
                        ->where('status', 'enrolled')
                        ->count(),
                    'total_teachers' => $department->teacherAssignments()
                        ->when($academicYearId, function($query) use ($academicYearId) {
                            return $query->where('academic_year_id', $academicYearId);
                        })
                        ->where('is_active', true)
                        ->distinct('teacher_id')
                        ->count(),
                ],
                'course_performance' => $this->getCoursePerformance($department, $academicYearId),
                'teacher_workload' => $this->getTeacherWorkload($department, $academicYearId),
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics,
                'message' => 'Department analytics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve department analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get course analytics.
     */
    public function courseAnalytics(Course $course, Request $request): JsonResponse
    {
        try {
            $academicYearId = $request->get('academic_year_id');

            $analytics = [
                'course_info' => [
                    'name' => $course->name,
                    'code' => $course->code,
                    'department' => $course->department->name,
                    'faculty' => $course->department->faculty->name,
                ],
                'academic_overview' => [
                    'total_subjects' => $course->subjects()->count(),
                    'total_classes' => $course->classes()->count(),
                    'total_students' => $course->studentEnrollments()
                        ->when($academicYearId, function($query) use ($academicYearId) {
                            return $query->where('academic_year_id', $academicYearId);
                        })
                        ->where('status', 'enrolled')
                        ->count(),
                ],
                'subject_distribution' => $this->getSubjectDistribution($course),
                'enrollment_trends' => $this->getCourseEnrollmentTrends($course),
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics,
                'message' => 'Course analytics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve course analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get class analytics.
     */
    public function classAnalytics(ClassRoom $class, Request $request): JsonResponse
    {
        try {
            $academicYearId = $request->get('academic_year_id');

            $analytics = [
                'class_info' => [
                    'name' => $class->name,
                    'campus' => $class->campus->name,
                    'course' => $class->course->name,
                    'capacity' => $class->capacity,
                ],
                'academic_overview' => [
                    'total_subjects' => $class->subjects()->count(),
                    'total_students' => $class->studentEnrollments()
                        ->when($academicYearId, function($query) use ($academicYearId) {
                            return $query->where('academic_year_id', $academicYearId);
                        })
                        ->where('status', 'enrolled')
                        ->count(),
                    'total_teachers' => $class->teacherAssignments()
                        ->when($academicYearId, function($query) use ($academicYearId) {
                            return $query->where('academic_year_id', $academicYearId);
                        })
                        ->where('is_active', true)
                        ->distinct('teacher_id')
                        ->count(),
                    'enrollment_rate' => $this->calculateEnrollmentRate($class, $academicYearId),
                ],
                'subject_performance' => $this->getClassSubjectPerformance($class, $academicYearId),
                'timetable_coverage' => $this->getTimetableCoverage($class, $academicYearId),
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics,
                'message' => 'Class analytics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve class analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get teacher workload analytics.
     */
    public function teacherWorkloadAnalytics(Request $request): JsonResponse
    {
        try {
            $teacherId = $request->get('teacher_id');
            $academicYearId = $request->get('academic_year_id');

            if (!$teacherId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher ID is required'
                ], 422);
            }

            $query = TeacherAssignment::where('teacher_id', $teacherId)
                                    ->where('is_active', true);

            if ($academicYearId) {
                $query->where('academic_year_id', $academicYearId);
            }

            $assignments = $query->with(['classRoom', 'subject', 'academicYear'])->get();

            $analytics = [
                'teacher_info' => [
                    'id' => $teacherId,
                    'total_assignments' => $assignments->count(),
                    'total_classes' => $assignments->unique('class_id')->count(),
                    'total_subjects' => $assignments->unique('subject_id')->count(),
                ],
                'workload_distribution' => [
                    'primary_teacher' => $assignments->where('role', 'primary_teacher')->count(),
                    'assistant_teacher' => $assignments->where('role', 'assistant_teacher')->count(),
                    'substitute_teacher' => $assignments->where('role', 'substitute_teacher')->count(),
                ],
                'weekly_hours' => $assignments->sum('weekly_hours'),
                'assignments_by_academic_year' => $assignments->groupBy('academic_year_id')
                    ->map(function($yearAssignments) {
                        return [
                            'total_classes' => $yearAssignments->unique('class_id')->count(),
                            'total_subjects' => $yearAssignments->unique('subject_id')->count(),
                            'weekly_hours' => $yearAssignments->sum('weekly_hours'),
                        ];
                    }),
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics,
                'message' => 'Teacher workload analytics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve teacher workload analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student performance analytics.
     */
    public function studentPerformanceAnalytics(Request $request): JsonResponse
    {
        try {
            $studentId = $request->get('student_id');
            $academicYearId = $request->get('academic_year_id');
            $termId = $request->get('term_id');

            if (!$studentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student ID is required'
                ], 422);
            }

            $query = StudentGrade::where('student_id', $studentId);

            if ($academicYearId) {
                $query->where('academic_year_id', $academicYearId);
            }

            if ($termId) {
                $query->where('term_id', $termId);
            }

            $grades = $query->with(['subject.course.department.faculty', 'academicYear', 'term'])->get();

            $analytics = [
                'student_info' => [
                    'id' => $studentId,
                    'total_subjects' => $grades->unique('subject_id')->count(),
                    'total_grades' => $grades->count(),
                ],
                'performance_summary' => [
                    'average_percentage' => $grades->avg('percentage'),
                    'highest_percentage' => $grades->max('percentage'),
                    'lowest_percentage' => $grades->min('percentage'),
                    'passing_subjects' => $grades->where('percentage', '>=', 50)->count(),
                    'failing_subjects' => $grades->where('percentage', '<', 50)->count(),
                ],
                'performance_by_subject' => $grades->groupBy('subject_id')
                    ->map(function($subjectGrades) {
                        return [
                            'subject_name' => $subjectGrades->first()->subject->name,
                            'average_percentage' => $subjectGrades->avg('percentage'),
                            'total_grades' => $subjectGrades->count(),
                        ];
                    }),
                'performance_by_term' => $grades->groupBy('term_id')
                    ->map(function($termGrades) {
                        return [
                            'term_name' => $termGrades->first()->term ? $termGrades->first()->term->name : 'No Term',
                            'average_percentage' => $termGrades->avg('percentage'),
                            'total_subjects' => $termGrades->unique('subject_id')->count(),
                        ];
                    }),
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics,
                'message' => 'Student performance analytics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve student performance analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system-wide analytics.
     */
    public function systemAnalytics(Request $request): JsonResponse
    {
        try {
            $schoolId = $request->get('school_id');
            $academicYearId = $request->get('academic_year_id');

            $query = School::query();
            if ($schoolId) {
                $query->where('id', $schoolId);
            }

            $schools = $query->get();

            $analytics = [
                'system_overview' => [
                    'total_schools' => $schools->count(),
                    'total_campuses' => $schools->sum(function($school) {
                        return $school->campuses()->count();
                    }),
                    'total_faculties' => $schools->sum(function($school) {
                        return $school->faculties()->count();
                    }),
                    'total_departments' => $schools->sum(function($school) {
                        return $school->departments()->count();
                    }),
                    'total_courses' => $schools->sum(function($school) {
                        return $school->courses()->count();
                    }),
                    'total_subjects' => $schools->sum(function($school) {
                        return $school->subjects()->count();
                    }),
                    'total_classes' => $schools->sum(function($school) {
                        return $school->classes()->count();
                    }),
                ],
                'academic_year_summary' => $this->getAcademicYearSummary($academicYearId),
                'enrollment_summary' => $this->getSystemEnrollmentSummary($academicYearId),
                'performance_summary' => $this->getSystemPerformanceSummary($academicYearId),
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics,
                'message' => 'System analytics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve system analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Helper methods for analytics calculations
    private function getEnrollmentTrends($school, $academicYearId)
    {
        $query = $school->studentEnrollments()->where('status', 'enrolled');
        
        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        return [
            'total_enrollments' => $query->count(),
            'enrollments_by_academic_year' => $query->groupBy('academic_year_id')
                ->map(function($enrollments) {
                    return [
                        'academic_year' => $enrollments->first()->academicYear->name,
                        'count' => $enrollments->count(),
                    ];
                }),
        ];
    }

    private function getPerformanceMetrics($school, $academicYearId, $termId)
    {
        $query = $school->studentGrades();
        
        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }
        
        if ($termId) {
            $query->where('term_id', $termId);
        }

        $grades = $query->get();

        return [
            'total_grades' => $grades->count(),
            'average_percentage' => $grades->avg('percentage'),
            'passing_rate' => $grades->where('percentage', '>=', 50)->count() / max($grades->count(), 1) * 100,
        ];
    }

    private function getFacultyDistribution($school)
    {
        return $school->faculties()->withCount(['departments', 'courses', 'subjects'])->get()
            ->map(function($faculty) {
                return [
                    'name' => $faculty->name,
                    'departments_count' => $faculty->departments_count,
                    'courses_count' => $faculty->courses_count,
                    'subjects_count' => $faculty->subjects_count,
                ];
            });
    }

    private function getDepartmentPerformance($school, $academicYearId)
    {
        return $school->departments()->with(['faculty'])->get()
            ->map(function($department) use ($academicYearId) {
                $enrollments = $department->studentEnrollments()
                    ->when($academicYearId, function($query) use ($academicYearId) {
                        return $query->where('academic_year_id', $academicYearId);
                    })
                    ->where('status', 'enrolled');

                return [
                    'name' => $department->name,
                    'faculty' => $department->faculty->name,
                    'courses_count' => $department->courses()->count(),
                    'students_count' => $enrollments->count(),
                ];
            });
    }

    private function getClassDistribution($campus)
    {
        return $campus->classes()->with(['course.department.faculty'])->get()
            ->map(function($class) {
                return [
                    'name' => $class->name,
                    'course' => $class->course->name,
                    'department' => $class->course->department->name,
                    'faculty' => $class->course->department->faculty->name,
                    'capacity' => $class->capacity,
                ];
            });
    }

    private function getEnrollmentCapacity($campus, $academicYearId)
    {
        $classes = $campus->classes()->get();
        $totalCapacity = $classes->sum('capacity');
        
        $totalEnrollments = $campus->studentEnrollments()
            ->when($academicYearId, function($query) use ($academicYearId) {
                return $query->where('academic_year_id', $academicYearId);
            })
            ->where('status', 'enrolled')
            ->count();

        return [
            'total_capacity' => $totalCapacity,
            'total_enrollments' => $totalEnrollments,
            'utilization_rate' => $totalCapacity > 0 ? ($totalEnrollments / $totalCapacity) * 100 : 0,
        ];
    }

    private function getDepartmentDistribution($faculty)
    {
        return $faculty->departments()->withCount(['courses', 'subjects'])->get()
            ->map(function($department) {
                return [
                    'name' => $department->name,
                    'courses_count' => $department->courses_count,
                    'subjects_count' => $department->subjects_count,
                ];
            });
    }

    private function getCourseEnrollment($faculty, $academicYearId)
    {
        return $faculty->courses()->with(['department'])->get()
            ->map(function($course) use ($academicYearId) {
                $enrollments = $course->studentEnrollments()
                    ->when($academicYearId, function($query) use ($academicYearId) {
                        return $query->where('academic_year_id', $academicYearId);
                    })
                    ->where('status', 'enrolled');

                return [
                    'name' => $course->name,
                    'code' => $course->code,
                    'department' => $course->department->name,
                    'students_count' => $enrollments->count(),
                ];
            });
    }

    private function getCoursePerformance($department, $academicYearId)
    {
        return $department->courses()->get()
            ->map(function($course) use ($academicYearId) {
                $grades = $course->studentGrades()
                    ->when($academicYearId, function($query) use ($academicYearId) {
                        return $query->where('academic_year_id', $academicYearId);
                    });

                return [
                    'name' => $course->name,
                    'code' => $course->code,
                    'average_percentage' => $grades->avg('percentage'),
                    'total_grades' => $grades->count(),
                ];
            });
    }

    private function getTeacherWorkload($department, $academicYearId)
    {
        return $department->teacherAssignments()
            ->when($academicYearId, function($query) use ($academicYearId) {
                return $query->where('academic_year_id', $academicYearId);
            })
            ->where('is_active', true)
            ->with(['teacher', 'subject'])
            ->get()
            ->groupBy('teacher_id')
            ->map(function($assignments) {
                return [
                    'teacher_name' => $assignments->first()->teacher->name,
                    'total_assignments' => $assignments->count(),
                    'total_weekly_hours' => $assignments->sum('weekly_hours'),
                    'subjects' => $assignments->pluck('subject.name')->unique(),
                ];
            });
    }

    private function getSubjectDistribution($course)
    {
        return $course->subjects()->withCount(['labs', 'exams'])->get()
            ->map(function($subject) {
                return [
                    'name' => $subject->name,
                    'code' => $subject->code,
                    'labs_count' => $subject->labs_count,
                    'exams_count' => $subject->exams_count,
                ];
            });
    }

    private function getCourseEnrollmentTrends($course)
    {
        return $course->studentEnrollments()
            ->where('status', 'enrolled')
            ->groupBy('academic_year_id')
            ->with('academicYear')
            ->get()
            ->map(function($enrollments) {
                return [
                    'academic_year' => $enrollments->first()->academicYear->name,
                    'enrollments_count' => $enrollments->count(),
                ];
            });
    }

    private function calculateEnrollmentRate($class, $academicYearId)
    {
        $enrollments = $class->studentEnrollments()
            ->when($academicYearId, function($query) use ($academicYearId) {
                return $query->where('academic_year_id', $academicYearId);
            })
            ->where('status', 'enrolled')
            ->count();

        return $class->capacity > 0 ? ($enrollments / $class->capacity) * 100 : 0;
    }

    private function getClassSubjectPerformance($class, $academicYearId)
    {
        return $class->studentGrades()
            ->when($academicYearId, function($query) use ($academicYearId) {
                return $query->where('academic_year_id', $academicYearId);
            })
            ->with('subject')
            ->get()
            ->groupBy('subject_id')
            ->map(function($grades) {
                return [
                    'subject_name' => $grades->first()->subject->name,
                    'average_percentage' => $grades->avg('percentage'),
                    'total_grades' => $grades->count(),
                ];
            });
    }

    private function getTimetableCoverage($class, $academicYearId)
    {
        $timetables = $class->timetables()
            ->when($academicYearId, function($query) use ($academicYearId) {
                return $query->where('academic_year_id', $academicYearId);
            });

        return [
            'total_sessions' => $timetables->count(),
            'subjects_covered' => $timetables->distinct('subject_id')->count(),
            'teachers_involved' => $timetables->distinct('teacher_id')->count(),
        ];
    }

    private function getAcademicYearSummary($academicYearId)
    {
        if (!$academicYearId) {
            return AcademicYear::with('school')->get()
                ->map(function($year) {
                    return [
                        'name' => $year->name,
                        'school' => $year->school->name,
                        'is_active' => $year->is_active,
                    ];
                });
        }

        $year = AcademicYear::with('school')->find($academicYearId);
        return [
            'name' => $year->name,
            'school' => $year->school->name,
            'is_active' => $year->is_active,
            'terms_count' => $year->terms()->count(),
        ];
    }

    private function getSystemEnrollmentSummary($academicYearId)
    {
        $query = StudentEnrollment::where('status', 'enrolled');
        
        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        $enrollments = $query->get();

        return [
            'total_enrollments' => $enrollments->count(),
            'enrollments_by_status' => $enrollments->groupBy('status')
                ->map(function($statusEnrollments) {
                    return $statusEnrollments->count();
                }),
        ];
    }

    private function getSystemPerformanceSummary($academicYearId)
    {
        $query = StudentGrade::query();
        
        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        $grades = $query->get();

        return [
            'total_grades' => $grades->count(),
            'average_percentage' => $grades->avg('percentage'),
            'passing_rate' => $grades->where('percentage', '>=', 50)->count() / max($grades->count(), 1) * 100,
        ];
    }
} 