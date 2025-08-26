<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Attendance\StudentAttendanceController;
use App\Http\Controllers\Api\Attendance\TeacherAttendanceController;
use App\Http\Controllers\Api\Attendance\StudentAttendanceExcuseController;
use App\Http\Controllers\Api\Attendance\TeacherAttendanceExcuseController;

class AttendanceRouteService
{
    /**
     * Register all attendance-related routes.
     */
    public static function registerRoutes(): void
    {
        self::registerStudentAttendanceRoutes();
        self::registerTeacherAttendanceRoutes();
        self::registerStudentExcuseRoutes();
        self::registerTeacherExcuseRoutes();
        self::registerBulkOperationRoutes();
        self::registerReportRoutes();
    }

    /**
     * Register student attendance routes.
     */
    private static function registerStudentAttendanceRoutes(): void
    {
        Route::prefix('student-attendance')->name('student-attendance.')->group(function () {
            Route::get('/', [StudentAttendanceController::class, 'index'])->name('index');
            Route::post('/', [StudentAttendanceController::class, 'store'])->name('store');
            Route::get('/{attendance}', [StudentAttendanceController::class, 'show'])->name('show');
            Route::put('/{attendance}', [StudentAttendanceController::class, 'update'])->name('update');
            Route::delete('/{attendance}', [StudentAttendanceController::class, 'destroy'])->name('destroy');
            
            // Filtered routes
            Route::get('/student/{studentId}', [StudentAttendanceController::class, 'byStudent'])->name('by-student');
            Route::get('/class/{classId}', [StudentAttendanceController::class, 'byClass'])->name('by-class');
            Route::get('/subject/{subjectId}', [StudentAttendanceController::class, 'bySubject'])->name('by-subject');
            Route::get('/date/{date}', [StudentAttendanceController::class, 'byDate'])->name('by-date');
            Route::post('/date-range', [StudentAttendanceController::class, 'byDateRange'])->name('by-date-range');
            
            // Summary and trends
            Route::get('/class/{classId}/summary', [StudentAttendanceController::class, 'classSummary'])->name('class-summary');
            Route::get('/student/{studentId}/summary', [StudentAttendanceController::class, 'studentSummary'])->name('student-summary');
            Route::get('/student/{studentId}/trends', [StudentAttendanceController::class, 'trends'])->name('trends');
            
            // Statistics and reports
            Route::get('/statistics', [StudentAttendanceController::class, 'statistics'])->name('statistics');
            Route::get('/report', [StudentAttendanceController::class, 'report'])->name('report');
        });
    }

    /**
     * Register teacher attendance routes.
     */
    private static function registerTeacherAttendanceRoutes(): void
    {
        Route::prefix('teacher-attendance')->name('teacher-attendance.')->group(function () {
            Route::get('/', [TeacherAttendanceController::class, 'index'])->name('index');
            Route::post('/', [TeacherAttendanceController::class, 'store'])->name('store');
            Route::get('/{attendance}', [TeacherAttendanceController::class, 'show'])->name('show');
            Route::put('/{attendance}', [TeacherAttendanceController::class, 'update'])->name('update');
            Route::delete('/{attendance}', [TeacherAttendanceController::class, 'destroy'])->name('destroy');
            
            // Filtered routes
            Route::get('/teacher/{teacherId}', [TeacherAttendanceController::class, 'byTeacher'])->name('by-teacher');
            Route::get('/class/{classId}', [TeacherAttendanceController::class, 'byClass'])->name('by-class');
            Route::get('/subject/{subjectId}', [TeacherAttendanceController::class, 'bySubject'])->name('by-subject');
            Route::get('/date/{date}', [TeacherAttendanceController::class, 'byDate'])->name('by-date');
            Route::post('/date-range', [TeacherAttendanceController::class, 'byDateRange'])->name('by-date-range');
            
            // Summary and trends
            Route::get('/class/{classId}/summary', [TeacherAttendanceController::class, 'classSummary'])->name('class-summary');
            Route::get('/teacher/{teacherId}/summary', [TeacherAttendanceController::class, 'teacherSummary'])->name('teacher-summary');
            Route::get('/teacher/{teacherId}/trends', [TeacherAttendanceController::class, 'trends'])->name('trends');
            
            // Statistics and reports
            Route::get('/statistics', [TeacherAttendanceController::class, 'statistics'])->name('statistics');
            Route::get('/report', [TeacherAttendanceController::class, 'report'])->name('report');
        });
    }

    /**
     * Register student excuse routes.
     */
    private static function registerStudentExcuseRoutes(): void
    {
        Route::prefix('student-excuses')->name('student-excuses.')->group(function () {
            Route::get('/', [StudentAttendanceExcuseController::class, 'index'])->name('index');
            Route::post('/', [StudentAttendanceExcuseController::class, 'store'])->name('store');
            Route::get('/{excuse}', [StudentAttendanceExcuseController::class, 'show'])->name('show');
            Route::put('/{excuse}', [StudentAttendanceExcuseController::class, 'update'])->name('update');
            Route::delete('/{excuse}', [StudentAttendanceExcuseController::class, 'destroy'])->name('destroy');
            
            // Filtered routes
            Route::get('/student/{studentId}', [StudentAttendanceExcuseController::class, 'byStudent'])->name('by-student');
            Route::get('/class/{classId}', [StudentAttendanceExcuseController::class, 'byClass'])->name('by-class');
            Route::get('/subject/{subjectId}', [StudentAttendanceExcuseController::class, 'bySubject'])->name('by-subject');
            Route::get('/date/{date}', [StudentAttendanceExcuseController::class, 'byDate'])->name('by-date');
            Route::post('/date-range', [StudentAttendanceExcuseController::class, 'byDateRange'])->name('by-date-range');
            
            // Status-based routes
            Route::get('/pending', [StudentAttendanceExcuseController::class, 'pending'])->name('pending');
            Route::get('/approved', [StudentAttendanceExcuseController::class, 'approved'])->name('approved');
            Route::get('/rejected', [StudentAttendanceExcuseController::class, 'rejected'])->name('rejected');
            
            // Approval actions
            Route::post('/{excuse}/approve', [StudentAttendanceExcuseController::class, 'approve'])->name('approve');
            Route::post('/{excuse}/reject', [StudentAttendanceExcuseController::class, 'reject'])->name('reject');
            
            // Statistics and reports
            Route::get('/statistics', [StudentAttendanceExcuseController::class, 'statistics'])->name('statistics');
            Route::get('/report', [StudentAttendanceExcuseController::class, 'report'])->name('report');
            Route::get('/student/{studentId}/trends', [StudentAttendanceExcuseController::class, 'trends'])->name('trends');
        });
    }

    /**
     * Register teacher excuse routes.
     */
    private static function registerTeacherExcuseRoutes(): void
    {
        Route::prefix('teacher-excuses')->name('teacher-excuses.')->group(function () {
            Route::get('/', [TeacherAttendanceExcuseController::class, 'index'])->name('index');
            Route::post('/', [TeacherAttendanceExcuseController::class, 'store'])->name('store');
            Route::get('/{excuse}', [TeacherAttendanceExcuseController::class, 'show'])->name('show');
            Route::put('/{excuse}', [TeacherAttendanceExcuseController::class, 'update'])->name('update');
            Route::delete('/{excuse}', [TeacherAttendanceExcuseController::class, 'destroy'])->name('destroy');
            
            // Filtered routes
            Route::get('/teacher/{teacherId}', [TeacherAttendanceExcuseController::class, 'byTeacher'])->name('by-teacher');
            Route::get('/class/{classId}', [TeacherAttendanceExcuseController::class, 'byClass'])->name('by-class');
            Route::get('/subject/{subjectId}', [TeacherAttendanceExcuseController::class, 'bySubject'])->name('by-subject');
            Route::get('/date/{date}', [TeacherAttendanceExcuseController::class, 'byDate'])->name('by-date');
            Route::post('/date-range', [TeacherAttendanceExcuseController::class, 'byDateRange'])->name('by-date-range');
            
            // Status-based routes
            Route::get('/pending', [TeacherAttendanceExcuseController::class, 'pending'])->name('pending');
            Route::get('/approved', [TeacherAttendanceExcuseController::class, 'approved'])->name('approved');
            Route::get('/rejected', [TeacherAttendanceExcuseController::class, 'rejected'])->name('rejected');
            
            // Approval actions
            Route::post('/{excuse}/approve', [TeacherAttendanceExcuseController::class, 'approve'])->name('approve');
            Route::post('/{excuse}/reject', [TeacherAttendanceExcuseController::class, 'reject'])->name('reject');
            
            // Statistics and reports
            Route::get('/statistics', [TeacherAttendanceExcuseController::class, 'statistics'])->name('statistics');
            Route::get('/report', [TeacherAttendanceExcuseController::class, 'report'])->name('report');
            Route::get('/teacher/{teacherId}/trends', [TeacherAttendanceExcuseController::class, 'trends'])->name('trends');
        });
    }

    /**
     * Register bulk operation routes.
     */
    private static function registerBulkOperationRoutes(): void
    {
        Route::prefix('bulk')->name('bulk.')->group(function () {
            // Student attendance bulk operations
            Route::post('/student-attendance/create', [StudentAttendanceController::class, 'bulkCreate'])->name('student-attendance.create');
            Route::post('/student-attendance/update', [StudentAttendanceController::class, 'bulkUpdate'])->name('student-attendance.update');
            
            // Teacher attendance bulk operations
            Route::post('/teacher-attendance/create', [TeacherAttendanceController::class, 'bulkCreate'])->name('teacher-attendance.create');
            Route::post('/teacher-attendance/update', [TeacherAttendanceController::class, 'bulkUpdate'])->name('teacher-attendance.update');
        });
    }

    /**
     * Register report routes.
     */
    private static function registerReportRoutes(): void
    {
        Route::prefix('reports')->name('reports.')->group(function () {
            // Attendance reports
            Route::get('/student-attendance', [StudentAttendanceController::class, 'report'])->name('student-attendance');
            Route::get('/teacher-attendance', [TeacherAttendanceController::class, 'report'])->name('teacher-attendance');
            
            // Excuse reports
            Route::get('/student-excuses', [StudentAttendanceExcuseController::class, 'report'])->name('student-excuses');
            Route::get('/teacher-excuses', [TeacherAttendanceExcuseController::class, 'report'])->name('teacher-excuses');
            
            // Statistics
            Route::get('/student-attendance/stats', [StudentAttendanceController::class, 'statistics'])->name('student-attendance-stats');
            Route::get('/teacher-attendance/stats', [TeacherAttendanceController::class, 'statistics'])->name('teacher-attendance-stats');
            Route::get('/student-excuses/stats', [StudentAttendanceExcuseController::class, 'statistics'])->name('student-excuses-stats');
            Route::get('/teacher-excuses/stats', [TeacherAttendanceExcuseController::class, 'statistics'])->name('teacher-excuses-stats');
        });
    }
} 