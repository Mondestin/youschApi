<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamsGradings\ExamType;
use App\Models\AdminAcademics\Exam;
use App\Models\ExamsGradings\ExamMark;
use App\Models\ExamsGradings\StudentGPA;
use App\Models\ExamsGradings\ReportCard;
use App\Models\AdminAcademics\{ClassRoom, Subject, Lab, Term, AcademicYear};
use App\Models\Teachers\Teacher;
use App\Models\Students\Student;

class ExamsGradingsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Starting Exams & Gradings Seeder...');

        $this->createExamTypes();
        $this->createExams();
        $this->createExamMarks();
        $this->createStudentGPAs();
        $this->createReportCards();

        $this->command->info('✅ Exams & Gradings Seeder completed successfully!');
    }

    private function createExamTypes(): void
    {
        $this->command->info('📝 Creating exam types...');

        $examTypes = [
            ['name' => 'Midterm', 'description' => 'Mid-semester examination', 'weight' => 30.0],
            ['name' => 'Final', 'description' => 'End of semester examination', 'weight' => 40.0],
            ['name' => 'Quiz', 'description' => 'Short assessment test', 'weight' => 10.0],
            ['name' => 'Assignment', 'description' => 'Written or practical assignment', 'weight' => 15.0],
            ['name' => 'Practical', 'description' => 'Hands-on practical examination', 'weight' => 25.0],
            ['name' => 'Project', 'description' => 'Long-term project work', 'weight' => 20.0],
            ['name' => 'Lab Test', 'description' => 'Laboratory practical test', 'weight' => 20.0],
            ['name' => 'Presentation', 'description' => 'Oral presentation assessment', 'weight' => 15.0],
        ];

        foreach ($examTypes as $examType) {
            ExamType::create($examType);
        }

        $this->command->info('✅ Exam types created successfully');
    }

    private function createExams(): void
    {
        $this->command->info('📚 Creating exams...');

        $classes = ClassRoom::with(['subjects'])->get();
        $examTypes = ExamType::all();
        $teachers = Teacher::all();

        if ($classes->isEmpty() || $examTypes->isEmpty() || $teachers->isEmpty()) {
            $this->command->warn('⚠️ Skipping exam creation - missing required data');
            return;
        }

        $currentDate = now()->startOfMonth();
        
        foreach ($classes as $class) {
            foreach ($class->subjects as $subject) {
                $examType = $examTypes->random();
                $teacher = $teachers->random();
                
                // Create 2-3 exams per subject
                for ($i = 0; $i < rand(2, 3); $i++) {
                    $examDate = $currentDate->copy()->addDays(rand(1, 30));
                    
                    Exam::create([
                        'name' => $examType->name . ' Exam - ' . $subject->name . ' (' . $class->name . ')',
                        'class_id' => $class->id,
                        'subject_id' => $subject->id,
                        'lab_id' => $subject->labs->random()->id ?? null,
                        'exam_type_id' => $examType->id,
                        'exam_date' => $examDate->format('Y-m-d'),
                        'start_time' => '09:00:00',
                        'end_time' => '11:00:00',
                        'examiner_id' => $teacher->id,
                        'instructions' => 'Please read all questions carefully before answering.',
                        'status' => $examDate->isPast() ? 'completed' : 'scheduled',
                    ]);
                }
            }
        }

        $this->command->info('✅ Exams created successfully');
    }

    private function createExamMarks(): void
    {
        $this->command->info('📊 Creating exam marks...');

        $exams = Exam::where('status', 'completed')->get();
        $students = Student::all();

        if ($exams->isEmpty() || $students->isEmpty()) {
            $this->command->warn('⚠️ Skipping exam marks creation - missing required data');
            return;
        }

        foreach ($exams as $exam) {
            $classStudents = $students->where('class_id', $exam->class_id);
            
            foreach ($classStudents as $student) {
                // Generate realistic marks (60-100 range with some variation)
                $marks = rand(60, 100);
                if (rand(1, 10) === 1) { // 10% chance of lower marks
                    $marks = rand(40, 59);
                }
                
                $grade = $this->calculateGrade($marks);
                
                ExamMark::create([
                    'exam_id' => $exam->id,
                    'student_id' => $student->id,
                    'marks_obtained' => $marks,
                    'grade' => $grade,
                    'remarks' => $this->generateRemarks($grade),
                ]);
            }
        }

        $this->command->info('✅ Exam marks created successfully');
    }

    private function createStudentGPAs(): void
    {
        $this->command->info('🎓 Creating student GPAs...');

        $students = Student::all();
        $terms = Term::all();
        $academicYears = AcademicYear::all();

        if ($students->isEmpty() || $terms->isEmpty() || $academicYears->isEmpty()) {
            $this->command->warn('⚠️ Skipping student GPA creation - missing required data');
            return;
        }

        foreach ($students as $student) {
            foreach ($academicYears as $academicYear) {
                foreach ($terms as $term) {
                    // Generate realistic GPA (2.0-4.0 range)
                    $gpa = round(rand(20, 40) / 10, 2);
                    
                    StudentGPA::create([
                        'student_id' => $student->id,
                        'term_id' => $term->id,
                        'academic_year_id' => $academicYear->id,
                        'gpa' => $gpa,
                        'cgpa' => $gpa, // Simplified for seeding
                    ]);
                }
            }
        }

        $this->command->info('✅ Student GPAs created successfully');
    }

    private function createReportCards(): void
    {
        $this->command->info('📋 Creating report cards...');

        $students = Student::all();
        $classes = ClassRoom::all();
        $terms = Term::all();
        $academicYears = AcademicYear::all();

        if ($students->isEmpty() || $classes->isEmpty() || $terms->isEmpty() || $academicYears->isEmpty()) {
            $this->command->warn('⚠️ Skipping report card creation - missing required data');
            return;
        }

        foreach ($students as $student) {
            foreach ($academicYears as $academicYear) {
                foreach ($terms as $term) {
                    $class = $classes->random();
                    $gpa = StudentGPA::where('student_id', $student->id)
                        ->where('term_id', $term->id)
                        ->where('academic_year_id', $academicYear->id)
                        ->value('gpa') ?? 3.0;
                    
                    ReportCard::create([
                        'student_id' => $student->id,
                        'class_id' => $class->id,
                        'term_id' => $term->id,
                        'academic_year_id' => $academicYear->id,
                        'gpa' => $gpa,
                        'cgpa' => $gpa, // Simplified for seeding
                        'remarks' => $this->generateReportCardRemarks($gpa),
                        'issued_date' => now()->subDays(rand(1, 30)),
                        'format' => rand(1, 2) === 1 ? 'PDF' : 'Digital',
                    ]);
                }
            }
        }

        $this->command->info('✅ Report cards created successfully');
    }

    private function calculateGrade(int $marks): string
    {
        if ($marks >= 90) return 'A+';
        if ($marks >= 80) return 'A';
        if ($marks >= 70) return 'B+';
        if ($marks >= 60) return 'B';
        if ($marks >= 50) return 'C+';
        if ($marks >= 40) return 'C';
        return 'F';
    }

    private function generateRemarks(string $grade): string
    {
        return match($grade) {
            'A+' => 'Excellent performance! Keep up the great work.',
            'A' => 'Very good work. Continue to excel.',
            'B+' => 'Good performance. Room for improvement.',
            'B' => 'Satisfactory work. Keep working hard.',
            'C+' => 'Average performance. Need more effort.',
            'C' => 'Below average. Requires improvement.',
            'F' => 'Failed. Need to retake the exam.',
            default => 'Performance recorded.',
        };
    }

    private function generateReportCardRemarks(float $gpa): string
    {
        if ($gpa >= 3.8) return 'Outstanding academic performance. Exceptional work ethic and dedication.';
        if ($gpa >= 3.5) return 'Excellent academic performance. Strong commitment to learning.';
        if ($gpa >= 3.0) return 'Good academic performance. Consistent effort shown.';
        if ($gpa >= 2.5) return 'Satisfactory performance. Some areas need improvement.';
        if ($gpa >= 2.0) return 'Below average performance. Requires more focus and effort.';
        return 'Poor performance. Immediate attention needed.';
    }
} 