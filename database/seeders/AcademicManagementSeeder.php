<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\AdminAcademics\{
    School,
    Campus,
    Faculty,
    Department,
    Course,
    Subject,
    ClassRoom,
    AcademicYear,
    Term,
    Timetable,
    Exam,
    GradingScheme,
    GradeScale,
    StudentEnrollment,
    StudentGrade,
    TeacherAssignment,
    Announcement,
    Lab,
    SchoolAdmin,
    SchoolCalendar
};

class AcademicManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting Academic Management Seeder...');

        // Create users first
        $this->createUsers();
        
        // Create schools and campuses
        $this->createSchoolsAndCampuses();
        
        // Create academic structure
        $this->createAcademicStructure();
        
        // Create classes and subjects
        $this->createClassesAndSubjects();
        
        // Create academic years and terms
        $this->createAcademicYearsAndTerms();
        
        // Create timetables
        $this->createTimetables();
        
        // Create exams and grading
        $this->createExamsAndGrading();
        
        // Create enrollments and grades
        $this->createEnrollmentsAndGrades();
        
        // Create teacher assignments
        $this->createTeacherAssignments();
        
        // Create announcements
        $this->createAnnouncements();
        
        // Create school calendar events
        $this->createSchoolCalendarEvents();

        $this->command->info('✅ Academic Management Seeder completed successfully!');
    }

    /**
     * Create users for the system
     */
    private function createUsers(): void
    {
        $this->command->info('👥 Creating users...');

        // Create admin users
        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@yousch.edu',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'School Principal',
            'email' => 'principal@yousch.edu',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        // Create teachers
        $teachers = [
            ['name' => 'Dr. Sarah Johnson', 'email' => 'sarah.johnson@yousch.edu'],
            ['name' => 'Prof. Michael Chen', 'email' => 'michael.chen@yousch.edu'],
            ['name' => 'Dr. Emily Rodriguez', 'email' => 'emily.rodriguez@yousch.edu'],
            ['name' => 'Prof. David Thompson', 'email' => 'david.thompson@yousch.edu'],
            ['name' => 'Dr. Lisa Wang', 'email' => 'lisa.wang@yousch.edu'],
        ];

        foreach ($teachers as $teacher) {
            User::create([
                'name' => $teacher['name'],
                'email' => $teacher['email'],
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
        }

        // Create students
        $students = [
            ['name' => 'Alex Smith', 'email' => 'alex.smith@student.yousch.edu'],
            ['name' => 'Maria Garcia', 'email' => 'maria.garcia@student.yousch.edu'],
            ['name' => 'James Wilson', 'email' => 'james.wilson@student.yousch.edu'],
            ['name' => 'Emma Davis', 'email' => 'emma.davis@student.yousch.edu'],
            ['name' => 'Noah Brown', 'email' => 'noah.brown@student.yousch.edu'],
            ['name' => 'Sophia Lee', 'email' => 'sophia.lee@student.yousch.edu'],
            ['name' => 'Lucas Anderson', 'email' => 'lucas.anderson@student.yousch.edu'],
            ['name' => 'Olivia Taylor', 'email' => 'olivia.taylor@student.yousch.edu'],
        ];

        foreach ($students as $student) {
            User::create([
                'name' => $student['name'],
                'email' => $student['email'],
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
        }

        $this->command->info('✅ Users created successfully');
    }

    /**
     * Create schools and campuses
     */
    private function createSchoolsAndCampuses(): void
    {
        $this->command->info('🏫 Creating schools and campuses...');

        // Create main school
        $school = School::create([
            'name' => 'Yousch International School',
            'domain' => 'yousch.edu',
            'contact_info' => 'Contact us for more information',
            'address' => '123 Education Street, Knowledge City',
            'phone' => '+1-555-0123',
            'email' => 'info@yousch.edu',
            'website' => 'https://www.yousch.edu',
            'is_active' => true,
        ]);

        // Create campuses
        $campuses = [
            [
                'name' => 'Main Campus',
                'address' => '123 Education Street, Knowledge City',
                'phone' => '+1-555-0123',
                'email' => 'main@yousch.edu',
                'is_active' => true,
            ],
            [
                'name' => 'North Campus',
                'address' => '456 Learning Avenue, North District',
                'phone' => '+1-555-0124',
                'email' => 'north@yousch.edu',
                'is_active' => true,
            ],
            [
                'name' => 'East Campus',
                'address' => '789 Wisdom Road, East Quarter',
                'phone' => '+1-555-0125',
                'email' => 'east@yousch.edu',
                'is_active' => true,
            ],
        ];

        foreach ($campuses as $campusData) {
            Campus::create(array_merge($campusData, ['school_id' => $school->id]));
        }

        $this->command->info('✅ Schools and campuses created successfully');
    }

    /**
     * Create academic structure (faculties, departments, courses, subjects)
     */
    private function createAcademicStructure(): void
    {
        $this->command->info('📚 Creating academic structure...');

        $school = School::first();
        $mainCampus = Campus::where('name', 'Main Campus')->first();

        // Create faculties
        $faculties = [
            [
                'name' => 'Faculty of Science and Technology',
                'description' => 'Leading research and education in science and technology',
            ],
            [
                'name' => 'Faculty of Business and Economics',
                'description' => 'Preparing future business leaders and economists',
            ],
            [
                'name' => 'Faculty of Arts and Humanities',
                'description' => 'Exploring creativity, culture, and human expression',
            ],
        ];

        foreach ($faculties as $facultyData) {
            Faculty::create(array_merge($facultyData, ['school_id' => $school->id]));
        }

        // Create departments
        $departments = [
            [
                'faculty_id' => Faculty::where('name', 'Faculty of Science and Technology')->first()->id,
                'name' => 'Computer Science',
                'head_id' => User::where('email', 'sarah.johnson@yousch.edu')->first()->id,
            ],
            [
                'faculty_id' => Faculty::where('name', 'Faculty of Science and Technology')->first()->id,
                'name' => 'Mathematics',
                'head_id' => User::where('email', 'michael.chen@yousch.edu')->first()->id,
            ],
            [
                'faculty_id' => Faculty::where('name', 'Faculty of Business and Economics')->first()->id,
                'name' => 'Business Administration',
                'head_id' => User::where('email', 'emily.rodriguez@yousch.edu')->first()->id,
            ],
            [
                'faculty_id' => Faculty::where('name', 'Faculty of Arts and Humanities')->first()->id,
                'name' => 'English Literature',
                'head_id' => User::where('email', 'david.thompson@yousch.edu')->first()->id,
            ],
        ];

        foreach ($departments as $departmentData) {
            Department::create($departmentData);
        }

        // Create courses
        $courses = [
            [
                'department_id' => Department::where('name', 'Computer Science')->first()->id,
                'name' => 'Bachelor of Computer Science',
                'code' => 'BCS',
                'description' => 'Comprehensive computer science program',
            ],
            [
                'department_id' => Department::where('name', 'Mathematics')->first()->id,
                'name' => 'Bachelor of Mathematics',
                'code' => 'BMATH',
                'description' => 'Advanced mathematics program',
            ],
            [
                'department_id' => Department::where('name', 'Business Administration')->first()->id,
                'name' => 'Bachelor of Business Administration',
                'code' => 'BBA',
                'description' => 'Business management program',
            ],
            [
                'department_id' => Department::where('name', 'English Literature')->first()->id,
                'name' => 'Bachelor of Arts in English',
                'code' => 'BAENG',
                'description' => 'English literature and language program',
            ],
        ];

        foreach ($courses as $courseData) {
            Course::create($courseData);
        }

        // Create subjects
        $subjects = [
            [
                'course_id' => Course::where('code', 'BCS')->first()->id,
                'name' => 'Introduction to Programming',
                'code' => 'CS101',
                'description' => 'Basic programming concepts and practices',
                'coordinator_id' => User::where('email', 'sarah.johnson@yousch.edu')->first()->id,
            ],
            [
                'course_id' => Course::where('code', 'BCS')->first()->id,
                'name' => 'Data Structures and Algorithms',
                'code' => 'CS201',
                'description' => 'Advanced programming concepts',
                'coordinator_id' => User::where('email', 'sarah.johnson@yousch.edu')->first()->id,
            ],
            [
                'course_id' => Course::where('code', 'BMATH')->first()->id,
                'name' => 'Calculus I',
                'code' => 'MATH101',
                'description' => 'Fundamental calculus concepts',
                'coordinator_id' => User::where('email', 'michael.chen@yousch.edu')->first()->id,
            ],
            [
                'course_id' => Course::where('code', 'BBA')->first()->id,
                'name' => 'Principles of Management',
                'code' => 'BUS101',
                'description' => 'Basic management principles',
                'coordinator_id' => User::where('email', 'emily.rodriguez@yousch.edu')->first()->id,
            ],
            [
                'course_id' => Course::where('code', 'BAENG')->first()->id,
                'name' => 'Introduction to Literature',
                'code' => 'ENG101',
                'description' => 'Literary analysis and appreciation',
                'coordinator_id' => User::where('email', 'david.thompson@yousch.edu')->first()->id,
            ],
        ];

        foreach ($subjects as $subjectData) {
            Subject::create($subjectData);
        }

        // Create labs for computer science subjects
        $csSubject = Subject::where('code', 'CS101')->first();
        Lab::create([
            'subject_id' => $csSubject->id,
            'name' => 'Programming Lab 1',
            'description' => 'Computer lab for programming exercises',
            'schedule' => 'Monday 2:00 PM - 4:00 PM',
        ]);

        $this->command->info('✅ Academic structure created successfully');
    }

    /**
     * Create classes and subjects
     */
    private function createClassesAndSubjects(): void
    {
        $this->command->info('🏫 Creating classes and subject assignments...');

        $mainCampus = Campus::where('name', 'Main Campus')->first();
        $courses = Course::all();

        // Create classes for each course
        foreach ($courses as $course) {
            ClassRoom::create([
                'campus_id' => $mainCampus->id,
                'course_id' => $course->id,
                'name' => $course->code . ' - Class A',
                'capacity' => 30,
            ]);

            ClassRoom::create([
                'campus_id' => $mainCampus->id,
                'course_id' => $course->id,
                'name' => $course->code . ' - Class B',
                'capacity' => 25,
            ]);
        }

        $this->command->info('✅ Classes and subject assignments created successfully');
    }

    /**
     * Create academic years and terms
     */
    private function createAcademicYearsAndTerms(): void
    {
        $this->command->info('📅 Creating academic years and terms...');

        $school = School::first();

        // Create current academic year
        $currentYear = AcademicYear::create([
            'school_id' => $school->id,
            'name' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'is_active' => true,
        ]);

        // Create terms for current academic year
        $terms = [
            [
                'name' => 'Fall Semester',
                'start_date' => '2024-09-01',
                'end_date' => '2024-12-20',
                'is_active' => true,
            ],
            [
                'name' => 'Spring Semester',
                'start_date' => '2025-01-15',
                'end_date' => '2025-05-15',
                'is_active' => false,
            ],
            [
                'name' => 'Summer Session',
                'start_date' => '2025-06-01',
                'end_date' => '2025-07-31',
                'is_active' => false,
            ],
        ];

        foreach ($terms as $termData) {
            Term::create(array_merge($termData, ['academic_year_id' => $currentYear->id]));
        }

        $this->command->info('✅ Academic years and terms created successfully');
    }

    /**
     * Create timetables
     */
    private function createTimetables(): void
    {
        $this->command->info('⏰ Creating timetables...');

        $classes = ClassRoom::all();
        $subjects = Subject::all();
        $teachers = User::whereIn('email', [
            'sarah.johnson@yousch.edu',
            'michael.chen@yousch.edu',
            'emily.rodriguez@yousch.edu',
            'david.thompson@yousch.edu'
        ])->get();

        $currentDate = now()->startOfWeek();
        
        foreach ($classes as $class) {
            $course = $class->course;
            $courseSubjects = Subject::where('course_id', $course->id)->get();
            
            foreach ($courseSubjects as $index => $subject) {
                $teacher = $teachers[$index % $teachers->count()];
                
                // Create weekly schedule for this subject
                for ($day = 0; $day < 5; $day++) { // Monday to Friday
                    $date = $currentDate->copy()->addDays($day);
                    
                    Timetable::create([
                        'class_id' => $class->id,
                        'subject_id' => $subject->id,
                        'teacher_id' => $teacher->id,
                        'date' => $date->format('Y-m-d'),
                        'start_time' => '09:00:00',
                        'end_time' => '10:30:00',
                        'room' => 'Room ' . ($index + 101),
                    ]);
                    
                    Timetable::create([
                        'class_id' => $class->id,
                        'subject_id' => $subject->id,
                        'teacher_id' => $teacher->id,
                        'date' => $date->format('Y-m-d'),
                        'start_time' => '14:00:00',
                        'end_time' => '15:30:00',
                        'room' => 'Room ' . ($index + 101),
                    ]);
                }
            }
        }

        $this->command->info('✅ Timetables created successfully');
    }

    /**
     * Create exams and grading schemes
     */
    private function createExamsAndGrading(): void
    {
        $this->command->info('📝 Creating exams and grading schemes...');

        $school = School::first();
        $subjects = Subject::all();
        $classes = ClassRoom::all();
        $teachers = User::whereIn('email', [
            'sarah.johnson@yousch.edu',
            'michael.chen@yousch.edu',
            'emily.rodriguez@yousch.edu',
            'david.thompson@yousch.edu'
        ])->get();

        // Create grading scheme
        $gradingScheme = GradingScheme::create([
            'name' => 'Standard Grading Scale',
            'description' => 'Standard A-F grading scale',
            'school_id' => $school->id,
            'is_active' => true,
            'min_score' => 0.00,
            'max_score' => 100.00,
            'passing_score' => 60.00,
            'grade_scale_type' => 'letter',
            'created_by' => User::where('email', 'admin@yousch.edu')->first()->id,
        ]);

        // Create grade scales
        $gradeScales = [
            ['grade' => 'A', 'min_score' => 90.00, 'max_score' => 100.00, 'grade_point' => 4.00, 'is_passing' => true],
            ['grade' => 'B', 'min_score' => 80.00, 'max_score' => 89.99, 'grade_point' => 3.00, 'is_passing' => true],
            ['grade' => 'C', 'min_score' => 70.00, 'max_score' => 79.99, 'grade_point' => 2.00, 'is_passing' => true],
            ['grade' => 'D', 'min_score' => 60.00, 'max_score' => 69.99, 'grade_point' => 1.00, 'is_passing' => true],
            ['grade' => 'F', 'min_score' => 0.00, 'max_score' => 59.99, 'grade_point' => 0.00, 'is_passing' => false],
        ];

        foreach ($gradeScales as $scaleData) {
            GradeScale::create(array_merge($scaleData, ['grading_scheme_id' => $gradingScheme->id]));
        }

        // Create exams for each subject
        foreach ($subjects as $subject) {
            $examTypes = ['midterm', 'final', 'quiz'];
            
            foreach ($examTypes as $type) {
                Exam::create([
                    'name' => ucfirst($type) . ' Exam - ' . $subject->name,
                    'type' => $type,
                    'subject_id' => $subject->id,
                    'class_id' => $classes->where('course_id', $subject->course_id)->first()->id,
                    'coordinator_id' => $subject->coordinator_id,
                    'exam_date' => now()->addDays(rand(30, 90)),
                    'start_time' => '09:00:00',
                    'end_time' => '11:00:00',
                    'duration_minutes' => 120,
                    'total_marks' => 100,
                    'passing_marks' => 60,
                    'description' => ucfirst($type) . ' examination for ' . $subject->name,
                    'instructions' => 'Please read all questions carefully before answering.',
                    'is_active' => true,
                    'school_id' => $school->id,
                ]);
            }
        }

        $this->command->info('✅ Exams and grading schemes created successfully');
    }

    /**
     * Create student enrollments and grades
     */
    private function createEnrollmentsAndGrades(): void
    {
        $this->command->info('📚 Creating student enrollments and grades...');

        $school = School::first();
        $academicYear = AcademicYear::where('is_active', true)->first();
        $classes = ClassRoom::all();
        $students = User::where('email', 'like', '%@student.yousch.edu')->get();
        $subjects = Subject::all();
        $exams = Exam::all();

        // Create enrollments
        foreach ($students as $student) {
            $class = $classes->random();
            
            StudentEnrollment::create([
                'student_id' => $student->id,
                'class_id' => $class->id,
                'academic_year_id' => $academicYear->id,
                'enrollment_date' => now()->subDays(rand(30, 60)),
                'status' => 'active',
                'enrolled_by' => User::where('email', 'admin@yousch.edu')->first()->id,
                'notes' => 'Regular enrollment',
                'school_id' => $school->id,
            ]);
        }

        // Create grades for enrolled students
        foreach ($students as $student) {
            $enrollment = StudentEnrollment::where('student_id', $student->id)->first();
            if (!$enrollment) continue;

            $class = ClassRoom::find($enrollment->class_id);
            $classSubjects = Subject::where('course_id', $class->course_id)->get();
            $term = Term::where('is_active', true)->first();

            foreach ($classSubjects as $subject) {
                $exam = $exams->where('subject_id', $subject->id)->first();
                if (!$exam) continue;

                $score = rand(65, 95); // Random score between 65-95
                
                StudentGrade::create([
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'class_id' => $class->id,
                    'exam_id' => $exam->id,
                    'academic_year_id' => $academicYear->id,
                    'term_id' => $term->id,
                    'grade' => $this->calculateGrade($score),
                    'score' => $score,
                    'max_score' => 100,
                    'percentage' => $score,
                    'remarks' => 'Good performance',
                    'graded_by' => $subject->coordinator_id,
                    'graded_at' => now(),
                    'school_id' => $school->id,
                ]);
            }
        }

        $this->command->info('✅ Student enrollments and grades created successfully');
    }

    /**
     * Create teacher assignments
     */
    private function createTeacherAssignments(): void
    {
        $this->command->info('👨‍🏫 Creating teacher assignments...');

        $school = School::first();
        $academicYear = AcademicYear::where('is_active', true)->first();
        $classes = ClassRoom::all();
        $teachers = User::whereIn('email', [
            'sarah.johnson@yousch.edu',
            'michael.chen@yousch.edu',
            'emily.rodriguez@yousch.edu',
            'david.thompson@yousch.edu'
        ])->get();

        foreach ($classes as $index => $class) {
            $teacher = $teachers[$index % $teachers->count()];
            
            TeacherAssignment::create([
                'teacher_id' => $teacher->id,
                'class_id' => $class->id,
                'subject_id' => Subject::where('course_id', $class->course_id)->first()->id,
                'academic_year_id' => $academicYear->id,
                'role' => 'teacher',
                'is_primary' => true,
                'is_active' => true,
                'assigned_by' => User::where('email', 'admin@yousch.edu')->first()->id,
                'assigned_at' => now(),
                'notes' => 'Primary teacher assignment',
                'school_id' => $school->id,
            ]);
        }

        $this->command->info('✅ Teacher assignments created successfully');
    }

    /**
     * Create announcements
     */
    private function createAnnouncements(): void
    {
        $this->command->info('📢 Creating announcements...');

        $school = School::first();
        $mainCampus = Campus::where('name', 'Main Campus')->first();
        $classes = ClassRoom::limit(2)->get();

        $announcements = [
            [
                'title' => 'Welcome to New Academic Year 2024-2025!',
                'content' => 'We are excited to welcome all students to the new academic year. Classes begin on September 1st, 2024.',
                'scope' => 'school',
                'priority' => 'high',
                'school_id' => $school->id,
                'campus_id' => null,
                'class_id' => null,
                'is_active' => true,
                'is_urgent' => false,
                'publish_date' => now()->subDays(5),
                'expiry_date' => now()->addDays(30),
                'created_by' => User::where('email', 'admin@yousch.edu')->first()->id,
                'target_audience' => 'all',
                'attachments' => [],
            ],
            [
                'title' => 'Campus Maintenance Notice',
                'content' => 'Scheduled maintenance will be conducted on the main campus this weekend. Please plan accordingly.',
                'scope' => 'campus',
                'priority' => 'normal',
                'school_id' => $school->id,
                'campus_id' => $mainCampus->id,
                'class_id' => null,
                'is_active' => true,
                'is_urgent' => false,
                'publish_date' => now()->subDays(2),
                'expiry_date' => now()->addDays(7),
                'created_by' => User::where('email', 'admin@yousch.edu')->first()->id,
                'target_audience' => 'all',
                'attachments' => [],
            ],
            [
                'title' => 'Computer Science Lab Schedule Update',
                'content' => 'The programming lab schedule has been updated. Please check your timetables.',
                'scope' => 'class',
                'priority' => 'normal',
                'school_id' => $school->id,
                'campus_id' => $mainCampus->id,
                'class_id' => $classes->first()->id,
                'is_active' => true,
                'is_urgent' => false,
                'publish_date' => now()->subDays(1),
                'expiry_date' => now()->addDays(14),
                'created_by' => User::where('email', 'sarah.johnson@yousch.edu')->first()->id,
                'target_audience' => 'students',
                'attachments' => [],
            ],
        ];

        foreach ($announcements as $announcementData) {
            Announcement::create($announcementData);
        }

        $this->command->info('✅ Announcements created successfully');
    }

    /**
     * Create school calendar events
     */
    private function createSchoolCalendarEvents(): void
    {
        $this->command->info('📅 Creating school calendar events...');

        $school = School::first();

        $calendarEvents = [
            [
                'title' => 'Academic Year Start',
                'type' => 'academic_year_start',
                'start_date' => '2024-09-01',
                'end_date' => '2024-09-01',
                'description' => 'First day of the academic year',
                'is_recurring' => false,
            ],
            [
                'title' => 'Fall Break',
                'type' => 'holiday',
                'start_date' => '2024-11-25',
                'end_date' => '2024-11-29',
                'description' => 'Fall semester break',
                'is_recurring' => false,
            ],
            [
                'title' => 'Winter Break',
                'type' => 'holiday',
                'start_date' => '2024-12-23',
                'end_date' => '2025-01-05',
                'description' => 'Winter holiday break',
                'is_recurring' => false,
            ],
            [
                'title' => 'Spring Break',
                'type' => 'holiday',
                'start_date' => '2025-03-17',
                'end_date' => '2025-03-21',
                'description' => 'Spring semester break',
                'is_recurring' => false,
            ],
            [
                'title' => 'Final Exams Week',
                'type' => 'exam_period',
                'start_date' => '2025-05-12',
                'end_date' => '2025-05-16',
                'description' => 'Final examinations for all courses',
                'is_recurring' => false,
            ],
        ];

        foreach ($calendarEvents as $eventData) {
            SchoolCalendar::create(array_merge($eventData, ['school_id' => $school->id]));
        }

        $this->command->info('✅ School calendar events created successfully');
    }

    /**
     * Calculate grade based on score
     */
    private function calculateGrade($score): string
    {
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'F';
    }
} 