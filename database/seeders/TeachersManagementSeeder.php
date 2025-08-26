<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Teachers\Teacher;
use App\Models\AdminAcademics\Department;
use App\Models\AdminAcademics\Faculty;
use App\Models\AdminAcademics\AcademicYear;
use App\Models\AdminAcademics\Term;
use App\Models\Teachers\TeacherLeave;
use App\Models\Teachers\TeacherDocument;
use App\Models\Teachers\TeacherPerformance;
use App\Models\Teachers\TeacherTimetable;
use App\Models\AdminAcademics\ClassRoom;
use App\Models\AdminAcademics\Subject;

class TeachersManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create faculties if they don't exist
        $faculty = Faculty::firstOrCreate(
            ['name' => 'Faculty of Engineering'],
            [
                'description' => 'Faculty of Engineering and Technology',
                'status' => 'active'
            ]
        );

        // Create departments if they don't exist
        $department = Department::firstOrCreate(
            ['name' => 'Computer Science'],
            [
                'faculty_id' => $faculty->id,
                'description' => 'Department of Computer Science and Engineering',
                'status' => 'active'
            ]
        );

        // Create academic year if it doesn't exist
        $academicYear = AcademicYear::firstOrCreate(
            ['name' => '2024-2025'],
            [
                'start_date' => '2024-09-01',
                'end_date' => '2025-06-30',
                'status' => 'active'
            ]
        );

        // Create term if it doesn't exist
        $term = Term::firstOrCreate(
            ['name' => 'Fall 2024'],
            [
                'academic_year_id' => $academicYear->id,
                'start_date' => '2024-09-01',
                'end_date' => '2024-12-20',
                'status' => 'active'
            ]
        );

        // Create classes if they don't exist
        $class = ClassRoom::firstOrCreate(
            ['name' => 'CS101'],
            [
                'academic_year_id' => $academicYear->id,
                'term_id' => $term->id,
                'capacity' => 30,
                'status' => 'active'
            ]
        );

        // Create subjects if they don't exist
        $subject = Subject::firstOrCreate(
            ['name' => 'Introduction to Programming'],
            [
                'code' => 'CS101',
                'description' => 'Basic programming concepts and practices',
                'credits' => 3,
                'status' => 'active'
            ]
        );

        // Create sample teachers
        $teachers = [
            [
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'john.smith@university.edu',
                'phone' => '+1234567890',
                'date_of_birth' => '1985-03-15',
                'gender' => 'male',
                'address' => '123 University Ave, City, State 12345',
                'department_id' => $department->id,
                'faculty_id' => $faculty->id,
                'hire_date' => '2020-08-15',
                'employment_type' => 'full-time',
                'qualification' => 'Ph.D. in Computer Science',
                'specialization' => 'Software Engineering, Algorithms',
                'status' => 'active'
            ],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'email' => 'sarah.johnson@university.edu',
                'phone' => '+1234567891',
                'date_of_birth' => '1990-07-22',
                'gender' => 'female',
                'address' => '456 College Blvd, City, State 12345',
                'department_id' => $department->id,
                'faculty_id' => $faculty->id,
                'hire_date' => '2022-01-10',
                'employment_type' => 'full-time',
                'qualification' => 'M.S. in Computer Science',
                'specialization' => 'Database Systems, Web Development',
                'status' => 'active'
            ],
            [
                'first_name' => 'Michael',
                'last_name' => 'Brown',
                'email' => 'michael.brown@university.edu',
                'phone' => '+1234567892',
                'date_of_birth' => '1988-11-08',
                'gender' => 'male',
                'address' => '789 Campus Dr, City, State 12345',
                'department_id' => $department->id,
                'faculty_id' => $faculty->id,
                'hire_date' => '2021-09-01',
                'employment_type' => 'part-time',
                'qualification' => 'Ph.D. in Information Technology',
                'specialization' => 'Cybersecurity, Network Security',
                'status' => 'active'
            ]
        ];

        foreach ($teachers as $teacherData) {
            $teacher = Teacher::firstOrCreate(
                ['email' => $teacherData['email']],
                $teacherData
            );

            // Create sample leave requests for each teacher
            if ($teacher->id % 2 == 0) { // Only for some teachers
                TeacherLeave::firstOrCreate(
                    [
                        'teacher_id' => $teacher->id,
                        'start_date' => '2024-12-15',
                        'end_date' => '2024-12-20'
                    ],
                    [
                        'leave_type' => 'vacation',
                        'reason' => 'Holiday vacation with family',
                        'status' => 'pending',
                        'emergency_contact' => 'Emergency Contact',
                        'emergency_phone' => '+1987654321'
                    ]
                );
            }

            // Create sample documents for each teacher
            TeacherDocument::firstOrCreate(
                [
                    'teacher_id' => $teacher->id,
                    'document_type' => 'cv'
                ],
                [
                    'title' => 'Professional CV',
                    'description' => 'Updated professional curriculum vitae',
                    'file_path' => 'teacher_documents/sample_cv.pdf',
                    'file_name' => 'cv_' . $teacher->id . '.pdf',
                    'file_size' => 1024000,
                    'mime_type' => 'application/pdf',
                    'status' => 'approved',
                    'is_required' => true
                ]
            );

            // Create sample performance evaluations
            TeacherPerformance::firstOrCreate(
                [
                    'teacher_id' => $teacher->id,
                    'evaluation_period' => 'Fall 2024'
                ],
                [
                    'evaluation_date' => '2024-12-01',
                    'evaluator_id' => 1, // Assuming user ID 1 exists
                    'teaching_effectiveness' => rand(4, 5),
                    'classroom_management' => rand(4, 5),
                    'subject_knowledge' => rand(4, 5),
                    'communication_skills' => rand(4, 5),
                    'professional_development' => rand(4, 5),
                    'student_engagement' => rand(4, 5),
                    'assessment_quality' => rand(4, 5),
                    'overall_rating' => 4.5,
                    'strengths' => 'Excellent subject knowledge and communication skills',
                    'areas_for_improvement' => 'Could incorporate more interactive activities',
                    'recommendations' => 'Continue professional development in online teaching methods',
                    'status' => 'published'
                ]
            );

            // Create sample timetable entries
            TeacherTimetable::firstOrCreate(
                [
                    'teacher_id' => $teacher->id,
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'day_of_week' => 'monday'
                ],
                [
                    'start_time' => '09:00:00',
                    'end_time' => '10:30:00',
                    'room_number' => 'A101',
                    'academic_year_id' => $academicYear->id,
                    'term_id' => $term->id,
                    'is_active' => true,
                    'notes' => 'Introduction to Programming Lab'
                ]
            );
        }

        $this->command->info('Teachers management data seeded successfully!');
    }
} 