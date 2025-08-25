<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;

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
        Route::prefix('teachers')->group(function () {
            Route::get('/', 'TeacherController@index')->name('teachers.index');
            Route::post('/', 'TeacherController@store')->name('teachers.store');
            Route::get('/{teacher}', 'TeacherController@show')->name('teachers.show');
            Route::put('/{teacher}', 'TeacherController@update')->name('teachers.update');
            Route::delete('/{teacher}', 'TeacherController@destroy')->name('teachers.destroy');
            Route::post('/{teacher}/onboard', 'TeacherController@onboard')->name('teachers.onboard');
            Route::put('/{teacher}/status', 'TeacherController@changeStatus')->name('teachers.changeStatus');
            Route::get('/{teacher}/schedule', 'TeacherController@getSchedule')->name('teachers.schedule');
            Route::get('/{teacher}/workload', 'TeacherController@getWorkload')->name('teachers.workload');
            Route::get('/statistics/overview', 'TeacherController@statistics')->name('teachers.statistics');
        });
    }

    /**
     * Register teacher document routes.
     */
    private static function registerDocumentRoutes(): void
    {
        Route::prefix('documents')->group(function () {
            Route::get('/', 'TeacherDocumentController@index')->name('documents.index');
            Route::post('/', 'TeacherDocumentController@store')->name('documents.store');
            Route::get('/{document}', 'TeacherDocumentController@show')->name('documents.show');
            Route::put('/{document}', 'TeacherDocumentController@update')->name('documents.update');
            Route::delete('/{document}', 'TeacherDocumentController@destroy')->name('documents.destroy');
            Route::get('/teacher/{teacher}', 'TeacherDocumentController@byTeacher')->name('documents.byTeacher');
            Route::get('/statistics/overview', 'TeacherDocumentController@statistics')->name('documents.statistics');
        });
    }

    /**
     * Register teacher assignment routes.
     */
    private static function registerAssignmentRoutes(): void
    {
        Route::prefix('assignments')->group(function () {
            Route::get('/', 'TeacherAssignmentController@index')->name('assignments.index');
            Route::post('/', 'TeacherAssignmentController@store')->name('assignments.store');
            Route::get('/{assignment}', 'TeacherAssignmentController@show')->name('assignments.show');
            Route::put('/{assignment}', 'TeacherAssignmentController@update')->name('assignments.update');
            Route::delete('/{assignment}', 'TeacherAssignmentController@destroy')->name('assignments.destroy');
            Route::get('/teacher/{teacher}', 'TeacherAssignmentController@byTeacher')->name('assignments.byTeacher');
            Route::get('/class/{class}', 'TeacherAssignmentController@byClass')->name('assignments.byClass');
            Route::get('/subject/{subject}', 'TeacherAssignmentController@bySubject')->name('assignments.bySubject');
            Route::post('/bulk', 'TeacherAssignmentController@bulkAssign')->name('assignments.bulkAssign');
            Route::get('/statistics/overview', 'TeacherAssignmentController@statistics')->name('assignments.statistics');
        });
    }

    /**
     * Register teacher timetable routes.
     */
    private static function registerTimetableRoutes(): void
    {
        Route::prefix('timetables')->group(function () {
            Route::get('/', 'TeacherTimetableController@index')->name('timetables.index');
            Route::post('/', 'TeacherTimetableController@store')->name('timetables.store');
            Route::get('/{timetable}', 'TeacherTimetableController@show')->name('timetables.show');
            Route::put('/{timetable}', 'TeacherTimetableController@update')->name('timetables.update');
            Route::delete('/{timetable}', 'TeacherTimetableController@destroy')->name('timetables.destroy');
            Route::get('/teacher/{teacher}', 'TeacherTimetableController@byTeacher')->name('timetables.byTeacher');
            Route::get('/class/{class}', 'TeacherTimetableController@byClass')->name('timetables.byClass');
            Route::get('/generate/{teacher}', 'TeacherTimetableController@generate')->name('timetables.generate');
            Route::get('/statistics/overview', 'TeacherTimetableController@statistics')->name('timetables.statistics');
        });
    }

    /**
     * Register teacher leave routes.
     */
    private static function registerLeaveRoutes(): void
    {
        Route::prefix('leaves')->group(function () {
            Route::get('/', 'TeacherLeaveController@index')->name('leaves.index');
            Route::post('/', 'TeacherLeaveController@store')->name('leaves.store');
            Route::get('/{leave}', 'TeacherLeaveController@show')->name('leaves.show');
            Route::put('/{leave}', 'TeacherLeaveController@update')->name('leaves.update');
            Route::delete('/{leave}', 'TeacherLeaveController@destroy')->name('leaves.destroy');
            Route::post('/{leave}/approve', 'TeacherLeaveController@approve')->name('leaves.approve');
            Route::post('/{leave}/reject', 'TeacherLeaveController@reject')->name('leaves.reject');
            Route::get('/teacher/{teacher}', 'TeacherLeaveController@byTeacher')->name('leaves.byTeacher');
            Route::get('/pending', 'TeacherLeaveController@pending')->name('leaves.pending');
            Route::get('/statistics/overview', 'TeacherLeaveController@statistics')->name('leaves.statistics');
        });
    }

    /**
     * Register teacher performance routes.
     */
    private static function registerPerformanceRoutes(): void
    {
        Route::prefix('performance')->group(function () {
            Route::get('/', 'TeacherPerformanceController@index')->name('performance.index');
            Route::post('/', 'TeacherPerformanceController@store')->name('performance.store');
            Route::get('/{performance}', 'TeacherPerformanceController@show')->name('performance.show');
            Route::put('/{performance}', 'TeacherPerformanceController@update')->name('performance.update');
            Route::delete('/{performance}', 'TeacherPerformanceController@destroy')->name('performance.destroy');
            Route::get('/teacher/{teacher}', 'TeacherPerformanceController@byTeacher')->name('performance.byTeacher');
            Route::post('/evaluate/{teacher}', 'TeacherPerformanceController@evaluate')->name('performance.evaluate');
            Route::get('/statistics/overview', 'TeacherPerformanceController@statistics')->name('performance.statistics');
        });
    }

    /**
     * Register bulk operation routes.
     */
    private static function registerBulkOperationRoutes(): void
    {
        Route::prefix('bulk')->group(function () {
            Route::post('/teachers/import', 'TeacherController@bulkImport')->name('bulk.teachers.import');
            Route::post('/teachers/export', 'TeacherController@export')->name('bulk.teachers.export');
            Route::post('/assignments/import', 'TeacherAssignmentController@bulkImport')->name('bulk.assignments.import');
            Route::post('/assignments/export', 'TeacherAssignmentController@bulkExport')->name('bulk.assignments.export');
        });
    }

    /**
     * Register report routes.
     */
    private static function registerReportRoutes(): void
    {
        Route::prefix('reports')->group(function () {
            Route::get('/workload-distribution', 'TeacherController@workloadReport')->name('reports.workload');
            Route::get('/performance-analysis', 'TeacherPerformanceController@performanceReport')->name('reports.performance');
            Route::get('/leave-analysis', 'TeacherLeaveController@leaveReport')->name('reports.leave');
            Route::get('/assignment-analysis', 'TeacherAssignmentController@assignmentReport')->name('reports.assignment');
            Route::get('/teacher-demographics', 'TeacherController@demographicsReport')->name('reports.demographics');
        });
    }
} 