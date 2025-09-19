<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Démarrage du Seeder Principal de la Base de Données...');

        // Clear existing data (optional - uncomment if needed)
        // $this->clearExistingData();
         
        User::factory(10)->create();
        // Seed exam types first
        $this->call(ExamTypeSeeder::class);
        $this->call(VenueSeeder::class);
        
        // Seed academic management data
        $this->call(AcademicManagementSeeder::class);
        
        // Seed students management data
        $this->call(StudentsManagementSeeder::class);
        
        // Seed teachers management data
        $this->call(TeachersManagementSeeder::class);
        
        // Seed attendance management data
        $this->call(AttendanceManagementSeeder::class);
        
        // Seed exams and gradings data (needs teachers and students)
        $this->call(ExamsGradingsSeeder::class);
        
        // Seed labs and prerequisites data
        $this->call(LabSeeder::class);
        $this->call(PrerequisiteSeeder::class);
        
        // Seed realistic timetables (needs all other data first)
        $this->call(TimetableSeeder::class);

        $this->command->info('✅ Seeder Principal de la Base de Données terminé avec succès !');
        $this->command->info('🎉 Toutes les données de test ont été créées avec succès !');
    }

    /**
     * Clear existing data from the database
     */
    private function clearExistingData(): void
    {
        $this->command->info('🧹 Nettoyage des données existantes...');

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear all tables (adjust as needed)
        $tables = [
            'exam_types',
            'exams',
            'exam_marks',
            'student_gpas',
            'report_cards',
            'grading_schemes',
            'grade_scales',
            'student_enrollments',
            'student_grades',
            'teacher_assignments',
            'announcements',
            'labs',
            'school_admins',
            'school_calendars',
            'timetables',
            'student_attendance',
            'teacher_attendance',
            'student_attendance_excuses',
            'teacher_attendance_excuses',
            'teacher_leaves',
            'teacher_documents',
            'teacher_performance',
            'teacher_timetables',
            'classes',
            'subjects',
            'courses',
            'departments',
            'faculties',
            'terms',
            'academic_years',
            'campuses',
            'schools',
            'teachers',
            'students',
            'users',
        ];

        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->truncate();
                $this->command->info("✅ Table '{$table}' vidée");
            }
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('✅ Nettoyage des données terminé');
    }
}
