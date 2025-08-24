<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\RepositoryInterface;
use App\Repositories\BaseRepository;
use App\Repositories\AdminAcademics\ExamRepository;
use App\Repositories\AdminAcademics\StudentGradeRepository;
use App\Repositories\AdminAcademics\StudentEnrollmentRepository;
use App\Repositories\AdminAcademics\TeacherAssignmentRepository;
use App\Models\AdminAcademics\Exam;
use App\Models\AdminAcademics\StudentGrade;
use App\Models\AdminAcademics\StudentEnrollment;
use App\Models\AdminAcademics\TeacherAssignment;

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
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
} 