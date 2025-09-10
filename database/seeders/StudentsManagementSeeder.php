<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Students\StudentApplication;
use App\Models\Students\Student;
use App\Models\Students\AcademicHistory;
use App\Models\Students\StudentTransfer;
use App\Models\Students\StudentGraduation;
use App\Models\Students\StudentDocument;
use App\Models\AdminAcademics\School;
use App\Models\AdminAcademics\Campus;
use App\Models\AdminAcademics\ClassRoom;
use App\Models\AdminAcademics\Subject;
use App\Models\AdminAcademics\Term;
use App\Models\AdminAcademics\AcademicYear;
use App\Models\User;

class StudentsManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedStudentApplications();
        $this->seedStudents();
        $this->seedAcademicHistory();
        $this->seedStudentTransfers();
        $this->seedStudentGraduation();
        $this->seedStudentDocuments();
    }

    /**
     * Seed student applications.
     */
    private function seedStudentApplications(): void
    {
        $schools = School::all();
        $campuses = Campus::all();
        $users = User::all();

        if ($schools->isEmpty() || $campuses->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Skipping student applications seeding - required models not found.');
            return;
        }

        $applications = [
            [
                'school_id' => $schools->first()->id,
                'campus_id' => $campuses->first()->id,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'dob' => '2008-05-15',
                'gender' => 'male',
                'email' => 'john.doe@example.com',
                'phone' => '+1234567890',
                'parent_name' => 'Robert Doe',
                'parent_email' => 'robert.doe@example.com',
                'parent_phone' => '+1234567891',
                'status' => 'pending',
                'applied_on' => now()->subDays(5),
            ],
            [
                'school_id' => $schools->first()->id,
                'campus_id' => $campuses->first()->id,
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'dob' => '2007-08-22',
                'gender' => 'female',
                'email' => 'jane.smith@example.com',
                'phone' => '+1234567892',
                'parent_name' => 'Mary Smith',
                'parent_email' => 'mary.smith@example.com',
                'parent_phone' => '+1234567893',
                'status' => 'approved',
                'applied_on' => now()->subDays(10),
                'reviewed_on' => now()->subDays(8),
                'reviewer_id' => $users->first()->id,
            ],
            [
                'school_id' => $schools->first()->id,
                'campus_id' => $campuses->first()->id,
                'first_name' => 'Michael',
                'last_name' => 'Johnson',
                'dob' => '2006-12-03',
                'gender' => 'male',
                'email' => 'michael.johnson@example.com',
                'phone' => '+1234567894',
                'parent_name' => 'David Johnson',
                'parent_email' => 'david.johnson@example.com',
                'parent_phone' => '+1234567895',
                'status' => 'rejected',
                'applied_on' => now()->subDays(15),
                'reviewed_on' => now()->subDays(12),
                'reviewer_id' => $users->first()->id,
            ],
        ];

        foreach ($applications as $application) {
            StudentApplication::create($application);
        }

        $this->command->info('Student applications seeded successfully.');
    }

    /**
     * Seed students.
     */
    private function seedStudents(): void
    {
        $schools = School::all();
        $campuses = Campus::all();
        $classes = ClassRoom::all();

        if ($schools->isEmpty() || $campuses->isEmpty()) {
            $this->command->warn('Skipping students seeding - required models not found.');
            return;
        }

        $students = [
            [
                'school_id' => $schools->first()->id,
                'campus_id' => $campuses->first()->id,
                'class_id' => $classes->isNotEmpty() ? $classes->first()->id : null,
                'student_number' => 'STU2025001',
                'first_name' => 'Sarah',
                'last_name' => 'Wilson',
                'dob' => '2006-03-18',
                'gender' => 'female',
                'email' => 'sarah.wilson@example.com',
                'phone' => '+1234567896',
                'parent_name' => 'James Wilson',
                'parent_email' => 'james.wilson@example.com',
                'parent_phone' => '+1234567897',
                'enrollment_date' => '2024-09-01',
                'status' => 'active',
            ],
            [
                'school_id' => $schools->first()->id,
                'campus_id' => $campuses->first()->id,
                'class_id' => $classes->isNotEmpty() ? $classes->first()->id : null,
                'student_number' => 'STU2025002',
                'first_name' => 'David',
                'last_name' => 'Brown',
                'dob' => '2005-11-25',
                'gender' => 'male',
                'email' => 'david.brown@example.com',
                'phone' => '+1234567898',
                'parent_name' => 'Patricia Brown',
                'parent_email' => 'patricia.brown@example.com',
                'parent_phone' => '+1234567899',
                'enrollment_date' => '2024-09-01',
                'status' => 'active',
            ],
            [
                'school_id' => $schools->first()->id,
                'campus_id' => $campuses->first()->id,
                'class_id' => $classes->isNotEmpty() ? $classes->first()->id : null,
                'student_number' => 'STU2025003',
                'first_name' => 'Emily',
                'last_name' => 'Davis',
                'dob' => '2004-07-12',
                'gender' => 'female',
                'email' => 'emily.davis@example.com',
                'phone' => '+1234567900',
                'parent_name' => 'Thomas Davis',
                'parent_email' => 'thomas.davis@example.com',
                'parent_phone' => '+1234567901',
                'enrollment_date' => '2023-09-01',
                'status' => 'active', // Changed from 'graduated' to 'active'
            ],
            [
                'school_id' => $schools->first()->id,
                'campus_id' => $campuses->first()->id,
                'class_id' => $classes->isNotEmpty() ? $classes->first()->id : null,
                'student_number' => 'STU2025004',
                'first_name' => 'Michael',
                'last_name' => 'Johnson',
                'dob' => '2005-08-15',
                'gender' => 'male',
                'email' => 'michael.johnson@example.com',
                'phone' => '+1234567902',
                'parent_name' => 'Robert Johnson',
                'parent_email' => 'robert.johnson@example.com',
                'parent_phone' => '+1234567903',
                'enrollment_date' => '2024-09-01',
                'status' => 'active',
            ],
            [
                'school_id' => $schools->first()->id,
                'campus_id' => $campuses->first()->id,
                'class_id' => $classes->isNotEmpty() ? $classes->first()->id : null,
                'student_number' => 'STU2025005',
                'first_name' => 'Jessica',
                'last_name' => 'Martinez',
                'dob' => '2006-01-22',
                'gender' => 'female',
                'email' => 'jessica.martinez@example.com',
                'phone' => '+1234567904',
                'parent_name' => 'Maria Martinez',
                'parent_email' => 'maria.martinez@example.com',
                'parent_phone' => '+1234567905',
                'enrollment_date' => '2024-09-01',
                'status' => 'active',
            ],
            [
                'school_id' => $schools->first()->id,
                'campus_id' => $campuses->first()->id,
                'class_id' => $classes->isNotEmpty() ? $classes->first()->id : null,
                'student_number' => 'STU2025006',
                'first_name' => 'Christopher',
                'last_name' => 'Anderson',
                'dob' => '2005-12-03',
                'gender' => 'male',
                'email' => 'christopher.anderson@example.com',
                'phone' => '+1234567906',
                'parent_name' => 'Jennifer Anderson',
                'parent_email' => 'jennifer.anderson@example.com',
                'parent_phone' => '+1234567907',
                'enrollment_date' => '2024-09-01',
                'status' => 'active',
            ],
            [
                'school_id' => $schools->first()->id,
                'campus_id' => $campuses->first()->id,
                'class_id' => $classes->isNotEmpty() ? $classes->first()->id : null,
                'student_number' => 'STU2025007',
                'first_name' => 'Amanda',
                'last_name' => 'Taylor',
                'dob' => '2006-05-14',
                'gender' => 'female',
                'email' => 'amanda.taylor@example.com',
                'phone' => '+1234567908',
                'parent_name' => 'William Taylor',
                'parent_email' => 'william.taylor@example.com',
                'parent_phone' => '+1234567909',
                'enrollment_date' => '2024-09-01',
                'status' => 'active',
            ],
            [
                'school_id' => $schools->first()->id,
                'campus_id' => $campuses->first()->id,
                'class_id' => $classes->isNotEmpty() ? $classes->first()->id : null,
                'student_number' => 'STU2025008',
                'first_name' => 'Daniel',
                'last_name' => 'Thomas',
                'dob' => '2005-09-28',
                'gender' => 'male',
                'email' => 'daniel.thomas@example.com',
                'phone' => '+1234567910',
                'parent_name' => 'Lisa Thomas',
                'parent_email' => 'lisa.thomas@example.com',
                'parent_phone' => '+1234567911',
                'enrollment_date' => '2024-09-01',
                'status' => 'active',
            ],
        ];

        foreach ($students as $student) {
            Student::create($student);
        }

        $this->command->info('Students seeded successfully.');
    }

    /**
     * Seed academic history.
     */
    private function seedAcademicHistory(): void
    {
        $students = Student::all();
        $subjects = Subject::all();
        $classes = ClassRoom::all();
        $terms = Term::all();
        $academicYears = AcademicYear::all();

        if ($students->isEmpty() || $subjects->isEmpty() || $classes->isEmpty() || $terms->isEmpty() || $academicYears->isEmpty()) {
            $this->command->warn('Skipping academic history seeding - required models not found.');
            return;
        }

        // Create unique academic records to avoid constraint violations
        $academicRecords = [];
        
        // First student - different subjects
        if ($subjects->count() >= 2) {
            $academicRecords[] = [
                'student_id' => $students->first()->id,
                'subject_id' => $subjects->first()->id,
                'class_id' => $classes->first()->id,
                'term_id' => $terms->first()->id,
                'academic_year_id' => $academicYears->first()->id,
                'marks' => 85.5,
                'grade' => 'B+',
                'gpa' => 3.3,
            ];
            
            $academicRecords[] = [
                'student_id' => $students->first()->id,
                'subject_id' => $subjects->get(1)->id,
                'class_id' => $classes->first()->id,
                'term_id' => $terms->first()->id,
                'academic_year_id' => $academicYears->first()->id,
                'marks' => 92.0,
                'grade' => 'A-',
                'gpa' => 3.7,
            ];
        }
        
        // Second student - different subject
        if ($students->count() >= 2) {
            $academicRecords[] = [
                'student_id' => $students->get(1)->id,
                'subject_id' => $subjects->first()->id,
                'class_id' => $classes->first()->id,
                'term_id' => $terms->first()->id,
                'academic_year_id' => $academicYears->first()->id,
                'marks' => 78.5,
                'grade' => 'C+',
                'gpa' => 2.3,
            ];
        }

        foreach ($academicRecords as $record) {
            // Check if record already exists to avoid duplicates
            $exists = AcademicHistory::where([
                'student_id' => $record['student_id'],
                'subject_id' => $record['subject_id'],
                'class_id' => $record['class_id'],
                'term_id' => $record['term_id'],
                'academic_year_id' => $record['academic_year_id'],
            ])->exists();
            
            if (!$exists) {
                AcademicHistory::create($record);
            }
        }

        $this->command->info('Academic history seeded successfully.');
    }

    /**
     * Seed student transfers.
     */
    private function seedStudentTransfers(): void
    {
        $students = Student::all();
        $campuses = Campus::all();
        $users = User::all();

        if ($students->isEmpty() || $campuses->count() < 2 || $users->isEmpty()) {
            $this->command->warn('Skipping student transfers seeding - required models not found.');
            return;
        }

        $transfers = [
            [
                'student_id' => $students->first()->id,
                'from_campus_id' => $campuses->first()->id,
                'to_campus_id' => $campuses->get(1)->id,
                'request_date' => now()->subDays(30),
                'status' => 'pending',
            ],
            [
                'student_id' => $students->get(1)->id,
                'from_campus_id' => $campuses->first()->id,
                'to_campus_id' => $campuses->get(1)->id,
                'request_date' => now()->subDays(45),
                'approved_date' => now()->subDays(40),
                'status' => 'approved',
                'reviewer_id' => $users->first()->id,
            ],
        ];

        foreach ($transfers as $transfer) {
            StudentTransfer::create($transfer);
        }

        $this->command->info('Student transfers seeded successfully.');
    }

    /**
     * Seed student graduation.
     */
    private function seedStudentGraduation(): void
    {
        $students = Student::where('status', 'graduated')->get();

        if ($students->isEmpty()) {
            $this->command->warn('Skipping student graduation seeding - no graduated students found.');
            return;
        }

        $graduations = [
            [
                'student_id' => $students->first()->id,
                'graduation_date' => '2024-06-15',
                'diploma_number' => 'DIP2024001',
                'status' => 'issued',
            ],
        ];

        foreach ($graduations as $graduation) {
            StudentGraduation::create($graduation);
        }

        $this->command->info('Student graduation seeded successfully.');
    }

    /**
     * Seed student documents.
     */
    private function seedStudentDocuments(): void
    {
        $students = Student::all();

        if ($students->isEmpty()) {
            $this->command->warn('Skipping student documents seeding - no students found.');
            return;
        }

        $documents = [
            [
                'student_id' => $students->first()->id,
                'document_type' => 'Birth Certificate',
                'document_path' => 'students/documents/birth_cert_001.pdf',
                'original_filename' => 'birth_certificate.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 1024000, // 1MB
                'uploaded_at' => now()->subDays(10),
            ],
            [
                'student_id' => $students->first()->id,
                'document_type' => 'ID Card',
                'document_path' => 'students/documents/id_card_001.jpg',
                'original_filename' => 'student_id_card.jpg',
                'mime_type' => 'image/jpeg',
                'file_size' => 512000, // 500KB
                'uploaded_at' => now()->subDays(5),
            ],
            [
                'student_id' => $students->get(1)->id,
                'document_type' => 'Transcript',
                'document_path' => 'students/documents/transcript_001.pdf',
                'original_filename' => 'academic_transcript.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 2048000, // 2MB
                'uploaded_at' => now()->subDays(15),
            ],
        ];

        foreach ($documents as $document) {
            StudentDocument::create($document);
        }

        $this->command->info('Student documents seeded successfully.');
    }
} 