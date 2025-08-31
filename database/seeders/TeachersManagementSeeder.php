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
                'school_id' => 1 // Assuming school ID 1 exists
            ]
        );

        // Create departments if they don't exist
        $department = Department::firstOrCreate(
            ['name' => 'Computer Science'],
            [
                'faculty_id' => $faculty->id,
                'head_id' => null // Will be set later when teachers are created
            ]
        );

        // Create academic year if it doesn't exist
        $academicYear = AcademicYear::firstOrCreate(
            ['name' => '2024-2025'],
            [
                'start_date' => '2024-09-01',
                'end_date' => '2025-06-30',
                'is_active' => true,
                'school_id' => 1 // Assuming school ID 1 exists
            ]
        );

        // Create term if it doesn't exist
        $term = Term::firstOrCreate(
            ['name' => 'Fall 2024'],
            [
                'academic_year_id' => $academicYear->id,
                'start_date' => '2024-09-01',
                'end_date' => '2024-12-20',
                'is_active' => true
            ]
        );

        // Create classes if they don't exist
        $class = ClassRoom::firstOrCreate(
            ['name' => 'CS101'],
            [
                'campus_id' => 1, // Assuming campus ID 1 exists
                'course_id' => 1, // Assuming course ID 1 exists
                'capacity' => 30
            ]
        );

        // Create subjects if they don't exist
        $subject = Subject::firstOrCreate(
            ['name' => 'Introduction to Programming'],
            [
                'code' => 'CS101',
                'description' => 'Basic programming concepts and practices',
                'course_id' => 1, // Assuming course ID 1 exists
                'coordinator_id' => null // Will be set later when teachers are created
            ]
        );

        // Create sample teachers
        $teachers = [
            [
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'john.smith@university.edu',
                'phone' => '+1234567890',
                'dob' => '1985-03-15',
                'gender' => 'male',
                'address' => '123 University Ave, City, State 12345',
                'school_id' => 1, // Assuming school ID 1 exists
                'campus_id' => 1, // Assuming campus ID 1 exists
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
                'dob' => '1990-07-22',
                'gender' => 'female',
                'address' => '456 College Blvd, City, State 12345',
                'school_id' => 1, // Assuming school ID 1 exists
                'campus_id' => 1, // Assuming campus ID 1 exists
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
                'dob' => '1988-11-08',
                'gender' => 'male',
                'address' => '789 Campus Dr, City, State 12345',
                'school_id' => 1, // Assuming school ID 1 exists
                'campus_id' => 1, // Assuming campus ID 1 exists
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
                        'status' => 'pending',
                        'applied_on' => now()
                    ]
                );
            }

            // Create sample documents for each teacher
            TeacherDocument::firstOrCreate(
                [
                    'teacher_id' => $teacher->id,
                    'document_type' => 'CV'
                ],
                [
                    'document_path' => 'teacher_documents/sample_cv.pdf',
                    'uploaded_at' => now()
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
                    'evaluated_by' => 1, // Assuming user ID 1 exists
                    'teaching_quality' => 4.5,
                    'classroom_management' => 4.5,
                    'student_engagement' => 4.5,
                    'communication_skills' => 4.5,
                    'professional_development' => 4.5,
                    'attendance_punctuality' => 4.5,
                    'student_feedback_score' => 4.5,
                    'peer_review_score' => 4.5,
                    'supervisor_rating' => 4.5,
                    'overall_rating' => 4.5,
                    'comments' => 'Excellent subject knowledge and communication skills. Could incorporate more interactive activities.',
                    'recommendations' => 'Continue professional development in online teaching methods'
                ]
            );

            // Create sample timetable entries
            TeacherTimetable::firstOrCreate(
                [
                    'teacher_id' => $teacher->id,
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'date' => '2024-09-02' // Monday
                ],
                [
                    'start_time' => '09:00:00',
                    'end_time' => '10:30:00',
                    'room' => 'A101',
                    'lab_id' => null
                ]
            );
        }

        $this->command->info('Teachers management data seeded successfully!');
    }
} 