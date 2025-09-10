<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamsGradings\{
    ExamMark,
    StudentGPA,
    ReportCard
};
use App\Models\AdminAcademics\{
    Exam,
    Subject,
    ClassRoom,
    AcademicYear,
    Term
};
use App\Models\Teachers\Teacher;
use App\Models\Students\Student;
use App\Models\User;

class ExamsGradingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Démarrage du Seeder des Examens et Notes...');

        // Create exam marks
        $this->createExamMarks();
        
        // Create student GPAs
        $this->createStudentGPAs();
        
        // Create report cards
        $this->createReportCards();

        $this->command->info('✅ Seeder des Examens et Notes terminé avec succès !');
    }

    /**
     * Create exam marks for students
     */
    private function createExamMarks(): void
    {
        $this->command->info('📝 Création des notes d\'examen...');

        $exams = Exam::all();
        $students = User::where('email', 'like', '%@student.yousch.edu')->get();
        $teachers = Teacher::all();

        if ($exams->isEmpty() || $students->isEmpty() || $teachers->isEmpty()) {
            $this->command->warn('Données requises manquantes pour créer les notes d\'examen');
            return;
        }

        foreach ($exams as $exam) {
            $class = ClassRoom::find($exam->class_id);
            if (!$class) continue;

            // Get students enrolled in this class
            $classStudents = $students->take(rand(15, 25)); // Random number of students
            
            foreach ($classStudents as $student) {
                $score = $this->generateRandomScore();
                $maxScore = 100;
                $percentage = ($score / $maxScore) * 100;
                $grade = $this->calculateGrade($percentage);
                $isPassing = $percentage >= 60;
                
                ExamMark::create([
                    'exam_id' => $exam->id,
                    'student_id' => $student->id,
                    'marks_obtained' => $score,
                    'grade' => $grade,
                    'remarks' => $this->getGradeRemarks($percentage),
                ]);
            }
        }

        $this->command->info('✅ Notes d\'examen créées avec succès');
    }

    /**
     * Create student GPAs
     */
    private function createStudentGPAs(): void
    {
        $this->command->info('📊 Création des moyennes générales des étudiants...');

        $students = User::where('email', 'like', '%@student.yousch.edu')->get();
        $academicYears = AcademicYear::all();
        $terms = Term::all();

        if ($students->isEmpty() || $academicYears->isEmpty() || $terms->isEmpty()) {
            $this->command->warn('Données requises manquantes pour créer les moyennes générales');
            return;
        }

        foreach ($students as $student) {
            foreach ($academicYears as $academicYear) {
                foreach ($terms as $term) {
                    $gpa = $this->generateRandomGPA();
                    $cgpa = $this->generateRandomGPA();
                    
                    StudentGPA::create([
                        'student_id' => $student->id,
                        'academic_year_id' => $academicYear->id,
                        'term_id' => $term->id,
                        'gpa' => $gpa,
                        'cgpa' => $cgpa,
                    ]);
                }
            }
        }

        $this->command->info('✅ Moyennes générales des étudiants créées avec succès');
    }

    /**
     * Create report cards
     */
    private function createReportCards(): void
    {
        $this->command->info('📋 Création des bulletins scolaires...');

        $students = User::where('email', 'like', '%@student.yousch.edu')->get();
        $classes = ClassRoom::all();
        $academicYears = AcademicYear::all();
        $terms = Term::all();

        if ($students->isEmpty() || $classes->isEmpty() || $academicYears->isEmpty() || $terms->isEmpty()) {
            $this->command->warn('Données requises manquantes pour créer les bulletins scolaires');
            return;
        }

        foreach ($students as $student) {
            $class = $classes->random();
            $academicYear = $academicYears->random();
            $term = $terms->random();
            
            $gpa = $this->generateRandomGPA();
            $cgpa = $this->generateRandomGPA();
            
            ReportCard::create([
                'student_id' => $student->id,
                'class_id' => $class->id,
                'academic_year_id' => $academicYear->id,
                'term_id' => $term->id,
                'gpa' => $gpa,
                'cgpa' => $cgpa,
                'remarks' => $this->getGradeRemarks($gpa * 25), // Convert GPA to percentage for remarks
                'issued_date' => now()->toDateString(),
                'format' => 'Digital',
            ]);
        }

        $this->command->info('✅ Bulletins scolaires créés avec succès');
    }

    /**
     * Generate random score between 50 and 100
     */
    private function generateRandomScore(): int
    {
        return rand(50, 100);
    }

    /**
     * Calculate grade based on percentage
     */
    private function calculateGrade(float $percentage): string
    {
        if ($percentage >= 90) return 'A';
        if ($percentage >= 80) return 'B';
        if ($percentage >= 70) return 'C';
        if ($percentage >= 60) return 'D';
        return 'F';
    }

    /**
     * Get grade point for grade
     */
    private function getGradePoint(string $grade): float
    {
        $gradePoints = [
            'A' => 4.0,
            'B' => 3.0,
            'C' => 2.0,
            'D' => 1.0,
            'F' => 0.0,
        ];
        
        return $gradePoints[$grade] ?? 0.0;
    }

    /**
     * Get grade remarks based on percentage
     */
    private function getGradeRemarks(float $percentage): string
    {
        if ($percentage >= 90) return 'Excellente performance !';
        if ($percentage >= 80) return 'Très bonne performance';
        if ($percentage >= 70) return 'Bonne performance';
        if ($percentage >= 60) return 'Performance satisfaisante';
        return 'Performance insuffisante - amélioration requise';
    }

    /**
     * Generate random GPA between 2.0 and 4.0
     */
    private function generateRandomGPA(): float
    {
        return round(rand(200, 400) / 100, 2);
    }

    /**
     * Get academic standing based on GPA
     */
    private function getAcademicStanding(float $gpa): string
    {
        if ($gpa >= 3.5) return 'Excellent';
        if ($gpa >= 3.0) return 'Bon';
        if ($gpa >= 2.5) return 'Satisfaisant';
        if ($gpa >= 2.0) return 'Passable';
        return 'En probation académique';
    }

    /**
     * Get performance level based on GPA
     */
    private function getPerformanceLevel(float $gpa): string
    {
        if ($gpa >= 3.8) return 'Exceptionnel';
        if ($gpa >= 3.5) return 'Supérieur';
        if ($gpa >= 3.0) return 'Moyen supérieur';
        if ($gpa >= 2.5) return 'Moyen';
        if ($gpa >= 2.0) return 'Moyen inférieur';
        return 'Inférieur';
    }
} 