<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ExamsGradings\ExamTypeController;
use App\Http\Controllers\Api\ExamsGradings\ExamController;
use App\Http\Controllers\Api\ExamsGradings\ExamMarkController;
use App\Http\Controllers\Api\ExamsGradings\StudentGPAController;
use App\Http\Controllers\Api\ExamsGradings\ReportCardController;

class ExamsGradingsRouteService
{
    public static function registerRoutes(): void
    {
        self::registerExamTypeRoutes();
        self::registerExamMarkRoutes();
        self::registerStudentGPARoutes();
        self::registerReportCardRoutes();
        self::registerBulkOperationRoutes();
        self::registerReportRoutes();
    }

    /**
     * Register exam type management routes.
     */
    private static function registerExamTypeRoutes(): void
    {
        Route::prefix('exam-types')->name('exam-types.')->group(function () {
            Route::get('/', [ExamTypeController::class, 'index'])->name('index');
            Route::post('/', [ExamTypeController::class, 'store'])->name('store');
            Route::get('/all', [ExamTypeController::class, 'getAll'])->name('all');
            Route::get('/weighted', [ExamTypeController::class, 'weighted'])->name('weighted');
            Route::get('/statistics', [ExamTypeController::class, 'statistics'])->name('statistics');
            Route::get('/{examType}', [ExamTypeController::class, 'show'])->name('show');
            Route::put('/{examType}', [ExamTypeController::class, 'update'])->name('update');
            Route::delete('/{examType}', [ExamTypeController::class, 'destroy'])->name('destroy');
        });
    }
    /**
     * Register exam mark management routes.
     */
    private static function registerExamMarkRoutes(): void
    {
        Route::prefix('exam-marks')->name('exam-marks.')->group(function () {
            Route::get('/', [ExamMarkController::class, 'index'])->name('index');
            Route::post('/', [ExamMarkController::class, 'store'])->name('store');
            Route::get('/exam/{exam}', [ExamMarkController::class, 'getByExam'])->name('by-exam');
            Route::get('/student/{student}', [ExamMarkController::class, 'getByStudent'])->name('by-student');
            Route::get('/exam/{exam}/student/{student}', [ExamMarkController::class, 'getByExamAndStudent'])->name('by-exam-student');
            Route::get('/statistics', [ExamMarkController::class, 'statistics'])->name('statistics');
            Route::get('/exam/{exam}/results', [ExamMarkController::class, 'getExamResults'])->name('exam-results');
            Route::get('/{examMark}', [ExamMarkController::class, 'show'])->name('show');
            Route::put('/{examMark}', [ExamMarkController::class, 'update'])->name('update');
            Route::delete('/{examMark}', [ExamMarkController::class, 'destroy'])->name('destroy');
        });
    }

    /**
     * Register student GPA management routes.
     */
    private static function registerStudentGPARoutes(): void
    {
        Route::prefix('student-gpa')->name('student-gpa.')->group(function () {
            Route::get('/', [StudentGPAController::class, 'index'])->name('index');
            Route::post('/', [StudentGPAController::class, 'store'])->name('store');
            Route::get('/student/{student}', [StudentGPAController::class, 'getByStudent'])->name('by-student');
            Route::get('/term/{term}', [StudentGPAController::class, 'getByTerm'])->name('by-term');
            Route::get('/academic-year/{academicYear}', [StudentGPAController::class, 'getByAcademicYear'])->name('by-academic-year');
            Route::get('/student/{student}/term/{term}', [StudentGPAController::class, 'getByStudentAndTerm'])->name('by-student-term');
            Route::get('/student/{student}/academic-year/{academicYear}', [StudentGPAController::class, 'getByStudentAndAcademicYear'])->name('by-student-academic-year');
            Route::get('/calculate-gpa', [StudentGPAController::class, 'calculateGPA'])->name('calculate-gpa');
            Route::get('/calculate-cgpa', [StudentGPAController::class, 'calculateCGPA'])->name('calculate-cgpa');
            Route::get('/top-performers', [StudentGPAController::class, 'getTopPerformers'])->name('top-performers');
            Route::get('/low-performers', [StudentGPAController::class, 'getLowPerformers'])->name('low-performers');
            Route::get('/gpa-distribution', [StudentGPAController::class, 'getGPADistribution'])->name('gpa-distribution');
            Route::get('/statistics', [StudentGPAController::class, 'statistics'])->name('statistics');
            Route::get('/{studentGPA}', [StudentGPAController::class, 'show'])->name('show');
            Route::put('/{studentGPA}', [StudentGPAController::class, 'update'])->name('update');
            Route::delete('/{studentGPA}', [StudentGPAController::class, 'destroy'])->name('destroy');
        });
    }

    /**
     * Register report card management routes.
     */
    private static function registerReportCardRoutes(): void
    {
        Route::prefix('report-cards')->name('report-cards.')->group(function () {
            Route::get('/', [ReportCardController::class, 'index'])->name('index');
            Route::post('/', [ReportCardController::class, 'store'])->name('store');
            Route::get('/student/{student}', [ReportCardController::class, 'getByStudent'])->name('by-student');
            Route::get('/class/{class}', [ReportCardController::class, 'getByClass'])->name('by-class');
            Route::get('/term/{term}', [ReportCardController::class, 'getByTerm'])->name('by-term');
            Route::get('/academic-year/{academicYear}', [ReportCardController::class, 'getByAcademicYear'])->name('by-academic-year');
            Route::get('/student/{student}/term/{term}', [ReportCardController::class, 'getByStudentAndTerm'])->name('by-student-term');
            Route::post('/generate', [ReportCardController::class, 'generateReportCard'])->name('generate');
            Route::post('/generate-class', [ReportCardController::class, 'generateClassReportCards'])->name('generate-class');
            Route::get('/{reportCard}/export-pdf', [ReportCardController::class, 'exportToPDF'])->name('export-pdf');
            Route::get('/statistics', [ReportCardController::class, 'statistics'])->name('statistics');
            Route::get('/student/{student}/trends', [ReportCardController::class, 'getTrends'])->name('trends');
            Route::get('/{reportCard}', [ReportCardController::class, 'show'])->name('show');
            Route::put('/{reportCard}', [ReportCardController::class, 'update'])->name('update');
            Route::delete('/{reportCard}', [ReportCardController::class, 'destroy'])->name('destroy');
        });
    }

    /**
     * Register bulk operation routes.
     */
    private static function registerBulkOperationRoutes(): void
    {
        Route::prefix('bulk')->name('bulk.')->group(function () {
            Route::post('/exam-marks/create', [ExamMarkController::class, 'bulkCreate'])->name('exam-marks-create');
            Route::post('/exam-marks/update', [ExamMarkController::class, 'bulkUpdate'])->name('exam-marks-update');
            Route::post('/student-gpa/create', [StudentGPAController::class, 'bulkCreate'])->name('student-gpa-create');
            Route::post('/student-gpa/update', [StudentGPAController::class, 'bulkUpdate'])->name('student-gpa-update');
            Route::post('/report-cards/generate', [ReportCardController::class, 'bulkGenerate'])->name('report-cards-generate');
        });
    }

    /**
     * Register report and analytics routes.
     */
    private static function registerReportRoutes(): void
    {
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/exam-performance', [ExamMarkController::class, 'performanceReport'])->name('exam-performance');
            Route::get('/student-gpa-analysis', [StudentGPAController::class, 'gpaAnalysisReport'])->name('student-gpa-analysis');
            Route::get('/report-card-summary', [ReportCardController::class, 'summaryReport'])->name('report-card-summary');
            Route::get('/academic-progress', [StudentGPAController::class, 'academicProgressReport'])->name('academic-progress');
            Route::get('/class-performance', [ExamMarkController::class, 'classPerformanceReport'])->name('class-performance');
            Route::get('/subject-performance', [ExamMarkController::class, 'subjectPerformanceReport'])->name('subject-performance');
        });
    }
} 