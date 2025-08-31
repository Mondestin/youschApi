<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Teachers\Teacher;
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
use App\Models\Teachers\{
    TeacherLeave,
    TeacherDocument,
    TeacherPerformance,
    TeacherTimetable
};

class TeachersManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Démarrage du Seeder de Gestion des Enseignants...');

        // Create schools and campuses first
        $this->createSchoolsAndCampuses();
        
        // Create academic structure
        $this->createAcademicStructure();
        
        // Create teachers
        $this->createTeachers();
        
        // Create academic years and terms
        $this->createAcademicYearsAndTerms();
        
        // Create classes and subjects
        $this->createClassesAndSubjects();
        
        // Create teacher-related data (commented out due to table issues)
        // $this->createTeacherLeaves();
        // $this->createTeacherDocuments();
        // $this->createTeacherPerformance();
        // $this->createTeacherTimetables();

        $this->command->info('✅ Seeder de Gestion des Enseignants terminé avec succès !');
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
                'head_id' => 1, // Will be updated after teachers are created
            ],
            [
                'faculty_id' => Faculty::where('name', 'Faculté des Sciences et Technologies')->first()->id,
                'name' => 'Mathématiques',
                'head_id' => 2, // Will be updated after teachers are created
            ],
            [
                'faculty_id' => Faculty::where('name', 'Faculté des Affaires et de l\'Économie')->first()->id,
                'name' => 'Administration des Affaires',
                'head_id' => 3, // Will be updated after teachers are created
            ],
            [
                'faculty_id' => Faculty::where('name', 'Faculté des Arts et Humanités')->first()->id,
                'name' => 'Littérature Anglaise',
                'head_id' => 4, // Will be updated after teachers are created
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
                'coordinator_id' => 1, // Will be updated after teachers are created
            ],
            [
                'course_id' => Course::where('code', 'BMATH')->first()->id,
                'name' => 'Calcul I',
                'code' => 'MATH101',
                'description' => 'Concepts fondamentaux du calcul',
                'coordinator_id' => 2, // Will be updated after teachers are created
            ],
            [
                'course_id' => Course::where('code', 'BBA')->first()->id,
                'name' => 'Principes de Gestion',
                'code' => 'BUS101',
                'description' => 'Principes de gestion de base',
                'coordinator_id' => 3, // Will be updated after teachers are created
            ],
            [
                'course_id' => Course::where('code', 'BAENG')->first()->id,
                'name' => 'Introduction à la Littérature',
                'code' => 'ENG101',
                'description' => 'Analyse et appréciation littéraire',
                'coordinator_id' => 4, // Will be updated after teachers are created
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
     * Create teachers
     */
    private function createTeachers(): void
    {
        $this->command->info('👨‍🏫 Création des enseignants...');

        $school = School::first();
        $mainCampus = Campus::where('name', 'Campus Principal')->first();
        $departments = Department::all();

        $teachers = [
            [
                'first_name' => 'Dr. Sarah',
                'last_name' => 'Johnson',
                'email' => 'sarah.johnson@yousch.edu',
                'phone' => '+1-555-0101',
                'address' => '123 Rue des Enseignants, Cité du Savoir',
                'dob' => '1985-03-15',
                'gender' => 'female',
                'hire_date' => '2020-09-01',
                'employment_type' => 'full-time',
                'qualification' => 'Ph.D. en Informatique',
                'specialization' => 'Intelligence Artificielle, Machine Learning',
                'department_id' => Department::where('name', 'Informatique')->first()->id,
                'faculty_id' => Faculty::where('name', 'Faculté des Sciences et Technologies')->first()->id,
                'school_id' => $school->id,
                'campus_id' => $mainCampus->id,
                'status' => 'active',
            ],
            [
                'first_name' => 'Prof. Michael',
                'last_name' => 'Chen',
                'email' => 'michael.chen@yousch.edu',
                'phone' => '+1-555-0102',
                'address' => '456 Avenue des Mathématiciens, District Nord',
                'dob' => '1980-07-22',
                'gender' => 'male',
                'hire_date' => '2018-08-15',
                'employment_type' => 'full-time',
                'qualification' => 'Ph.D. en Mathématiques',
                'specialization' => 'Algèbre, Topologie',
                'department_id' => Department::where('name', 'Mathématiques')->first()->id,
                'faculty_id' => Faculty::where('name', 'Faculté des Sciences et Technologies')->first()->id,
                'school_id' => $school->id,
                'campus_id' => $mainCampus->id,
                'status' => 'active',
            ],
            [
                'first_name' => 'Dr. Emily',
                'last_name' => 'Rodriguez',
                'email' => 'emily.rodriguez@yousch.edu',
                'phone' => '+1-555-0103',
                'address' => '789 Route des Gestionnaires, Quartier Est',
                'dob' => '1988-11-08',
                'gender' => 'female',
                'hire_date' => '2021-01-10',
                'employment_type' => 'full-time',
                'qualification' => 'Ph.D. en Administration des Affaires',
                'specialization' => 'Stratégie d\'Entreprise, Leadership',
                'department_id' => Department::where('name', 'Administration des Affaires')->first()->id,
                'faculty_id' => Faculty::where('name', 'Faculté des Affaires et de l\'Économie')->first()->id,
                'school_id' => $school->id,
                'campus_id' => $mainCampus->id,
                'status' => 'active',
            ],
            [
                'first_name' => 'Prof. David',
                'last_name' => 'Thompson',
                'email' => 'david.thompson@yousch.edu',
                'phone' => '+1-555-0104',
                'address' => '321 Boulevard des Littéraires, Zone Ouest',
                'dob' => '1975-04-30',
                'gender' => 'male',
                'hire_date' => '2015-06-01',
                'employment_type' => 'full-time',
                'qualification' => 'Ph.D. en Littérature Anglaise',
                'specialization' => 'Littérature Moderne, Critique Littéraire',
                'department_id' => Department::where('name', 'Littérature Anglaise')->first()->id,
                'faculty_id' => Faculty::where('name', 'Faculté des Arts et Humanités')->first()->id,
                'school_id' => $school->id,
                'campus_id' => $mainCampus->id,
                'status' => 'active',
            ],
            [
                'first_name' => 'Dr. Lisa',
                'last_name' => 'Wang',
                'email' => 'lisa.wang@yousch.edu',
                'phone' => '+1-555-0105',
                'address' => '654 Chemin des Chercheurs, Secteur Sud',
                'dob' => '1990-09-12',
                'gender' => 'female',
                'hire_date' => '2022-03-01',
                'employment_type' => 'contract',
                'qualification' => 'Ph.D. en Sciences Cognitives',
                'specialization' => 'Neurosciences, Psychologie Cognitive',
                'department_id' => Department::where('name', 'Informatique')->first()->id,
                'faculty_id' => Faculty::where('name', 'Faculté des Sciences et Technologies')->first()->id,
                'school_id' => $school->id,
                'campus_id' => $mainCampus->id,
                'status' => 'active',
            ],
        ];

        foreach ($teachers as $teacherData) {
            Teacher::create($teacherData);
        }

        // Update department heads with actual teacher IDs
        $this->updateDepartmentHeads();

        $this->command->info('✅ Enseignants créés avec succès');
    }

    /**
     * Update department heads with actual teacher IDs
     */
    private function updateDepartmentHeads(): void
    {
        $departments = [
            'Informatique' => 'sarah.johnson@yousch.edu',
            'Mathématiques' => 'michael.chen@yousch.edu',
            'Administration des Affaires' => 'emily.rodriguez@yousch.edu',
            'Littérature Anglaise' => 'david.thompson@yousch.edu',
        ];

        foreach ($departments as $departmentName => $teacherEmail) {
            $teacher = Teacher::where('email', $teacherEmail)->first();
            if ($teacher) {
                Department::where('name', $departmentName)->update(['head_id' => $teacher->id]);
            }
        }

        // Update subject coordinators
        $subjects = [
            'Introduction à la Programmation' => 'sarah.johnson@yousch.edu',
            'Calcul I' => 'michael.chen@yousch.edu',
            'Principes de Gestion' => 'emily.rodriguez@yousch.edu',
            'Introduction à la Littérature' => 'david.thompson@yousch.edu',
        ];

        foreach ($subjects as $subjectName => $teacherEmail) {
            $teacher = Teacher::where('email', $teacherEmail)->first();
            if ($teacher) {
                Subject::where('name', $subjectName)->update(['coordinator_id' => $teacher->id]);
            }
        }
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
     * Create teacher leaves (commented out due to table issues)
     */
    private function createTeacherLeaves(): void
    {
        $this->command->info('🏖️ Création des congés d\'enseignants...');

        $teachers = Teacher::all();
        $terms = Term::all();

        foreach ($teachers as $teacher) {
            $term = $terms->random();
            
            TeacherLeave::create([
                'teacher_id' => $teacher->id,
                'leave_type' => 'annual',
                'start_date' => now()->addDays(rand(30, 60)),
                'end_date' => now()->addDays(rand(31, 65)),
                'applied_on' => now(),
                'status' => 'approved',
                'approved_by' => 1, // Admin
                'approved_at' => now(),
                'notes' => 'Congé annuel approuvé',
            ]);
        }

        $this->command->info('✅ Congés d\'enseignants créés avec succès');
    }

    /**
     * Create teacher documents (commented out due to table issues)
     */
    private function createTeacherDocuments(): void
    {
        $this->command->info('📄 Création des documents d\'enseignants...');

        $teachers = Teacher::all();

        foreach ($teachers as $teacher) {
            TeacherDocument::create([
                'teacher_id' => $teacher->id,
                'document_type' => 'CV',
                'document_path' => 'teacher_documents/sample_cv.pdf',
                'uploaded_at' => now(),
            ]);
        }

        $this->command->info('✅ Documents d\'enseignants créés avec succès');
    }

    /**
     * Create teacher performance records (commented out due to table issues)
     */
    private function createTeacherPerformance(): void
    {
        $this->command->info('📊 Création des évaluations de performance des enseignants...');

        $teachers = Teacher::all();
        $terms = Term::all();

        foreach ($teachers as $teacher) {
            $term = $terms->random();
            
            TeacherPerformance::create([
                'teacher_id' => $teacher->id,
                'academic_year_id' => $term->academic_year_id,
                'term_id' => $term->id,
                'evaluated_by' => 1, // Admin
                'evaluation_date' => now(),
                'teaching_quality' => rand(4, 5),
                'classroom_management' => rand(4, 5),
                'communication_skills' => rand(4, 5),
                'subject_knowledge' => rand(4, 5),
                'overall_rating' => rand(4, 5),
                'strengths' => 'Excellente maîtrise du sujet, bonne communication avec les étudiants',
                'areas_for_improvement' => 'Peut améliorer l\'utilisation de la technologie en classe',
                'recommendations' => 'Continuer le développement professionnel',
                'is_confidential' => false,
            ]);
        }

        $this->command->info('✅ Évaluations de performance des enseignants créées avec succès');
    }

    /**
     * Create teacher timetables (commented out due to table issues)
     */
    private function createTeacherTimetables(): void
    {
        $this->command->info('⏰ Création des emplois du temps des enseignants...');

        $teachers = Teacher::all();
        $classes = ClassRoom::all();
        $subjects = Subject::all();
        $terms = Term::where('is_active', true)->get();

        foreach ($teachers as $teacher) {
            $class = $classes->random();
            $subject = $subjects->where('coordinator_id', $teacher->id)->first();
            $term = $terms->first();
            
            if ($subject) {
                TeacherTimetable::create([
                    'teacher_id' => $teacher->id,
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'date' => now()->addDays(rand(1, 30)),
                    'start_time' => '09:00:00',
                    'end_time' => '10:30:00',
                    'room' => 'Salle ' . rand(101, 120),
                    'lab_id' => null,
                ]);
            }
        }

        $this->command->info('✅ Emplois du temps des enseignants créés avec succès');
    }
} 