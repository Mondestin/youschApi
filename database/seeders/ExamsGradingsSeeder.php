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
        $students = Student::all();
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
                    'subject_id' => $exam->subject_id,
                    'class_id' => $exam->class_id,
                    'academic_year_id' => $exam->academic_year_id ?? 1,
                    'term_id' => $exam->term_id ?? 1,
                    'score' => $score,
                    'max_score' => $maxScore,
                    'percentage' => $percentage,
                    'grade' => $grade,
                    'grade_point' => $this->getGradePoint($grade),
                    'has_grade' => true,
                    'is_passing' => $isPassing,
                    'remarks' => $this->getGradeRemarks($percentage),
                    'graded_by' => $teachers->random()->id,
                    'graded_at' => now(),
                    'is_absent' => false,
                    'is_excused' => false,
                    'submitted_at' => now(),
                    'reviewed_at' => now(),
                    'reviewed_by' => $teachers->random()->id,
                    'review_notes' => 'Note vérifiée et approuvée',
                    'appeal_status' => 'none',
                    'appeal_reason' => null,
                    'appeal_submitted_at' => null,
                    'appeal_reviewed_at' => null,
                    'appeal_reviewed_by' => null,
                    'appeal_decision' => null,
                    'appeal_notes' => null,
                    'is_curved' => false,
                    'curve_factor' => null,
                    'original_score' => $score,
                    'original_percentage' => $percentage,
                    'original_grade' => $grade,
                    'moderation_notes' => null,
                    'moderated_by' => null,
                    'moderated_at' => null,
                    'is_final' => true,
                    'can_be_modified' => false,
                    'modification_reason' => null,
                    'modification_approved_by' => null,
                    'modification_approved_at' => null,
                    'created_by' => $teachers->random()->id,
                    'updated_by' => $teachers->random()->id,
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

        $students = Student::all();
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
                        'total_credits' => rand(15, 21),
                        'earned_credits' => rand(12, 21),
                        'failed_credits' => rand(0, 3),
                        'incomplete_credits' => rand(0, 2),
                        'withdrawn_credits' => rand(0, 1),
                        'academic_standing' => $this->getAcademicStanding($gpa),
                        'performance_level' => $this->getPerformanceLevel($gpa),
                        'rank_in_class' => rand(1, 30),
                        'total_students_in_class' => rand(25, 35),
                        'percentile' => rand(70, 100),
                        'is_on_probation' => $gpa < 2.0,
                        'probation_reason' => $gpa < 2.0 ? 'Moyenne générale inférieure à 2.0' : null,
                        'probation_start_date' => $gpa < 2.0 ? now() : null,
                        'probation_end_date' => $gpa < 2.0 ? now()->addMonths(6) : null,
                        'academic_advisor_id' => rand(1, 5),
                        'advisor_notes' => 'Performance académique satisfaisante',
                        'improvement_plan' => $gpa < 2.5 ? 'Plan d\'amélioration académique requis' : null,
                        'is_eligible_for_honors' => $gpa >= 3.5,
                        'honors_type' => $gpa >= 3.8 ? 'Magna Cum Laude' : ($gpa >= 3.5 ? 'Cum Laude' : null),
                        'graduation_eligibility' => $gpa >= 2.0 ? 'Éligible' : 'Non éligible',
                        'graduation_date' => $gpa >= 2.0 ? now()->addMonths(6) : null,
                        'created_by' => rand(1, 5),
                        'updated_by' => rand(1, 5),
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

        $students = Student::all();
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
                'total_credits' => rand(15, 21),
                'earned_credits' => rand(12, 21),
                'failed_credits' => rand(0, 3),
                'incomplete_credits' => rand(0, 2),
                'withdrawn_credits' => rand(0, 1),
                'academic_standing' => $this->getAcademicStanding($gpa),
                'performance_level' => $this->getPerformanceLevel($gpa),
                'rank_in_class' => rand(1, 30),
                'total_students_in_class' => rand(25, 35),
                'percentile' => rand(70, 100),
                'is_on_probation' => $gpa < 2.0,
                'probation_reason' => $gpa < 2.0 ? 'Moyenne générale inférieure à 2.0' : null,
                'probation_start_date' => $gpa < 2.0 ? now() : null,
                'probation_end_date' => $gpa < 2.0 ? now()->addMonths(6) : null,
                'academic_advisor_id' => rand(1, 5),
                'advisor_notes' => 'Performance académique satisfaisante',
                'improvement_plan' => $gpa < 2.5 ? 'Plan d\'amélioration académique requis' : null,
                'is_eligible_for_honors' => $gpa >= 3.5,
                'honors_type' => $gpa >= 3.8 ? 'Magna Cum Laude' : ($gpa >= 3.5 ? 'Cum Laude' : null),
                'graduation_eligibility' => $gpa >= 2.0 ? 'Éligible' : 'Non éligible',
                'graduation_date' => $gpa >= 2.0 ? now()->addMonths(6) : null,
                'report_card_number' => 'RC-' . str_pad($student->id, 6, '0', STR_PAD_LEFT) . '-' . $term->id,
                'issue_date' => now(),
                'is_published' => true,
                'published_at' => now(),
                'published_by' => rand(1, 5),
                'is_acknowledged' => rand(0, 1),
                'acknowledged_at' => rand(0, 1) ? now() : null,
                'acknowledged_by' => rand(0, 1) ? $student->id : null,
                'parent_signature' => rand(0, 1) ? 'Signature des parents reçue' : null,
                'parent_signature_date' => rand(0, 1) ? now() : null,
                'student_signature' => rand(0, 1) ? 'Signature de l\'étudiant reçue' : null,
                'student_signature_date' => rand(0, 1) ? now() : null,
                'format' => 'standard',
                'template_version' => '1.0',
                'is_archived' => false,
                'archived_at' => null,
                'archived_by' => null,
                'archive_reason' => null,
                'can_be_modified' => false,
                'modification_reason' => null,
                'modification_approved_by' => null,
                'modification_approved_at' => null,
                'created_by' => rand(1, 5),
                'updated_by' => rand(1, 5),
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