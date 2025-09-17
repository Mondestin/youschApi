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
use App\Models\ExamsGradings\ExamType;

class AcademicManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Démarrage du Seeder de Gestion Académique...');

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

        $this->command->info('✅ Seeder de Gestion Académique terminé avec succès !');
    }

    /**
     * Create users for the system
     */
    private function createUsers(): void
    {
        $this->command->info('👥 Création des utilisateurs...');

        // Create admin users
        User::create([
            'name' => 'Administrateur Système',
            'email' => 'admin@yousch.edu',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Directeur de l\'École',
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

        $this->command->info('✅ Utilisateurs créés avec succès');
    }

    /**
     * Create schools and campuses
     */
    private function createSchoolsAndCampuses(): void
    {
        $this->command->info('🏫 Création des écoles et campus...');

        // Create main school
        $school = School::create([
            'name' => 'École Internationale Yousch',
            'domain' => 'yousch.edu',
            'contact_info' => 'Contactez-nous pour plus d\'informations',
            'address' => '123 Rue de l\'Éducation, Cité du Savoir',
            'phone' => '+1-555-0123',
            'email' => 'info@yousch.edu',
            'website' => 'https://www.yousch.edu',
            'is_active' => true,
        ]);

        // Create campuses
        $campuses = [
            [
                'name' => 'Campus Principal',
                'address' => '123 Rue de l\'Éducation, Cité du Savoir',
                'phone' => '+1-555-0123',
                'email' => 'main@yousch.edu',
                'is_active' => true,
            ],
            [
                'name' => 'Campus Nord',
                'address' => '456 Avenue de l\'Apprentissage, District Nord',
                'phone' => '+1-555-0124',
                'email' => 'north@yousch.edu',
                'is_active' => true,
            ],
            [
                'name' => 'Campus Est',
                'address' => '789 Route de la Sagesse, Quartier Est',
                'phone' => '+1-555-0125',
                'email' => 'east@yousch.edu',
                'is_active' => true,
            ],
        ];

        foreach ($campuses as $campusData) {
            Campus::create(array_merge($campusData, ['school_id' => $school->id]));
        }

        $this->command->info('✅ Écoles et campus créés avec succès');
    }

    /**
     * Create academic structure (faculties, departments, courses, subjects)
     */
    private function createAcademicStructure(): void
    {
        $this->command->info('📚 Création de la structure académique...');

        $school = School::first();
        $mainCampus = Campus::where('name', 'Campus Principal')->first();

        // Create faculties
        $faculties = [
            [
                'name' => 'Faculté des Sciences et Technologies',
                'description' => 'Recherche et éducation de pointe en sciences et technologies',
            ],
            [
                'name' => 'Faculté des Affaires et de l\'Économie',
                'description' => 'Préparation des futurs leaders d\'affaires et économistes',
            ],
            [
                'name' => 'Faculté des Arts et Humanités',
                'description' => 'Exploration de la créativité, de la culture et de l\'expression humaine',
            ],
        ];

        foreach ($faculties as $facultyData) {
            Faculty::create(array_merge($facultyData, ['school_id' => $school->id]));
        }

        // Create departments
        $departments = [
            [
                'faculty_id' => Faculty::where('name', 'Faculté des Sciences et Technologies')->first()->id,
                'name' => 'Informatique',
                'head_id' => User::where('email', 'sarah.johnson@yousch.edu')->first()->id,
            ],
            [
                'faculty_id' => Faculty::where('name', 'Faculté des Sciences et Technologies')->first()->id,
                'name' => 'Mathématiques',
                'head_id' => User::where('email', 'michael.chen@yousch.edu')->first()->id,
            ],
            [
                'faculty_id' => Faculty::where('name', 'Faculté des Affaires et de l\'Économie')->first()->id,
                'name' => 'Administration des Affaires',
                'head_id' => User::where('email', 'emily.rodriguez@yousch.edu')->first()->id,
            ],
            [
                'faculty_id' => Faculty::where('name', 'Faculté des Arts et Humanités')->first()->id,
                'name' => 'Littérature Anglaise',
                'head_id' => User::where('email', 'david.thompson@yousch.edu')->first()->id,
            ],
        ];

        foreach ($departments as $departmentData) {
            Department::create($departmentData);
        }

        // Create courses
        $courses = [
            [
                'department_id' => Department::where('name', 'Informatique')->first()->id,
                'name' => 'Baccalauréat en Informatique',
                'code' => 'BCS',
                'description' => 'Programme complet d\'informatique',
            ],
            [
                'department_id' => Department::where('name', 'Mathématiques')->first()->id,
                'name' => 'Baccalauréat en Mathématiques',
                'code' => 'BMATH',
                'description' => 'Programme de mathématiques avancées',
            ],
            [
                'department_id' => Department::where('name', 'Administration des Affaires')->first()->id,
                'name' => 'Baccalauréat en Administration des Affaires',
                'code' => 'BBA',
                'description' => 'Programme de gestion des affaires',
            ],
            [
                'department_id' => Department::where('name', 'Littérature Anglaise')->first()->id,
                'name' => 'Baccalauréat ès Arts en Anglais',
                'code' => 'BAENG',
                'description' => 'Programme de littérature et langue anglaise',
            ],
        ];

        foreach ($courses as $courseData) {
            Course::create($courseData);
        }

        // Create subjects
        $subjects = [
            // Computer Science Subjects
            [
                'course_id' => Course::where('code', 'BCS')->first()->id,
                'name' => 'Introduction à la Programmation',
                'code' => 'CS101',
                'description' => 'Concepts et pratiques de programmation de base',
                'coordinator_id' => User::where('email', 'sarah.johnson@yousch.edu')->first()->id,
            ],
            [
                'course_id' => Course::where('code', 'BCS')->first()->id,
                'name' => 'Structures de Données et Algorithmes',
                'code' => 'CS201',
                'description' => 'Concepts de programmation avancés',
                'coordinator_id' => User::where('email', 'sarah.johnson@yousch.edu')->first()->id,
            ],
            [
                'course_id' => Course::where('code', 'BCS')->first()->id,
                'name' => 'Bases de Données',
                'code' => 'CS301',
                'description' => 'Conception et gestion de bases de données',
                'coordinator_id' => User::where('email', 'sarah.johnson@yousch.edu')->first()->id,
            ],
            [
                'course_id' => Course::where('code', 'BCS')->first()->id,
                'name' => 'Développement Web',
                'code' => 'CS401',
                'description' => 'Développement d\'applications web modernes',
                'coordinator_id' => User::where('email', 'sarah.johnson@yousch.edu')->first()->id,
            ],
            [
                'course_id' => Course::where('code', 'BCS')->first()->id,
                'name' => 'Projet de Fin d\'Études',
                'code' => 'CS501',
                'description' => 'Projet intégrateur en informatique',
                'coordinator_id' => User::where('email', 'sarah.johnson@yousch.edu')->first()->id,
            ],
            
            // Mathematics Subjects
            [
                'course_id' => Course::where('code', 'BMATH')->first()->id,
                'name' => 'Calcul I',
                'code' => 'MATH101',
                'description' => 'Concepts fondamentaux du calcul',
                'coordinator_id' => User::where('email', 'michael.chen@yousch.edu')->first()->id,
            ],
            [
                'course_id' => Course::where('code', 'BMATH')->first()->id,
                'name' => 'Algèbre Linéaire',
                'code' => 'MATH201',
                'description' => 'Algèbre linéaire et géométrie analytique',
                'coordinator_id' => User::where('email', 'michael.chen@yousch.edu')->first()->id,
            ],
            [
                'course_id' => Course::where('code', 'BMATH')->first()->id,
                'name' => 'Statistiques et Probabilités',
                'code' => 'MATH301',
                'description' => 'Statistiques descriptives et inférentielles',
                'coordinator_id' => User::where('email', 'michael.chen@yousch.edu')->first()->id,
            ],
            [
                'course_id' => Course::where('code', 'BMATH')->first()->id,
                'name' => 'Analyse Mathématique',
                'code' => 'MATH401',
                'description' => 'Analyse mathématique avancée',
                'coordinator_id' => User::where('email', 'michael.chen@yousch.edu')->first()->id,
            ],
            
            // Business Subjects
            [
                'course_id' => Course::where('code', 'BBA')->first()->id,
                'name' => 'Principes de Gestion',
                'code' => 'BUS101',
                'description' => 'Principes de gestion de base',
                'coordinator_id' => User::where('email', 'emily.rodriguez@yousch.edu')->first()->id,
            ],
            [
                'course_id' => Course::where('code', 'BBA')->first()->id,
                'name' => 'Marketing Fondamental',
                'code' => 'BUS201',
                'description' => 'Concepts et stratégies de marketing',
                'coordinator_id' => User::where('email', 'emily.rodriguez@yousch.edu')->first()->id,
            ],
            [
                'course_id' => Course::where('code', 'BBA')->first()->id,
                'name' => 'Gestion Financière',
                'code' => 'BUS301',
                'description' => 'Gestion financière et comptabilité',
                'coordinator_id' => User::where('email', 'emily.rodriguez@yousch.edu')->first()->id,
            ],
            [
                'course_id' => Course::where('code', 'BBA')->first()->id,
                'name' => 'Stratégie d\'Entreprise',
                'code' => 'BUS401',
                'description' => 'Stratégie et planification d\'entreprise',
                'coordinator_id' => User::where('email', 'emily.rodriguez@yousch.edu')->first()->id,
            ],
            
            // English Subjects
            [
                'course_id' => Course::where('code', 'BAENG')->first()->id,
                'name' => 'Introduction à la Littérature',
                'code' => 'ENG101',
                'description' => 'Analyse et appréciation littéraire',
                'coordinator_id' => User::where('email', 'david.thompson@yousch.edu')->first()->id,
            ],
            [
                'course_id' => Course::where('code', 'BAENG')->first()->id,
                'name' => 'Écriture Avancée',
                'code' => 'ENG201',
                'description' => 'Techniques d\'écriture avancées',
                'coordinator_id' => User::where('email', 'david.thompson@yousch.edu')->first()->id,
            ],
            [
                'course_id' => Course::where('code', 'BAENG')->first()->id,
                'name' => 'Littérature Moderne',
                'code' => 'ENG301',
                'description' => 'Étude de la littérature moderne',
                'coordinator_id' => User::where('email', 'david.thompson@yousch.edu')->first()->id,
            ],
            [
                'course_id' => Course::where('code', 'BAENG')->first()->id,
                'name' => 'Critique Littéraire',
                'code' => 'ENG401',
                'description' => 'Méthodes de critique littéraire',
                'coordinator_id' => User::where('email', 'david.thompson@yousch.edu')->first()->id,
            ],
        ];

        foreach ($subjects as $subjectData) {
            Subject::create($subjectData);
        }

        // Create labs for various subjects
        $this->createLabsForSubjects();
        
        // Create prerequisite relationships
        $this->createPrerequisiteRelationships();

        $this->command->info('✅ Structure académique créée avec succès');
    }

    /**
     * Create classes and subjects
     */
    private function createClassesAndSubjects(): void
    {
        $this->command->info('🏫 Création des classes et affectations de matières...');

        $mainCampus = Campus::where('name', 'Campus Principal')->first();
        $courses = Course::all();

        // Create classes for each course
        foreach ($courses as $course) {
            ClassRoom::create([
                'campus_id' => $mainCampus->id,
                'course_id' => $course->id,
                'name' => $course->code . ' - Classe A',
                'capacity' => 30,
            ]);

            ClassRoom::create([
                'campus_id' => $mainCampus->id,
                'course_id' => $course->id,
                'name' => $course->code . ' - Classe B',
                'capacity' => 25,
            ]);
        }

        $this->command->info('✅ Classes et affectations de matières créées avec succès');
    }

    /**
     * Create academic years and terms
     */
    private function createAcademicYearsAndTerms(): void
    {
        $this->command->info('📅 Création des années académiques et trimestres...');

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
                'name' => 'Semestre d\'Automne',
                'start_date' => '2024-09-01',
                'end_date' => '2024-12-20',
                'is_active' => true,
            ],
            [
                'name' => 'Semestre de Printemps',
                'start_date' => '2025-01-15',
                'end_date' => '2025-05-15',
                'is_active' => false,
            ],
            [
                'name' => 'Session d\'Été',
                'start_date' => '2025-06-01',
                'end_date' => '2025-07-31',
                'is_active' => false,
            ],
        ];

        foreach ($terms as $termData) {
            Term::create(array_merge($termData, ['academic_year_id' => $currentYear->id]));
        }

        $this->command->info('✅ Années académiques et trimestres créés avec succès');
    }

    /**
     * Create timetables
     */
    private function createTimetables(): void
    {
        $this->command->info('⏰ Création des emplois du temps...');

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
                    
                    // Get a random venue for the timetable entry
                    $venue = \App\Models\AdminAcademics\Venue::inRandomOrder()->first();
                    
                    Timetable::create([
                        'class_id' => $class->id,
                        'subject_id' => $subject->id,
                        'teacher_id' => $teacher->id,
                        'date' => $date->format('Y-m-d'),
                        'start_time' => '09:00:00',
                        'end_time' => '10:30:00',
                        'venue_id' => $venue ? $venue->id : null,
                    ]);
                    
                    Timetable::create([
                        'class_id' => $class->id,
                        'subject_id' => $subject->id,
                        'teacher_id' => $teacher->id,
                        'date' => $date->format('Y-m-d'),
                        'start_time' => '14:00:00',
                        'end_time' => '15:30:00',
                        'venue_id' => $venue ? $venue->id : null,
                    ]);
                }
            }
        }

        $this->command->info('✅ Emplois du temps créés avec succès');
    }

    /**
     * Create exams and grading schemes
     */
    private function createExamsAndGrading(): void
    {
        $this->command->info('📝 Création des examens et systèmes de notation...');

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
            'name' => 'Échelle de Notation Standard',
            'description' => 'Échelle de notation standard A-F',
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
            $examTypes = ['mi-parcours', 'final', 'quiz'];
            
            foreach ($examTypes as $type) {
                $examType = ExamType::where('name', $type)->first();
                if (!$examType) {
                    $this->command->warn("Type d'examen '{$type}' non trouvé, passage...");
                    continue;
                }
                
                Exam::create([
                    'name' => 'Examen ' . ucfirst($type) . ' - ' . $subject->name,
                    'subject_id' => $subject->id,
                    'class_id' => $classes->where('course_id', $subject->course_id)->first()->id,
                    'exam_date' => now()->addDays(rand(30, 90)),
                    'start_time' => '09:00:00',
                    'end_time' => '11:00:00',
                    'instructions' => 'Veuillez lire attentivement toutes les questions avant de répondre.',
                    'lab_id' => null, // No lab for general exams
                    'exam_type_id' => $examType->id,
                    'examiner_id' => $subject->coordinator_id,
                    'status' => 'scheduled',
                ]);
            }
        }

        $this->command->info('✅ Examens et systèmes de notation créés avec succès');
    }

    /**
     * Create student enrollments and grades
     */
    private function createEnrollmentsAndGrades(): void
    {
        $this->command->info('📚 Création des inscriptions et notes des étudiants...');

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
                'notes' => 'Inscription régulière',
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
                    'remarks' => 'Bonne performance',
                    'graded_by' => $subject->coordinator_id,
                    'graded_at' => now(),
                    'school_id' => $school->id,
                ]);
            }
        }

        $this->command->info('✅ Inscriptions et notes des étudiants créées avec succès');
    }

    /**
     * Create teacher assignments
     */
    private function createTeacherAssignments(): void
    {
        $this->command->info('👨‍🏫 Création des affectations d\'enseignants...');

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
                'assignment_date' => now(),
                'notes' => 'Affectation d\'enseignant principal',
                'school_id' => $school->id,
            ]);
        }

        $this->command->info('✅ Affectations d\'enseignants créées avec succès');
    }

    /**
     * Create announcements
     */
    private function createAnnouncements(): void
    {
        $this->command->info('📢 Création des annonces...');

        $school = School::first();
        $mainCampus = Campus::where('name', 'Campus Principal')->first();
        $classes = ClassRoom::limit(2)->get();

        $announcements = [
            [
                'title' => 'Bienvenue à la Nouvelle Année Académique 2024-2025 !',
                'content' => 'Nous sommes ravis d\'accueillir tous les étudiants à la nouvelle année académique. Les cours commencent le 1er septembre 2024.',
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
                'title' => 'Avis de Maintenance du Campus',
                'content' => 'Une maintenance programmée sera effectuée sur le campus principal ce week-end. Veuillez planifier en conséquence.',
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
                'title' => 'Mise à Jour du Planning du Laboratoire d\'Informatique',
                'content' => 'Le planning du laboratoire de programmation a été mis à jour. Veuillez vérifier vos emplois du temps.',
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

        $this->command->info('✅ Annonces créées avec succès');
    }

    /**
     * Create school calendar events
     */
    private function createSchoolCalendarEvents(): void
    {
        $this->command->info('📅 Création des événements du calendrier scolaire...');

        $school = School::first();

        $calendarEvents = [
            [
                'title' => 'Début de l\'Année Académique',
                'type' => 'academic_year_start',
                'start_date' => '2024-09-01',
                'end_date' => '2024-09-01',
                'description' => 'Premier jour de l\'année académique',
                'is_recurring' => false,
            ],
            [
                'title' => 'Vacances d\'Automne',
                'type' => 'holiday',
                'start_date' => '2024-11-25',
                'end_date' => '2024-11-29',
                'description' => 'Vacances du semestre d\'automne',
                'is_recurring' => false,
            ],
            [
                'title' => 'Vacances d\'Hiver',
                'type' => 'holiday',
                'start_date' => '2024-12-23',
                'end_date' => '2025-01-05',
                'description' => 'Vacances d\'hiver',
                'is_recurring' => false,
            ],
            [
                'title' => 'Vacances de Printemps',
                'type' => 'holiday',
                'start_date' => '2025-03-17',
                'end_date' => '2025-03-21',
                'description' => 'Vacances du semestre de printemps',
                'is_recurring' => false,
            ],
            [
                'title' => 'Semaine des Examens Finaux',
                'type' => 'exam_period',
                'start_date' => '2025-05-12',
                'end_date' => '2025-05-16',
                'description' => 'Examens finaux pour tous les cours',
                'is_recurring' => false,
            ],
        ];

        foreach ($calendarEvents as $eventData) {
            SchoolCalendar::create(array_merge($eventData, ['school_id' => $school->id]));
        }

        $this->command->info('✅ Événements du calendrier scolaire créés avec succès');
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

    /**
     * Create labs for various subjects
     */
    private function createLabsForSubjects(): void
    {
        $this->command->info('🔬 Création des laboratoires...');

        $labs = [
            // Computer Science Labs
            [
                'subject_code' => 'CS101',
                'name' => 'Laboratoire de Programmation 1',
                'description' => 'Laboratoire informatique pour exercices de programmation de base',
                'schedule' => 'Lundi 14h00 - 16h00',
            ],
            [
                'subject_code' => 'CS101',
                'name' => 'Laboratoire de Programmation 2',
                'description' => 'Laboratoire avancé pour projets de programmation',
                'schedule' => 'Mercredi 10h00 - 12h00',
            ],
            [
                'subject_code' => 'CS201',
                'name' => 'Laboratoire de Structures de Données',
                'description' => 'Laboratoire pour l\'implémentation de structures de données',
                'schedule' => 'Mardi 14h00 - 16h00',
            ],
            [
                'subject_code' => 'CS301',
                'name' => 'Laboratoire de Base de Données',
                'description' => 'Laboratoire pour la conception et manipulation de bases de données',
                'schedule' => 'Jeudi 10h00 - 12h00',
            ],
            [
                'subject_code' => 'CS301',
                'name' => 'Laboratoire de Base de Données Avancé',
                'description' => 'Laboratoire pour requêtes complexes et optimisation',
                'schedule' => 'Vendredi 14h00 - 16h00',
            ],
            // Mathematics Labs
            [
                'subject_code' => 'MATH101',
                'name' => 'Laboratoire de Calcul',
                'description' => 'Laboratoire pour exercices pratiques de calcul',
                'schedule' => 'Lundi 16h00 - 18h00',
            ],
            [
                'subject_code' => 'MATH201',
                'name' => 'Laboratoire d\'Algèbre Linéaire',
                'description' => 'Laboratoire pour exercices d\'algèbre linéaire',
                'schedule' => 'Mercredi 14h00 - 16h00',
            ],
            // Business Labs
            [
                'subject_code' => 'BUS101',
                'name' => 'Laboratoire de Gestion',
                'description' => 'Laboratoire pour simulations de gestion d\'entreprise',
                'schedule' => 'Mardi 10h00 - 12h00',
            ],
            [
                'subject_code' => 'BUS201',
                'name' => 'Laboratoire de Marketing',
                'description' => 'Laboratoire pour études de cas marketing',
                'schedule' => 'Jeudi 14h00 - 16h00',
            ],
            // English Labs
            [
                'subject_code' => 'ENG101',
                'name' => 'Laboratoire de Communication',
                'description' => 'Laboratoire pour exercices de communication orale et écrite',
                'schedule' => 'Vendredi 10h00 - 12h00',
            ],
        ];

        foreach ($labs as $labData) {
            $subject = Subject::where('code', $labData['subject_code'])->first();
            if ($subject) {
                Lab::create([
                    'subject_id' => $subject->id,
                    'name' => $labData['name'],
                    'description' => $labData['description'],
                    'schedule' => $labData['schedule'],
                ]);
            }
        }

        $this->command->info('✅ Laboratoires créés avec succès');
    }

    /**
     * Create prerequisite relationships between subjects
     */
    private function createPrerequisiteRelationships(): void
    {
        $this->command->info('🔗 Création des prérequis...');

        $prerequisites = [
            // Computer Science Prerequisites
            ['subject' => 'CS201', 'prerequisite' => 'CS101'],
            ['subject' => 'CS301', 'prerequisite' => 'CS201'],
            ['subject' => 'CS301', 'prerequisite' => 'MATH101'],
            ['subject' => 'CS401', 'prerequisite' => 'CS301'],
            ['subject' => 'CS401', 'prerequisite' => 'MATH201'],
            
            // Mathematics Prerequisites
            ['subject' => 'MATH201', 'prerequisite' => 'MATH101'],
            ['subject' => 'MATH301', 'prerequisite' => 'MATH201'],
            
            // Business Prerequisites
            ['subject' => 'BUS201', 'prerequisite' => 'BUS101'],
            ['subject' => 'BUS301', 'prerequisite' => 'BUS201'],
            ['subject' => 'BUS301', 'prerequisite' => 'MATH101'],
            
            // English Prerequisites
            ['subject' => 'ENG201', 'prerequisite' => 'ENG101'],
            ['subject' => 'ENG301', 'prerequisite' => 'ENG201'],
        ];

        foreach ($prerequisites as $prereq) {
            $subject = Subject::where('code', $prereq['subject'])->first();
            $prerequisite = Subject::where('code', $prereq['prerequisite'])->first();
            
            if ($subject && $prerequisite) {
                // Check if prerequisite relationship already exists
                $existing = \App\Models\AdminAcademics\SubjectPrerequisite::where('subject_id', $subject->id)
                    ->where('prerequisite_id', $prerequisite->id)
                    ->first();
                
                if (!$existing) {
                    \App\Models\AdminAcademics\SubjectPrerequisite::create([
                        'subject_id' => $subject->id,
                        'prerequisite_id' => $prerequisite->id,
                    ]);
                }
            }
        }

        $this->command->info('✅ Prérequis créés avec succès');
    }
} 