<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminAcademics\{
    SchoolController,
    CampusController,
    FacultyController,
    DepartmentController,
    CourseController,
    SubjectController,
    ClassController,
    AcademicYearController,
    TermController,
    TimetableController,
    ExamController,
    GradingSchemeController,
    StudentEnrollmentController,
    StudentGradeController,
    TeacherAssignmentController,
    AnnouncementController,
    AnalyticsController
};

class AcademicRouteService
{
    /**
     * Register all academic management routes.
     */
    public static function registerRoutes(): void
    {
        self::registerSchoolRoutes();
        self::registerAcademicYearRoutes();
        self::registerFacultyRoutes();
        self::registerCourseRoutes();
        self::registerClassRoutes();
        self::registerTimetableRoutes();
        self::registerExamRoutes();
        self::registerEnrollmentRoutes();
        self::registerTeacherRoutes();
        self::registerAnnouncementRoutes();
        self::registerAnalyticsRoutes();
    }

    /**
     * Register school and campus management routes.
     */
    private static function registerSchoolRoutes(): void
    {
        Route::prefix('schools')->name('schools.')->group(function () {
            Route::get('/', [SchoolController::class, 'index'])->name('index');
            Route::post('/', [SchoolController::class, 'store'])->name('store');
            Route::get('/{school}', [SchoolController::class, 'show'])->name('show');
            Route::put('/{school}', [SchoolController::class, 'update'])->name('update');
            Route::delete('/{school}', [SchoolController::class, 'destroy'])->name('destroy');
            Route::get('/{school}/statistics', [SchoolController::class, 'statistics'])->name('statistics');
            Route::get('/{school}/campuses', [CampusController::class, 'bySchool'])->name('campuses');
        });

        Route::prefix('campuses')->name('campuses.')->group(function () {
            Route::get('/', [CampusController::class, 'index'])->name('index');
            Route::post('/', [CampusController::class, 'store'])->name('store');
            Route::get('/{campus}', [CampusController::class, 'show'])->name('show');
            Route::put('/{campus}', [CampusController::class, 'update'])->name('update');
            Route::delete('/{campus}', [CampusController::class, 'destroy'])->name('destroy');
            Route::get('/{campus}/statistics', [CampusController::class, 'statistics'])->name('statistics');
        });
    }

    /**
     * Register academic year and term routes.
     */
    private static function registerAcademicYearRoutes(): void
    {
        Route::prefix('academic-years')->name('academic-years.')->group(function () {
            Route::get('/', [AcademicYearController::class, 'index'])->name('index');
            Route::post('/', [AcademicYearController::class, 'store'])->name('store');
            Route::get('/{academicYear}', [AcademicYearController::class, 'show'])->name('show');
            Route::put('/{academicYear}', [AcademicYearController::class, 'update'])->name('update');
            Route::delete('/{academicYear}', [AcademicYearController::class, 'destroy'])->name('destroy');
            Route::patch('/{academicYear}/activate', [AcademicYearController::class, 'activate'])->name('activate');
            Route::get('/{academicYear}/statistics', [AcademicYearController::class, 'statistics'])->name('statistics');
            Route::get('/{academicYear}/terms', [TermController::class, 'byAcademicYear'])->name('terms');
        });

        Route::prefix('terms')->name('terms.')->group(function () {
            Route::get('/', [TermController::class, 'index'])->name('index');
            Route::post('/', [TermController::class, 'store'])->name('store');
            Route::get('/{term}', [TermController::class, 'show'])->name('show');
            Route::put('/{term}', [TermController::class, 'update'])->name('update');
            Route::delete('/{term}', [TermController::class, 'destroy'])->name('destroy');
            Route::patch('/{term}/activate', [TermController::class, 'activate'])->name('activate');
            Route::get('/{term}/statistics', [TermController::class, 'statistics'])->name('statistics');
        });
    }

    /**
     * Register faculty and department routes.
     */
    private static function registerFacultyRoutes(): void
    {
        Route::prefix('faculties')->name('faculties.')->group(function () {
            Route::get('/', [FacultyController::class, 'index'])->name('index');
            Route::post('/', [FacultyController::class, 'store'])->name('store');
            Route::get('/{faculty}', [FacultyController::class, 'show'])->name('show');
            Route::put('/{faculty}', [FacultyController::class, 'update'])->name('update');
            Route::delete('/{faculty}', [FacultyController::class, 'destroy'])->name('destroy');
            Route::get('/{faculty}/statistics', [FacultyController::class, 'statistics'])->name('statistics');
            Route::get('/{faculty}/departments', [DepartmentController::class, 'byFaculty'])->name('departments');
        });

        Route::prefix('departments')->name('departments.')->group(function () {
            Route::get('/', [DepartmentController::class, 'index'])->name('index');
            Route::post('/', [DepartmentController::class, 'store'])->name('store');
            Route::get('/{department}', [DepartmentController::class, 'show'])->name('show');
            Route::put('/{department}', [DepartmentController::class, 'update'])->name('update');
            Route::delete('/{department}', [DepartmentController::class, 'destroy'])->name('destroy');
            Route::patch('/{department}/assign-head', [DepartmentController::class, 'assignHead'])->name('assign-head');
            Route::get('/{department}/statistics', [DepartmentController::class, 'statistics'])->name('statistics');
        });
    }

    /**
     * Register course and subject routes.
     */
    private static function registerCourseRoutes(): void
    {
        Route::prefix('courses')->name('courses.')->group(function () {
            Route::get('/', [CourseController::class, 'index'])->name('index');
            Route::post('/', [CourseController::class, 'store'])->name('store');
            Route::get('/{course}', [CourseController::class, 'show'])->name('show');
            Route::put('/{course}', [CourseController::class, 'update'])->name('update');
            Route::delete('/{course}', [CourseController::class, 'destroy'])->name('destroy');
            Route::get('/{course}/statistics', [CourseController::class, 'statistics'])->name('statistics');
            Route::get('/{course}/subjects', [SubjectController::class, 'byCourse'])->name('subjects');
        });

        Route::prefix('subjects')->name('subjects.')->group(function () {
            Route::get('/', [SubjectController::class, 'index'])->name('index');
            Route::post('/', [SubjectController::class, 'store'])->name('store');
            Route::get('/{subject}', [SubjectController::class, 'show'])->name('show');
            Route::put('/{subject}', [SubjectController::class, 'update'])->name('update');
            Route::delete('/{subject}', [SubjectController::class, 'destroy'])->name('destroy');
            Route::patch('/{subject}/assign-coordinator', [SubjectController::class, 'assignCoordinator'])->name('assign-coordinator');
            Route::post('/{subject}/prerequisites', [SubjectController::class, 'addPrerequisites'])->name('add-prerequisites');
            Route::delete('/{subject}/prerequisites/{prerequisite}', [SubjectController::class, 'removePrerequisite'])->name('remove-prerequisite');
            Route::get('/{subject}/statistics', [SubjectController::class, 'statistics'])->name('statistics');
        });
    }

    /**
     * Register class management routes.
     */
    private static function registerClassRoutes(): void
    {
        Route::prefix('classes')->name('classes.')->group(function () {
            Route::get('/', [ClassController::class, 'index'])->name('index');
            Route::post('/', [ClassController::class, 'store'])->name('store');
            Route::get('/{class}', [ClassController::class, 'show'])->name('show');
            Route::put('/{class}', [ClassController::class, 'update'])->name('update');
            Route::delete('/{class}', [ClassController::class, 'destroy'])->name('destroy');
            Route::patch('/{class}/assign-subject', [ClassController::class, 'assignSubject'])->name('assign-subject');
            Route::delete('/{class}/subjects/{subject}', [ClassController::class, 'removeSubject'])->name('remove-subject');
            Route::patch('/{class}/assign-teacher', [ClassController::class, 'assignTeacher'])->name('assign-teacher');
            Route::get('/{class}/statistics', [ClassController::class, 'statistics'])->name('statistics');
        });
    }

    /**
     * Register timetable management routes.
     */
    private static function registerTimetableRoutes(): void
    {
        Route::prefix('timetables')->name('timetables.')->group(function () {
            Route::get('/', [TimetableController::class, 'index'])->name('index');
            Route::post('/', [TimetableController::class, 'store'])->name('store');
            Route::get('/{timetable}', [TimetableController::class, 'show'])->name('show');
            Route::put('/{timetable}', [TimetableController::class, 'update'])->name('update');
            Route::delete('/{timetable}', [TimetableController::class, 'destroy'])->name('destroy');
            Route::get('/class/{class}/timetable', [TimetableController::class, 'classTimetable'])->name('class-timetable');
            Route::get('/teacher/{teacher}/timetable', [TimetableController::class, 'teacherTimetable'])->name('teacher-timetable');
            Route::post('/class/{class}/generate-weekly', [TimetableController::class, 'generateWeekly'])->name('generate-weekly');
        });
    }

    /**
     * Register exam and grading routes.
     */
    private static function registerExamRoutes(): void
    {
        Route::prefix('exams')->name('exams.')->group(function () {
            Route::get('/', [ExamController::class, 'index'])->name('index');
            Route::post('/', [ExamController::class, 'store'])->name('store');
            Route::get('/{exam}', [ExamController::class, 'show'])->name('show');
            Route::put('/{exam}', [ExamController::class, 'update'])->name('update');
            Route::delete('/{exam}', [ExamController::class, 'destroy'])->name('destroy');
            Route::get('/subject/{subject}/exams', [ExamController::class, 'bySubject'])->name('by-subject');
            Route::get('/class/{class}/exams', [ExamController::class, 'byClass'])->name('by-class');
            Route::get('/upcoming', [ExamController::class, 'upcoming'])->name('upcoming');
            Route::get('/{exam}/statistics', [ExamController::class, 'statistics'])->name('statistics');
        });

        Route::prefix('grading-schemes')->name('grading-schemes.')->group(function () {
            Route::get('/', [GradingSchemeController::class, 'index'])->name('index');
            Route::post('/', [GradingSchemeController::class, 'store'])->name('store');
            Route::get('/{gradingScheme}', [GradingSchemeController::class, 'show'])->name('show');
            Route::put('/{gradingScheme}', [GradingSchemeController::class, 'update'])->name('update');
            Route::delete('/{gradingScheme}', [GradingSchemeController::class, 'destroy'])->name('destroy');
            Route::patch('/{gradingScheme}/activate', [GradingSchemeController::class, 'activate'])->name('activate');
            Route::get('/school/{school}/schemes', [GradingSchemeController::class, 'bySchool'])->name('by-school');
            Route::post('/{gradingScheme}/calculate-grade', [GradingSchemeController::class, 'calculateGrade'])->name('calculate-grade');
            Route::get('/{gradingScheme}/statistics', [GradingSchemeController::class, 'statistics'])->name('statistics');
        });
    }

    /**
     * Register student enrollment and grade routes.
     */
    private static function registerEnrollmentRoutes(): void
    {
        Route::prefix('enrollments')->name('enrollments.')->group(function () {
            Route::get('/', [StudentEnrollmentController::class, 'index'])->name('index');
            Route::post('/', [StudentEnrollmentController::class, 'store'])->name('store');
            Route::get('/{enrollment}', [StudentEnrollmentController::class, 'show'])->name('show');
            Route::put('/{enrollment}', [StudentEnrollmentController::class, 'update'])->name('update');
            Route::delete('/{enrollment}', [StudentEnrollmentController::class, 'destroy'])->name('destroy');
            Route::patch('/{enrollment}/change-status', [StudentEnrollmentController::class, 'changeStatus'])->name('change-status');
            Route::post('/bulk-enroll', [StudentEnrollmentController::class, 'bulkEnroll'])->name('bulk-enroll');
            Route::get('/class/{class}/enrollments', [StudentEnrollmentController::class, 'byClass'])->name('by-class');
            Route::get('/student/{student}/enrollments', [StudentEnrollmentController::class, 'byStudent'])->name('by-student');
        });

        Route::prefix('grades')->name('grades.')->group(function () {
            Route::get('/', [StudentGradeController::class, 'index'])->name('index');
            Route::post('/', [StudentGradeController::class, 'store'])->name('store');
            Route::get('/{grade}', [StudentGradeController::class, 'show'])->name('show');
            Route::put('/{grade}', [StudentGradeController::class, 'update'])->name('update');
            Route::delete('/{grade}', [StudentGradeController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-create', [StudentGradeController::class, 'bulkCreate'])->name('bulk-create');
            Route::get('/student/{student}/grades', [StudentGradeController::class, 'byStudent'])->name('by-student');
            Route::get('/class/{class}/grades', [StudentGradeController::class, 'byClass'])->name('by-class');
            Route::get('/student/{student}/transcript', [StudentGradeController::class, 'transcript'])->name('transcript');
        });
    }

    /**
     * Register teacher assignment routes.
     */
    private static function registerTeacherRoutes(): void
    {
        Route::prefix('teacher-assignments')->name('teacher-assignments.')->group(function () {
            Route::get('/', [TeacherAssignmentController::class, 'index'])->name('index');
            Route::post('/', [TeacherAssignmentController::class, 'store'])->name('store');
            Route::get('/{assignment}', [TeacherAssignmentController::class, 'show'])->name('show');
            Route::put('/{assignment}', [TeacherAssignmentController::class, 'update'])->name('update');
            Route::delete('/{assignment}', [TeacherAssignmentController::class, 'destroy'])->name('destroy');
            Route::patch('/{assignment}/deactivate', [TeacherAssignmentController::class, 'deactivate'])->name('deactivate');
            Route::get('/teacher/{teacher}/assignments', [TeacherAssignmentController::class, 'byTeacher'])->name('by-teacher');
            Route::get('/class/{class}/assignments', [TeacherAssignmentController::class, 'byClass'])->name('by-class');
            Route::get('/teacher/{teacher}/workload-summary', [TeacherAssignmentController::class, 'workloadSummary'])->name('workload-summary');
        });
    }

    /**
     * Register announcement routes.
     */
    private static function registerAnnouncementRoutes(): void
    {
        Route::prefix('announcements')->name('announcements.')->group(function () {
            Route::get('/', [AnnouncementController::class, 'index'])->name('index');
            Route::post('/', [AnnouncementController::class, 'store'])->name('store');
            Route::get('/{announcement}', [AnnouncementController::class, 'show'])->name('show');
            Route::put('/{announcement}', [AnnouncementController::class, 'update'])->name('update');
            Route::delete('/{announcement}', [AnnouncementController::class, 'destroy'])->name('destroy');
            Route::patch('/{announcement}/toggle-status', [AnnouncementController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/school/{school}/announcements', [AnnouncementController::class, 'bySchool'])->name('by-school');
            Route::get('/campus/{campus}/announcements', [AnnouncementController::class, 'byCampus'])->name('by-campus');
            Route::get('/class/{class}/announcements', [AnnouncementController::class, 'byClass'])->name('by-class');
            Route::get('/urgent', [AnnouncementController::class, 'urgent'])->name('urgent');
            Route::get('/statistics', [AnnouncementController::class, 'statistics'])->name('statistics');
        });
    }

    /**
     * Register analytics routes.
     */
    private static function registerAnalyticsRoutes(): void
    {
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/school/{school}', [AnalyticsController::class, 'schoolAnalytics'])->name('school');
            Route::get('/campus/{campus}', [AnalyticsController::class, 'campusAnalytics'])->name('campus');
            Route::get('/faculty/{faculty}', [AnalyticsController::class, 'facultyAnalytics'])->name('faculty');
            Route::get('/department/{department}', [AnalyticsController::class, 'departmentAnalytics'])->name('department');
            Route::get('/course/{course}', [AnalyticsController::class, 'courseAnalytics'])->name('course');
            Route::get('/class/{class}', [AnalyticsController::class, 'classAnalytics'])->name('class');
            Route::get('/teacher-workload', [AnalyticsController::class, 'teacherWorkloadAnalytics'])->name('teacher-workload');
            Route::get('/student-performance', [AnalyticsController::class, 'studentPerformanceAnalytics'])->name('student-performance');
            Route::get('/system', [AnalyticsController::class, 'systemAnalytics'])->name('system');
        });
    }
} 