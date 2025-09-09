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
use App\Repositories\Teachers\TeacherRepository;
use App\Repositories\Teachers\TeacherLeaveRepository;
use App\Repositories\Teachers\TeacherDocumentRepository;
use App\Repositories\Teachers\TeacherPerformanceRepository;
use App\Repositories\Teachers\TeacherTimetableRepository;
use App\Repositories\Teachers\TeacherAssignmentRepository as TeachersTeacherAssignmentRepository;
use App\Repositories\AdminAcademics\LabRepository;
use App\Repositories\AdminAcademics\PrerequisiteRepository;
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
use App\Models\Teachers\Teacher;
use App\Models\Teachers\TeacherLeave;
use App\Models\Teachers\TeacherDocument;
use App\Models\Teachers\TeacherPerformance;
use App\Models\Teachers\TeacherTimetable;
use App\Models\AdminAcademics\Lab;
use App\Models\AdminAcademics\SubjectPrerequisite;

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

        // Bind Student Repository Interface
        $this->app->bind(\App\Repositories\Students\StudentRepositoryInterface::class, StudentRepository::class);

        $this->app->bind(StudentApplicationRepository::class, function ($app) {
            return new StudentApplicationRepository(new StudentApplication());
        });

        // Bind Student Application Repository Interface
        $this->app->bind(\App\Repositories\Students\StudentApplicationRepositoryInterface::class, StudentApplicationRepository::class);

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

        // Bind Teachers repositories
        $this->app->bind(TeacherRepository::class, function ($app) {
            return new TeacherRepository(new Teacher());
        });

        // Bind Teacher Repository Interface
        $this->app->bind(\App\Repositories\Teachers\TeacherRepositoryInterface::class, function ($app) {
            return new \App\Repositories\Teachers\TeacherRepository(new \App\Models\Teachers\Teacher());
        });

        $this->app->bind(TeacherLeaveRepository::class, function ($app) {
            return new TeacherLeaveRepository(new TeacherLeave());
        });

        // Bind Teacher Leave Repository Interface
        $this->app->bind(\App\Repositories\Teachers\TeacherLeaveRepositoryInterface::class, TeacherLeaveRepository::class);

        $this->app->bind(TeacherDocumentRepository::class, function ($app) {
            return new TeacherDocumentRepository(new TeacherDocument());
        });

        // Bind Teacher Document Repository Interface
        $this->app->bind(\App\Repositories\Teachers\TeacherDocumentRepositoryInterface::class, TeacherDocumentRepository::class);

        $this->app->bind(TeacherPerformanceRepository::class, function ($app) {
            return new TeacherPerformanceRepository(new TeacherPerformance());
        });

        // Bind Teacher Performance Repository Interface
        $this->app->bind(\App\Repositories\Teachers\TeacherPerformanceRepositoryInterface::class, TeacherPerformanceRepository::class);

        $this->app->bind(TeacherTimetableRepository::class, function ($app) {
            return new TeacherTimetableRepository(new TeacherTimetable());
        });

        // Bind Teacher Timetable Repository Interface
        $this->app->bind(\App\Repositories\Teachers\TeacherTimetableRepositoryInterface::class, TeacherTimetableRepository::class);

        $this->app->bind(TeachersTeacherAssignmentRepository::class, function ($app) {
            return new TeachersTeacherAssignmentRepository(new \App\Models\Teachers\TeacherAssignment());
        });

        // Bind Teacher Assignment Repository Interface
        $this->app->bind(\App\Repositories\Teachers\TeacherAssignmentRepositoryInterface::class, TeachersTeacherAssignmentRepository::class);

        // Bind Attendance Repository Interfaces
        $this->app->bind(\App\Repositories\Attendance\StudentAttendanceRepositoryInterface::class, \App\Repositories\Attendance\StudentAttendanceRepository::class);
        $this->app->bind(\App\Repositories\Attendance\TeacherAttendanceRepositoryInterface::class, \App\Repositories\Attendance\TeacherAttendanceRepository::class);
        $this->app->bind(\App\Repositories\Attendance\StudentAttendanceExcuseRepositoryInterface::class, \App\Repositories\Attendance\StudentAttendanceExcuseRepository::class);
        $this->app->bind(\App\Repositories\Attendance\TeacherAttendanceExcuseRepositoryInterface::class, \App\Repositories\Attendance\TeacherAttendanceExcuseRepository::class);

        // Bind Exams & Gradings Repository Interfaces
        $this->app->bind(\App\Repositories\ExamsGradings\ExamTypeRepositoryInterface::class, \App\Repositories\ExamsGradings\ExamTypeRepository::class);
        $this->app->bind(\App\Repositories\ExamsGradings\ExamMarkRepositoryInterface::class, \App\Repositories\ExamsGradings\ExamMarkRepository::class);
        $this->app->bind(\App\Repositories\ExamsGradings\StudentGPARepositoryInterface::class, \App\Repositories\ExamsGradings\StudentGPARepository::class);
        $this->app->bind(\App\Repositories\ExamsGradings\ReportCardRepositoryInterface::class, \App\Repositories\ExamsGradings\ReportCardRepository::class);

        // Bind Lab Repository
        $this->app->bind(LabRepository::class, function ($app) {
            return new LabRepository(new Lab());
        });

        // Bind Lab Repository Interface
        $this->app->bind(\App\Repositories\AdminAcademics\LabRepositoryInterface::class, LabRepository::class);

        // Bind Prerequisite Repository
        $this->app->bind(PrerequisiteRepository::class, function ($app) {
            return new PrerequisiteRepository(new SubjectPrerequisite());
        });

        // Bind Prerequisite Repository Interface
        $this->app->bind(\App\Repositories\AdminAcademics\PrerequisiteRepositoryInterface::class, PrerequisiteRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
} 