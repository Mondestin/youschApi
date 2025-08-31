<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedStudentAttendance();
        $this->seedTeacherAttendance();
        $this->seedStudentAttendanceExcuses();
        $this->seedTeacherAttendanceExcuses();
    }

    /**
     * Seed student attendance data.
     */
    private function seedStudentAttendance(): void
    {
        $students = DB::table('students')->pluck('id')->toArray();
        $classes = DB::table('classes')->pluck('id')->toArray();
        $subjects = DB::table('subjects')->pluck('id')->toArray();
        $timetables = DB::table('timetables')->pluck('id')->toArray();

        if (empty($students) || empty($classes) || empty($subjects) || empty($timetables)) {
            $this->command->warn('Skipping student attendance seeding - required data not found');
            return;
        }

        $attendanceData = [];
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }

            foreach ($classes as $classId) {
                $classStudents = array_slice($students, 0, rand(15, 25)); // Random number of students per class
                
                foreach ($classStudents as $studentId) {
                    $subjectId = $subjects[array_rand($subjects)];
                    $timetableId = $timetables[array_rand($timetables)];
                    
                    $status = $this->getRandomAttendanceStatus();
                    $remarks = $this->getRandomRemarks($status);

                    $attendanceData[] = [
                        'student_id' => $studentId,
                        'class_id' => $classId,
                        'subject_id' => $subjectId,
                        'lab_id' => null, // No lab for now
                        'timetable_id' => $timetableId,
                        'date' => $date->format('Y-m-d'),
                        'status' => $status,
                        'remarks' => $remarks,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // Insert in chunks to avoid memory issues
        foreach (array_chunk($attendanceData, 1000) as $chunk) {
            DB::table('student_attendance')->insert($chunk);
        }

        $this->command->info('Student attendance data seeded successfully');
    }

    /**
     * Seed teacher attendance data.
     */
    private function seedTeacherAttendance(): void
    {
        $teachers = DB::table('teachers')->pluck('id')->toArray();
        $classes = DB::table('classes')->pluck('id')->toArray();
        $subjects = DB::table('subjects')->pluck('id')->toArray();
        $timetables = DB::table('timetables')->pluck('id')->toArray();

        if (empty($teachers) || empty($classes) || empty($subjects) || empty($timetables)) {
            $this->command->warn('Skipping teacher attendance seeding - required data not found');
            return;
        }

        $attendanceData = [];
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }

            foreach ($classes as $classId) {
                $classTeachers = array_slice($teachers, 0, rand(3, 8)); // Random number of teachers per class
                
                foreach ($classTeachers as $teacherId) {
                    $subjectId = $subjects[array_rand($subjects)];
                    $timetableId = $timetables[array_rand($timetables)];
                    
                    $status = $this->getRandomTeacherAttendanceStatus();
                    $remarks = $this->getRandomTeacherRemarks($status);

                    $attendanceData[] = [
                        'teacher_id' => $teacherId,
                        'class_id' => $classId,
                        'subject_id' => $subjectId,
                        'lab_id' => null, // No lab for now
                        'timetable_id' => $timetableId,
                        'date' => $date->format('Y-m-d'),
                        'status' => $status,
                        'remarks' => $remarks,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // Insert in chunks to avoid memory issues
        foreach (array_chunk($attendanceData, 1000) as $chunk) {
            DB::table('teacher_attendance')->insert($chunk);
        }

        $this->command->info('Teacher attendance data seeded successfully');
    }

    /**
     * Seed student attendance excuses.
     */
    private function seedStudentAttendanceExcuses(): void
    {
        $students = DB::table('students')->pluck('id')->toArray();
        $classes = DB::table('classes')->pluck('id')->toArray();
        $subjects = DB::table('subjects')->pluck('id')->toArray();
        $users = DB::table('users')->pluck('id')->toArray();

        if (empty($students) || empty($classes) || empty($subjects) || empty($users)) {
            $this->command->warn('Skipping student excuses seeding - required data not found');
            return;
        }

        $excuseData = [];
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }

            // Generate 1-5 excuses per day
            $numExcuses = rand(1, 5);
            
            for ($i = 0; $i < $numExcuses; $i++) {
                $studentId = $students[array_rand($students)];
                $classId = $classes[array_rand($classes)];
                $subjectId = $subjects[array_rand($subjects)];
                $status = $this->getRandomExcuseStatus();
                
                $excuseData[] = [
                    'student_id' => $studentId,
                    'class_id' => $classId,
                    'subject_id' => $subjectId,
                    'lab_id' => null,
                    'date' => $date->format('Y-m-d'),
                    'reason' => $this->getRandomExcuseReason(),
                    'document_path' => null, // No documents for now
                    'status' => $status,
                    'reviewed_by' => $status !== 'pending' ? $users[array_rand($users)] : null,
                    'reviewed_on' => $status !== 'pending' ? now() : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insert in chunks to avoid memory issues
        foreach (array_chunk($excuseData, 500) as $chunk) {
            DB::table('student_attendance_excuses')->insert($chunk);
        }

        $this->command->info('Student attendance excuses seeded successfully');
    }

    /**
     * Seed teacher attendance excuses.
     */
    private function seedTeacherAttendanceExcuses(): void
    {
        $teachers = DB::table('teachers')->pluck('id')->toArray();
        $classes = DB::table('classes')->pluck('id')->toArray();
        $subjects = DB::table('subjects')->pluck('id')->toArray();
        $users = DB::table('users')->pluck('id')->toArray();

        if (empty($teachers) || empty($classes) || empty($subjects) || empty($users)) {
            $this->command->warn('Skipping teacher excuses seeding - required data not found');
            return;
        }

        $excuseData = [];
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }

            // Generate 0-3 excuses per day (teachers have fewer excuses)
            $numExcuses = rand(0, 3);
            
            for ($i = 0; $i < $numExcuses; $i++) {
                $teacherId = $teachers[array_rand($teachers)];
                $classId = $classes[array_rand($classes)];
                $subjectId = $subjects[array_rand($subjects)];
                $status = $this->getRandomExcuseStatus();
                
                $excuseData[] = [
                    'teacher_id' => $teacherId,
                    'class_id' => $classId,
                    'subject_id' => $subjectId,
                    'lab_id' => null,
                    'date' => $date->format('Y-m-d'),
                    'reason' => $this->getRandomTeacherExcuseReason(),
                    'document_path' => null, // No documents for now
                    'status' => $status,
                    'reviewed_by' => $status !== 'pending' ? $users[array_rand($users)] : null,
                    'reviewed_on' => $status !== 'pending' ? now() : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insert in chunks to avoid memory issues
        foreach (array_chunk($excuseData, 500) as $chunk) {
            DB::table('teacher_attendance_excuses')->insert($chunk);
        }

        $this->command->info('Teacher attendance excuses seeded successfully');
    }

    /**
     * Get random attendance status for students.
     */
    private function getRandomAttendanceStatus(): string
    {
        $statuses = ['present', 'absent', 'late', 'excused'];
        $weights = [70, 15, 10, 5]; // 70% present, 15% absent, 10% late, 5% excused
        
        return $this->getWeightedRandom($statuses, $weights);
    }

    /**
     * Get random attendance status for teachers.
     */
    private function getRandomTeacherAttendanceStatus(): string
    {
        $statuses = ['present', 'absent', 'late'];
        $weights = [85, 10, 5]; // 85% present, 10% absent, 5% late
        
        return $this->getWeightedRandom($statuses, $weights);
    }

    /**
     * Get random excuse status.
     */
    private function getRandomExcuseStatus(): string
    {
        $statuses = ['pending', 'approved', 'rejected'];
        $weights = [30, 60, 10]; // 30% pending, 60% approved, 10% rejected
        
        return $this->getWeightedRandom($statuses, $weights);
    }

    /**
     * Get random remarks based on status.
     */
    private function getRandomRemarks(string $status): ?string
    {
        $remarks = [
            'present' => [
                'On time',
                'Present and ready',
                'Participated actively',
                null
            ],
            'absent' => [
                'No show',
                'Not in class',
                'Absent without notice',
                null
            ],
            'late' => [
                'Arrived 10 minutes late',
                'Traffic delay',
                'Late arrival',
                null
            ],
            'excused' => [
                'Medical appointment',
                'Family emergency',
                'School activity',
                null
            ]
        ];

        $statusRemarks = $remarks[$status] ?? [null];
        return $statusRemarks[array_rand($statusRemarks)];
    }

    /**
     * Get random teacher remarks based on status.
     */
    private function getRandomTeacherRemarks(string $status): ?string
    {
        $remarks = [
            'present' => [
                'On time',
                'Present and ready',
                'Class conducted normally',
                null
            ],
            'absent' => [
                'Substitute teacher assigned',
                'Class cancelled',
                'Not available',
                null
            ],
            'late' => [
                'Arrived 5 minutes late',
                'Technical issues',
                'Late arrival',
                null
            ]
        ];

        $statusRemarks = $remarks[$status] ?? [null];
        return $statusRemarks[array_rand($statusRemarks)];
    }

    /**
     * Get random excuse reason for students.
     */
    private function getRandomExcuseReason(): string
    {
        $reasons = [
            'Medical appointment',
            'Family emergency',
            'Personal illness',
            'Dental appointment',
            'Family travel',
            'Religious observance',
            'School activity',
            'Sports competition',
            'Music lesson',
            'Therapy session'
        ];

        return $reasons[array_rand($reasons)];
    }

    /**
     * Get random excuse reason for teachers.
     */
    private function getRandomTeacherExcuseReason(): string
    {
        $reasons = [
            'Professional development',
            'Medical appointment',
            'Family emergency',
            'Conference attendance',
            'Training session',
            'Personal illness',
            'Administrative meeting',
            'Curriculum planning',
            'Assessment marking',
            'Parent consultation'
        ];

        return $reasons[array_rand($reasons)];
    }

    /**
     * Get weighted random selection.
     */
    private function getWeightedRandom(array $items, array $weights): mixed
    {
        $totalWeight = array_sum($weights);
        $random = mt_rand(1, $totalWeight);
        
        $currentWeight = 0;
        foreach ($items as $index => $item) {
            $currentWeight += $weights[$index];
            if ($random <= $currentWeight) {
                return $item;
            }
        }
        
        return $items[0]; // Fallback
    }
} 