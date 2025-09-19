<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Students\Student;
use App\Models\AdminAcademics\{
    School,
    Campus,
    Faculty,
    Department,
    Course,
    Subject,
    ClassRoom,
    AcademicYear,
    Term
};

class AttendanceManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Démarrage du Seeder de Gestion des Présences...');

        // Create schools and campuses first
        $this->createSchoolsAndCampuses();
        
        // Create academic structure
        $this->createAcademicStructure();
        
        // Create academic years and terms
        $this->createAcademicYearsAndTerms();
        
        // Create classes and subjects
        $this->createClassesAndSubjects();
        
        // Create timetables
        $this->createTimetables();
        
        // Create student attendance records
        $this->createStudentAttendance();
        
        // Create teacher attendance records
        $this->createTeacherAttendance();

        $this->command->info('✅ Seeder de Gestion des Présences terminé avec succès !');
    }

    /**
     * Create schools and campuses
     */
    private function createSchoolsAndCampuses(): void
    {
        $this->command->info('🏫 Création des écoles et campus...');

        // Create main school
        $school = School::firstOrCreate([
            'name' => 'École Internationale Yousch',
        ], [
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
        ];

        foreach ($campuses as $campusData) {
            Campus::firstOrCreate([
                'name' => $campusData['name'],
            ], array_merge($campusData, ['school_id' => $school->id]));
        }

        $this->command->info('✅ Écoles et campus créés avec succès');
    }

    /**
     * Create academic structure
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
            Faculty::firstOrCreate([
                'name' => $facultyData['name'],
            ], array_merge($facultyData, ['school_id' => $school->id]));
        }

        // Create departments
        $departments = [
            [
                'faculty_id' => Faculty::where('name', 'Faculté des Sciences et Technologies')->first()->id,
                'name' => 'Informatique',
                'head_id' => null, // Will be set later
            ],
            [
                'faculty_id' => Faculty::where('name', 'Faculté des Sciences et Technologies')->first()->id,
                'name' => 'Mathématiques',
                'head_id' => null, // Will be set later
            ],
            [
                'faculty_id' => Faculty::where('name', 'Faculté des Affaires et de l\'Économie')->first()->id,
                'name' => 'Administration des Affaires',
                'head_id' => null, // Will be set later
            ],
            [
                'faculty_id' => Faculty::where('name', 'Faculté des Arts et Humanités')->first()->id,
                'name' => 'Littérature Anglaise',
                'head_id' => null, // Will be set later
            ],
        ];

        foreach ($departments as $departmentData) {
            Department::firstOrCreate([
                'name' => $departmentData['name'],
            ], $departmentData);
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
            Course::firstOrCreate([
                'name' => $courseData['name'],
            ], $courseData);
        }

        // Create subjects
        $subjects = [
            [
                'course_id' => Course::where('code', 'BCS')->first()->id,
                'name' => 'Introduction à la Programmation',
                'code' => 'CS101',
                'description' => 'Concepts et pratiques de programmation de base',
                'coordinator_id' => null, // Will be set later
            ],
            [
                'course_id' => Course::where('code', 'BMATH')->first()->id,
                'name' => 'Calcul I',
                'code' => 'MATH101',
                'description' => 'Concepts fondamentaux du calcul',
                'coordinator_id' => null, // Will be set later
            ],
            [
                'course_id' => Course::where('code', 'BBA')->first()->id,
                'name' => 'Principes de Gestion',
                'code' => 'BUS101',
                'description' => 'Principes de gestion de base',
                'coordinator_id' => null, // Will be set later
            ],
            [
                'course_id' => Course::where('code', 'BAENG')->first()->id,
                'name' => 'Introduction à la Littérature',
                'code' => 'ENG101',
                'description' => 'Analyse et appréciation littéraire',
                'coordinator_id' => null, // Will be set later
            ],
        ];

        foreach ($subjects as $subjectData) {
            Subject::firstOrCreate([
                'name' => $subjectData['name'],
            ], $subjectData);
        }

        $this->command->info('✅ Structure académique créée avec succès');
    }

    /**
     * Create academic years and terms
     */
    private function createAcademicYearsAndTerms(): void
    {
        $this->command->info('📅 Création des années académiques et trimestres...');

        $school = School::first();

        // Create current academic year
        $currentYear = AcademicYear::firstOrCreate([
            'name' => '2024-2025',
        ], [
            'school_id' => $school->id,
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
            Term::firstOrCreate([
                'name' => $termData['name'],
            ], array_merge($termData, ['academic_year_id' => $currentYear->id]));
        }

        $this->command->info('✅ Années académiques et trimestres créés avec succès');
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
            ClassRoom::firstOrCreate([
                'name' => $course->code . ' - Classe A',
            ], [
                'campus_id' => $mainCampus->id,
                'course_id' => $course->id,
                'capacity' => 30,
            ]);

            ClassRoom::firstOrCreate([
                'name' => $course->code . ' - Classe B',
            ], [
                'campus_id' => $mainCampus->id,
                'course_id' => $course->id,
                'capacity' => 25,
            ]);
        }

        $this->command->info('✅ Classes et affectations de matières créées avec succès');
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
                    
                    DB::table('timetables')->insert([
                        'class_id' => $class->id,
                        'subject_id' => $subject->id,
                        'teacher_id' => $teacher->id,
                        'date' => $date->format('Y-m-d'),
                        'start_time' => '09:00:00',
                        'end_time' => '10:30:00',
                        'venue_id' => $venue ? $venue->id : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    DB::table('timetables')->insert([
                        'class_id' => $class->id,
                        'subject_id' => $subject->id,
                        'teacher_id' => $teacher->id,
                        'date' => $date->format('Y-m-d'),
                        'start_time' => '14:00:00',
                        'end_time' => '15:30:00',
                        'venue_id' => $venue ? $venue->id : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('✅ Emplois du temps créés avec succès');
    }

    /**
     * Create student attendance records
     */
    private function createStudentAttendance(): void
    {
        $this->command->info('📚 Création des enregistrements de présence des étudiants...');

        $students = Student::where('status', 'active')->get();
        $classes = ClassRoom::all();
        $subjects = Subject::all();
        $timetables = DB::table('timetables')->get();

        if ($students->isEmpty()) {
            $this->command->warn('No active students found. Skipping student attendance creation.');
            return;
        }

        foreach ($students as $student) {
            $class = $classes->random();
            $classSubjects = Subject::where('course_id', $class->course_id)->get();
            
            foreach ($classSubjects as $subject) {
                $timetable = $timetables->where('class_id', $class->id)
                                       ->where('subject_id', $subject->id)
                                       ->first();
                
                if ($timetable) {
                    // Create attendance for the past week
                    for ($day = 0; $day < 5; $day++) {
                        $date = now()->subDays($day);
                        $status = $this->getRandomAttendanceStatus();
                        
                        DB::table('student_attendance')->insert([
                            'student_id' => $student->id,
                            'class_id' => $class->id,
                            'subject_id' => $subject->id,
                            'lab_id' => null,
                            'timetable_id' => $timetable->id,
                            'date' => $date->format('Y-m-d'),
                            'status' => $status,
                            'remarks' => $this->getAttendanceRemarks($status),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        $this->command->info('✅ Enregistrements de présence des étudiants créés avec succès');
    }

    /**
     * Create teacher attendance records
     */
    private function createTeacherAttendance(): void
    {
        $this->command->info('👨‍🏫 Création des enregistrements de présence des enseignants...');

        $teachers = User::whereIn('email', [
            'sarah.johnson@yousch.edu',
            'michael.chen@yousch.edu',
            'emily.rodriguez@yousch.edu',
            'david.thompson@yousch.edu'
        ])->get();
        
        $timetables = DB::table('timetables')->get();

        foreach ($teachers as $teacher) {
            $teacherTimetables = $timetables->where('teacher_id', $teacher->id);
            
            foreach ($teacherTimetables as $timetable) {
                // Create attendance for the past week
                for ($day = 0; $day < 5; $day++) {
                    $date = now()->subDays($day);
                    $status = $this->getRandomAttendanceStatus();
                    
                    DB::table('teacher_attendance')->insert([
                        'teacher_id' => $teacher->id,
                        'class_id' => $timetable->class_id,
                        'subject_id' => $timetable->subject_id,
                        'lab_id' => null,
                        'timetable_id' => $timetable->id,
                        'date' => $date->format('Y-m-d'),
                        'status' => $status,
                        'remarks' => $this->getAttendanceRemarks($status),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('✅ Enregistrements de présence des enseignants créés avec succès');
    }

    /**
     * Get random attendance status
     */
    private function getRandomAttendanceStatus(): string
    {
        $statuses = ['present', 'absent', 'late'];
        $weights = [75, 15, 10]; // 75% present, 15% absent, 10% late
        
        $random = rand(1, 100);
        $cumulative = 0;
        
        for ($i = 0; $i < count($statuses); $i++) {
            $cumulative += $weights[$i];
            if ($random <= $cumulative) {
                return $statuses[$i];
            }
        }
        
        return 'present';
    }

    /**
     * Get attendance remarks based on status
     */
    private function getAttendanceRemarks(string $status): string
    {
        $remarks = [
            'present' => 'Présent et ponctuel',
            'absent' => 'Absence non justifiée',
            'late' => 'Arrivé en retard',
        ];
        
        return $remarks[$status] ?? 'Aucune remarque';
    }
} 