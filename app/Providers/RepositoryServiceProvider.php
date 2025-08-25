<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\RepositoryInterface;
use App\Repositories\BaseRepository;
use App\Repositories\AdminAcademics\ExamRepository;
use App\Repositories\AdminAcademics\StudentGradeRepository;
use App\Repositories\AdminAcademics\StudentEnrollmentRepository;
use App\Repositories\AdminAcademics\TeacherAssignmentRepository;
use App\Repositories\Students\StudentRepository;
use App\Repositories\Students\StudentApplicationRepository;
use App\Repositories\Students\StudentTransferRepository;
use App\Repositories\Students\StudentGraduationRepository;
use App\Repositories\Students\StudentDocumentRepository;
use App\Repositories\Students\AcademicHistoryRepository;
use App\Models\AdminAcademics\Exam;
use App\Models\AdminAcademics\StudentGrade;
use App\Models\AdminAcademics\StudentEnrollment;
use App\Models\AdminAcademics\TeacherAssignment;
use App\Models\Students\Student;
use App\Models\Students\StudentApplication;
use App\Models\Students\StudentTransfer;
use App\Models\Students\StudentGraduation;
use App\Models\Students\StudentDocument;
use App\Models\Students\AcademicHistory;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind base repository interface
        $this->app->bind(RepositoryInterface::class, BaseRepository::class);

        // Bind specific repositories
        $this->app->bind(ExamRepository::class, function ($app) {
            return new ExamRepository(new Exam());
        });

        $this->app->bind(StudentGradeRepository::class, function ($app) {
            return new StudentGradeRepository(new StudentGrade());
        });

        $this->app->bind(StudentEnrollmentRepository::class, function ($app) {
            return new StudentEnrollmentRepository(new StudentEnrollment());
        });

        $this->app->bind(TeacherAssignmentRepository::class, function ($app) {
            return new TeacherAssignmentRepository(new TeacherAssignment());
        });

        // Bind Students repositories
        $this->app->bind(StudentRepository::class, function ($app) {
            return new StudentRepository(new Student());
        });

        $this->app->bind(StudentApplicationRepository::class, function ($app) {
            return new StudentApplicationRepository(new StudentApplication());
        });

        $this->app->bind(StudentTransferRepository::class, function ($app) {
            return new StudentTransferRepository(new StudentTransfer());
        });

        $this->app->bind(StudentGraduationRepository::class, function ($app) {
            return new StudentGraduationRepository(new StudentGraduation());
        });

        $this->app->bind(StudentDocumentRepository::class, function ($app) {
            return new StudentDocumentRepository(new StudentDocument());
        });

        $this->app->bind(AcademicHistoryRepository::class, function ($app) {
            return new AcademicHistoryRepository(new AcademicHistory());
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
} 