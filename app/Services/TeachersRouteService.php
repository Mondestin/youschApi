<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Teachers\TeacherController;
use App\Http\Controllers\Api\Teachers\TeacherDocumentController;
use App\Http\Controllers\Api\Teachers\TeacherLeaveController;
use App\Http\Controllers\Api\Teachers\TeacherPerformanceController;
use App\Http\Controllers\Api\Teachers\TeacherTimetableController;
use App\Http\Controllers\Api\Teachers\TeacherAssignmentController;

class TeachersRouteService
{
    /**
     * Register all teacher management routes.
     */
    public static function registerRoutes(): void
    {
        self::registerTeacherRoutes();
        self::registerDocumentRoutes();
        self::registerAssignmentRoutes();
        self::registerTimetableRoutes();
        self::registerLeaveRoutes();
        self::registerPerformanceRoutes();
        self::registerBulkOperationRoutes();
        self::registerReportRoutes();
    }

    /**
     * Register teacher profile routes.
     */
    private static function registerTeacherRoutes(): void
    {
        Route::prefix('teachers')->name('teachers.')->group(function () {
            Route::get('/', [TeacherController::class, 'index'])->name('index');
            Route::post('/', [TeacherController::class, 'store'])->name('store');
            Route::get('/{teacher}', [TeacherController::class, 'show'])->name('show');
            Route::put('/{teacher}', [TeacherController::class, 'update'])->name('update');
            Route::delete('/{teacher}', [TeacherController::class, 'destroy'])->name('destroy');
            Route::get('/department/{department}', [TeacherController::class, 'getByDepartment'])->name('by-department');
            Route::get('/faculty/{faculty}', [TeacherController::class, 'getByFaculty'])->name('by-faculty');
            Route::get('/statistics', [TeacherController::class, 'getStatistics'])->name('statistics');
        });
    }

    /**
     * Register teacher document routes.
     */
    private static function registerDocumentRoutes(): void
    {
        Route::prefix('documents')->name('documents.')->group(function () {
            Route::get('/', [TeacherDocumentController::class, 'index'])->name('index');
            Route::post('/', [TeacherDocumentController::class, 'store'])->name('store');
            Route::get('/{document}', [TeacherDocumentController::class, 'show'])->name('show');
            Route::put('/{document}', [TeacherDocumentController::class, 'update'])->name('update');
            Route::delete('/{document}', [TeacherDocumentController::class, 'destroy'])->name('destroy');
            Route::get('/{document}/download', [TeacherDocumentController::class, 'download'])->name('download');
            Route::post('/{document}/approve', [TeacherDocumentController::class, 'approve'])->name('approve');
            Route::post('/{document}/reject', [TeacherDocumentController::class, 'reject'])->name('reject');
            Route::get('/teacher/{teacher}', [TeacherDocumentController::class, 'getByTeacher'])->name('by-teacher');
            Route::get('/type/{type}', [TeacherDocumentController::class, 'getByType'])->name('by-type');
            Route::get('/status/{status}', [TeacherDocumentController::class, 'getByStatus'])->name('by-status');
            Route::get('/pending', [TeacherDocumentController::class, 'getPending'])->name('pending');
            Route::get('/expired', [TeacherDocumentController::class, 'getExpired'])->name('expired');
            Route::get('/statistics', [TeacherDocumentController::class, 'getStatistics'])->name('statistics');
        });
    }

    /**
     * Register teacher assignment routes.
     */
    private static function registerAssignmentRoutes(): void
    {
        Route::prefix('assignments')->name('assignments.')->group(function () {
            Route::get('/', [TeacherAssignmentController::class, 'index'])->name('index');
            Route::post('/', [TeacherAssignmentController::class, 'store'])->name('store');
            Route::get('/{assignment}', [TeacherAssignmentController::class, 'show'])->name('show');
            Route::put('/{assignment}', [TeacherAssignmentController::class, 'update'])->name('update');
            Route::delete('/{assignment}', [TeacherAssignmentController::class, 'destroy'])->name('destroy');
            Route::get('/teacher/{teacher}', [TeacherAssignmentController::class, 'byTeacher'])->name('by-teacher');
            Route::get('/class/{class}', [TeacherAssignmentController::class, 'byClass'])->name('by-class');
            Route::get('/subject/{subject}', [TeacherAssignmentController::class, 'bySubject'])->name('by-subject');
            Route::post('/bulk', [TeacherAssignmentController::class, 'bulkAssign'])->name('bulk-assign');
            Route::get('/statistics', [TeacherAssignmentController::class, 'statistics'])->name('statistics');
        });
    }

    /**
     * Register teacher timetable routes.
     */
    private static function registerTimetableRoutes(): void
    {
        Route::prefix('timetables')->name('timetables.')->group(function () {
            Route::get('/', [TeacherTimetableController::class, 'index'])->name('index');
            Route::post('/', [TeacherTimetableController::class, 'store'])->name('store');
            Route::get('/{timetable}', [TeacherTimetableController::class, 'show'])->name('show');
            Route::put('/{timetable}', [TeacherTimetableController::class, 'update'])->name('update');
            Route::delete('/{timetable}', [TeacherTimetableController::class, 'destroy'])->name('destroy');
            Route::get('/teacher/{teacher}', [TeacherTimetableController::class, 'getByTeacher'])->name('by-teacher');
            Route::get('/class/{class}', [TeacherTimetableController::class, 'getByClass'])->name('by-class');
            Route::get('/subject/{subject}', [TeacherTimetableController::class, 'getBySubject'])->name('by-subject');
            Route::get('/day/{day}', [TeacherTimetableController::class, 'getByDay'])->name('by-day');
            Route::get('/academic-year/{academicYear}/term/{term}', [TeacherTimetableController::class, 'getByAcademicYearAndTerm'])->name('by-academic-year-term');
            Route::get('/teacher/{teacher}/weekly-schedule/{academicYear}/{term}', [TeacherTimetableController::class, 'getWeeklySchedule'])->name('weekly-schedule');
            Route::get('/class/{class}/weekly-schedule/{academicYear}/{term}', [TeacherTimetableController::class, 'getClassWeeklySchedule'])->name('class-weekly-schedule');
            Route::post('/check-conflicts', [TeacherTimetableController::class, 'checkConflicts'])->name('check-conflicts');
            Route::get('/statistics', [TeacherTimetableController::class, 'getStatistics'])->name('statistics');
        });
    }

    /**
     * Register teacher leave routes.
     */
    private static function registerLeaveRoutes(): void
    {
        Route::prefix('leaves')->name('leaves.')->group(function () {
            Route::get('/', [TeacherLeaveController::class, 'index'])->name('index');
            Route::post('/', [TeacherLeaveController::class, 'store'])->name('store');
            Route::get('/{leave}', [TeacherLeaveController::class, 'show'])->name('show');
            Route::put('/{leave}', [TeacherLeaveController::class, 'update'])->name('update');
            Route::delete('/{leave}', [TeacherLeaveController::class, 'destroy'])->name('destroy');
            Route::post('/{leave}/approve', [TeacherLeaveController::class, 'approve'])->name('approve');
            Route::post('/{leave}/reject', [TeacherLeaveController::class, 'reject'])->name('reject');
            Route::get('/teacher/{teacher}', [TeacherLeaveController::class, 'getByTeacher'])->name('by-teacher');
            Route::get('/type/{type}', [TeacherLeaveController::class, 'getByType'])->name('by-type');
            Route::get('/status/{status}', [TeacherLeaveController::class, 'getByStatus'])->name('by-status');
            Route::get('/pending', [TeacherLeaveController::class, 'getPending'])->name('pending');
            Route::get('/statistics', [TeacherLeaveController::class, 'getStatistics'])->name('statistics');
        });
    }

    /**
     * Register teacher performance routes.
     */
    private static function registerPerformanceRoutes(): void
    {
        Route::prefix('performance')->name('performance.')->group(function () {
            Route::get('/', [TeacherPerformanceController::class, 'index'])->name('index');
            Route::post('/', [TeacherPerformanceController::class, 'store'])->name('store');
            Route::get('/{performance}', [TeacherPerformanceController::class, 'show'])->name('show');
            Route::put('/{performance}', [TeacherPerformanceController::class, 'update'])->name('update');
            Route::delete('/{performance}', [TeacherPerformanceController::class, 'destroy'])->name('destroy');
            Route::post('/{performance}/publish', [TeacherPerformanceController::class, 'publish'])->name('publish');
            Route::post('/{performance}/archive', [TeacherPerformanceController::class, 'archive'])->name('archive');
            Route::get('/teacher/{teacher}', [TeacherPerformanceController::class, 'getByTeacher'])->name('by-teacher');
            Route::get('/evaluator/{evaluator}', [TeacherPerformanceController::class, 'getByEvaluator'])->name('by-evaluator');
            Route::get('/period/{period}', [TeacherPerformanceController::class, 'getByPeriod'])->name('by-period');
            Route::post('/rating-range', [TeacherPerformanceController::class, 'getByRatingRange'])->name('rating-range');
            Route::get('/trends/{teacher}', [TeacherPerformanceController::class, 'getPerformanceTrends'])->name('trends');
            Route::get('/statistics', [TeacherPerformanceController::class, 'getStatistics'])->name('statistics');
        });
    }

    /**
     * Register bulk operation routes.
     */
    private static function registerBulkOperationRoutes(): void
    {
        Route::prefix('bulk')->name('bulk.')->group(function () {
            Route::post('/teachers/import', [TeacherController::class, 'bulkImport'])->name('teachers.import');
            Route::post('/teachers/export', [TeacherController::class, 'export'])->name('teachers.export');
            Route::post('/assignments/import', [TeacherAssignmentController::class, 'bulkImport'])->name('assignments.import');
            Route::post('/assignments/export', [TeacherAssignmentController::class, 'bulkExport'])->name('assignments.export');
        });
    }

    /**
     * Register report routes.
     */
    private static function registerReportRoutes(): void
    {
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/workload-distribution', [TeacherController::class, 'workloadReport'])->name('workload');
            Route::get('/performance-analysis', [TeacherPerformanceController::class, 'performanceReport'])->name('performance');
            Route::get('/leave-analysis', [TeacherLeaveController::class, 'leaveReport'])->name('leave');
            Route::get('/assignment-analysis', [TeacherAssignmentController::class, 'assignmentReport'])->name('assignment');
            Route::get('/teacher-demographics', [TeacherController::class, 'demographicsReport'])->name('demographics');
        });
    }
} 