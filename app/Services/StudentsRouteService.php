<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Students\StudentApplicationController;
use App\Http\Controllers\Api\Students\StudentController;
use App\Http\Controllers\Api\Students\AcademicHistoryController;
use App\Http\Controllers\Api\Students\StudentTransferController;
use App\Http\Controllers\Api\Students\StudentGraduationController;
use App\Http\Controllers\Api\Students\StudentDocumentController;

class StudentsRouteService
{
    /**
     * Register all student management routes.
     */
    public static function registerRoutes(): void
    {
        self::registerApplicationRoutes();
        self::registerStudentRoutes();
        self::registerAcademicHistoryRoutes();
        self::registerTransferRoutes();
        self::registerGraduationRoutes();
        self::registerDocumentRoutes();
        self::registerBulkOperationRoutes();
        self::registerReportRoutes();
    }

    /**
     * Register student application routes.
     */
    private static function registerApplicationRoutes(): void
    {
        Route::prefix('applications')->name('applications.')->group(function () {
            Route::get('/', [StudentApplicationController::class, 'index'])->name('index');
            Route::post('/', [StudentApplicationController::class, 'store'])->name('store');
            Route::get('/{application}', [StudentApplicationController::class, 'show'])->name('show');
            Route::put('/{application}', [StudentApplicationController::class, 'update'])->name('update');
            Route::delete('/{application}', [StudentApplicationController::class, 'destroy'])->name('destroy');
            
            // Application Management
            Route::post('/{application}/approve', [StudentApplicationController::class, 'approve'])->name('approve');
            Route::post('/{application}/reject', [StudentApplicationController::class, 'reject'])->name('reject');
            Route::get('/statistics/overview', [StudentApplicationController::class, 'statistics'])->name('statistics');
        });
    }

    /**
     * Register student management routes.
     */
    private static function registerStudentRoutes(): void
    {
        Route::prefix('students')->name('students.')->group(function () {
            Route::get('/', [StudentController::class, 'index'])->name('index');
            Route::post('/', [StudentController::class, 'store'])->name('store');
            Route::get('/{student}', [StudentController::class, 'show'])->name('show');
            Route::put('/{student}', [StudentController::class, 'update'])->name('update');
            Route::delete('/{student}', [StudentController::class, 'destroy'])->name('destroy');
            
            // Student Management
            Route::patch('/{student}/status', [StudentController::class, 'changeStatus'])->name('change-status');
            Route::patch('/{student}/class', [StudentController::class, 'assignToClass'])->name('assign-class');
            Route::get('/{student}/academic-performance', [StudentController::class, 'academicPerformance'])->name('academic-performance');
            Route::get('/statistics/overview', [StudentController::class, 'statistics'])->name('statistics');
        });
    }

    /**
     * Register academic history routes.
     */
    private static function registerAcademicHistoryRoutes(): void
    {
        Route::prefix('academic-history')->name('academic-history.')->group(function () {
            Route::get('/', [AcademicHistoryController::class, 'index'])->name('index');
            Route::post('/', [AcademicHistoryController::class, 'store'])->name('store');
            Route::get('/{academicHistory}', [AcademicHistoryController::class, 'show'])->name('show');
            Route::put('/{academicHistory}', [AcademicHistoryController::class, 'update'])->name('update');
            Route::delete('/{academicHistory}', [AcademicHistoryController::class, 'destroy'])->name('destroy');
            
            // Academic History Management
            Route::get('/student/{student}', [AcademicHistoryController::class, 'byStudent'])->name('by-student');
            Route::get('/subject/{subject}', [AcademicHistoryController::class, 'bySubject'])->name('by-subject');
            Route::get('/class/{class}', [AcademicHistoryController::class, 'byClass'])->name('by-class');
            Route::get('/term/{term}', [AcademicHistoryController::class, 'byTerm'])->name('by-term');
            Route::get('/academic-year/{academicYear}', [AcademicHistoryController::class, 'byAcademicYear'])->name('by-academic-year');
            Route::get('/statistics/overview', [AcademicHistoryController::class, 'statistics'])->name('statistics');
        });
    }

    /**
     * Register student transfer routes.
     */
    private static function registerTransferRoutes(): void
    {
        Route::prefix('transfers')->name('transfers.')->group(function () {
            Route::get('/', [StudentTransferController::class, 'index'])->name('index');
            Route::post('/', [StudentTransferController::class, 'store'])->name('store');
            Route::get('/{transfer}', [StudentTransferController::class, 'show'])->name('show');
            Route::put('/{transfer}', [StudentTransferController::class, 'update'])->name('update');
            Route::delete('/{transfer}', [StudentTransferController::class, 'destroy'])->name('destroy');
            
            // Transfer Management
            Route::post('/{transfer}/approve', [StudentTransferController::class, 'approve'])->name('approve');
            Route::post('/{transfer}/reject', [StudentTransferController::class, 'reject'])->name('reject');
            Route::get('/student/{student}', [StudentTransferController::class, 'byStudent'])->name('by-student');
            Route::get('/campus/{campus}', [StudentTransferController::class, 'byCampus'])->name('by-campus');
            Route::get('/statistics/overview', [StudentTransferController::class, 'statistics'])->name('statistics');
        });
    }

    /**
     * Register student graduation routes.
     */
    private static function registerGraduationRoutes(): void
    {
        Route::prefix('graduation')->name('graduation.')->group(function () {
            Route::get('/', [StudentGraduationController::class, 'index'])->name('index');
            Route::post('/', [StudentGraduationController::class, 'store'])->name('store');
            Route::get('/{graduation}', [StudentGraduationController::class, 'show'])->name('show');
            Route::put('/{graduation}', [StudentGraduationController::class, 'update'])->name('update');
            Route::delete('/{graduation}', [StudentGraduationController::class, 'destroy'])->name('destroy');
            
            // Graduation Management
            Route::post('/{graduation}/issue', [StudentGraduationController::class, 'issue'])->name('issue');
            Route::get('/student/{student}', [StudentGraduationController::class, 'byStudent'])->name('by-student');
            Route::get('/date-range', [StudentGraduationController::class, 'byDateRange'])->name('by-date-range');
            Route::get('/statistics/overview', [StudentGraduationController::class, 'statistics'])->name('statistics');
        });
    }

    /**
     * Register student document routes.
     */
    private static function registerDocumentRoutes(): void
    {
        Route::prefix('documents')->name('documents.')->group(function () {
            Route::get('/', [StudentDocumentController::class, 'index'])->name('index');
            Route::post('/', [StudentDocumentController::class, 'store'])->name('store');
            Route::get('/{document}', [StudentDocumentController::class, 'show'])->name('show');
            Route::put('/{document}', [StudentDocumentController::class, 'update'])->name('update');
            Route::delete('/{document}', [StudentDocumentController::class, 'destroy'])->name('destroy');
            
            // Document Management
            Route::get('/student/{student}', [StudentDocumentController::class, 'byStudent'])->name('by-student');
            Route::get('/type/{type}', [StudentDocumentController::class, 'byType'])->name('by-type');
            Route::post('/upload', [StudentDocumentController::class, 'upload'])->name('upload');
            Route::get('/download/{document}', [StudentDocumentController::class, 'download'])->name('download');
            Route::get('/statistics/overview', [StudentDocumentController::class, 'statistics'])->name('statistics');
        });
    }

    /**
     * Register bulk operation routes.
     */
    private static function registerBulkOperationRoutes(): void
    {
        Route::prefix('bulk')->name('bulk.')->group(function () {
            Route::post('/students/import', [StudentController::class, 'bulkImport'])->name('students-import');
            Route::post('/students/export', [StudentController::class, 'bulkExport'])->name('students-export');
            Route::post('/academic-history/import', [AcademicHistoryController::class, 'bulkImport'])->name('academic-history-import');
            Route::post('/academic-history/export', [AcademicHistoryController::class, 'bulkExport'])->name('academic-history-export');
        });
    }

    /**
     * Register report and analytics routes.
     */
    private static function registerReportRoutes(): void
    {
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/enrollment-trends', [StudentController::class, 'enrollmentTrends'])->name('enrollment-trends');
            Route::get('/academic-performance', [AcademicHistoryController::class, 'academicPerformanceReport'])->name('academic-performance');
            Route::get('/transfer-analysis', [StudentTransferController::class, 'transferAnalysis'])->name('transfer-analysis');
            Route::get('/graduation-analysis', [StudentGraduationController::class, 'graduationAnalysis'])->name('graduation-analysis');
            Route::get('/student-demographics', [StudentController::class, 'demographicsReport'])->name('student-demographics');
        });
    }
} 