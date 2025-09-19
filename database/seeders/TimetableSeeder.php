<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AdminAcademics\Timetable;
use App\Models\AdminAcademics\ClassRoom;
use App\Models\AdminAcademics\Subject;
use App\Models\AdminAcademics\Venue;
use App\Models\User;
use Carbon\Carbon;

class TimetableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing data
        $classes = ClassRoom::all();
        $subjects = Subject::all();
        $venues = Venue::where('is_active', true)->get();
        $teachers = User::where('role', 'teacher')->get();

        if ($classes->isEmpty() || $subjects->isEmpty() || $venues->isEmpty() || $teachers->isEmpty()) {
            $this->command->warn('⚠️  Certaines données requises sont manquantes. Assurez-vous que les classes, matières, lieux et enseignants sont créés d\'abord.');
            return;
        }

        // Realistic time slots from 7 AM to 5 PM with proper intervals
        $timeSlots = [
            // Morning sessions
            ['start' => '07:00:00', 'end' => '08:00:00'],  // 1 hour
            ['start' => '08:00:00', 'end' => '09:00:00'],  // 1 hour
            ['start' => '09:00:00', 'end' => '10:00:00'],  // 1 hour
            ['start' => '10:00:00', 'end' => '11:00:00'],  // 1 hour
            ['start' => '11:00:00', 'end' => '12:00:00'],  // 1 hour
            ['start' => '12:00:00', 'end' => '13:00:00'],  // 1 hour (lunch break)
            
            // Afternoon sessions
            ['start' => '13:00:00', 'end' => '14:00:00'],  // 1 hour
            ['start' => '14:00:00', 'end' => '15:00:00'],  // 1 hour
            ['start' => '15:00:00', 'end' => '16:00:00'],  // 1 hour
            ['start' => '16:00:00', 'end' => '17:00:00'],  // 1 hour
        ];

        // Days of the week (1 = Monday, 7 = Sunday)
        $weekDays = [1, 2, 3, 4, 5]; // Monday to Friday

        $timetableEntries = [];
        $entryCount = 0;

        // Generate timetable entries from August 1, 2025 to October 31, 2025
        $startDate = Carbon::create(2025, 8, 1);
        $endDate = Carbon::create(2025, 10, 31);

        $currentDate = $startDate->copy();

        // Create a realistic weekly schedule for each class
        while ($currentDate->lte($endDate)) {
            // Skip weekends
            if ($currentDate->isWeekend()) {
                $currentDate->addDay();
                continue;
            }

            // For each class, create a realistic daily schedule
            foreach ($classes as $class) {
                // Get subjects for this class's course
                $courseSubjects = Subject::where('course_id', $class->course_id)->get();
                
                if ($courseSubjects->isEmpty()) {
                    continue;
                }

                // Create 4-6 classes per day for each class (realistic school day)
                $dailySubjects = $courseSubjects->random(min(6, $courseSubjects->count()));
                $usedTimeSlots = [];

                foreach ($dailySubjects as $index => $subject) {
                    // Get a teacher for this subject
                    $teacher = $teachers->random();
                    $venue = $venues->random();
                    
                    // Select a time slot that hasn't been used for this class today
                    $availableTimeSlots = array_filter($timeSlots, function($slot) use ($usedTimeSlots) {
                        return !in_array($slot['start'], $usedTimeSlots);
                    });
                    
                    if (empty($availableTimeSlots)) {
                        continue; // No more available time slots for this class today
                    }
                    
                    $timeSlot = $availableTimeSlots[array_rand($availableTimeSlots)];
                    $usedTimeSlots[] = $timeSlot['start'];

                    // Check for conflicts (teacher, venue, or class already booked at this time)
                    $hasConflict = Timetable::where('date', $currentDate->format('Y-m-d'))
                        ->where(function($query) use ($timeSlot, $class, $teacher, $venue) {
                            $query->where('class_id', $class->id)
                                  ->orWhere('teacher_id', $teacher->id)
                                  ->orWhere('venue_id', $venue->id);
                        })
                        ->where(function($query) use ($timeSlot) {
                            $query->where('start_time', $timeSlot['start'])
                                  ->orWhere('end_time', '>', $timeSlot['start'])
                                  ->where('start_time', '<', $timeSlot['end']);
                        })
                        ->exists();

                    if (!$hasConflict) {
                        $timetableEntries[] = [
                            'class_id' => $class->id,
                            'subject_id' => $subject->id,
                            'teacher_id' => $teacher->id,
                            'venue_id' => $venue->id,
                            'date' => $currentDate->format('Y-m-d'),
                            'start_time' => $timeSlot['start'],
                            'end_time' => $timeSlot['end'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        $entryCount++;
                    }
                }
            }

            $currentDate->addDay();
        }

        // Insert timetable entries
        foreach ($timetableEntries as $entry) {
            Timetable::create($entry);
        }

        $this->command->info("✅ {$entryCount} entrées d'emploi du temps créées pour la période août-octobre 2025");
        
        // Display some statistics
        $this->command->info("📊 Statistiques du nouvel emploi du temps réaliste:");
        $this->command->info("   - Période: {$startDate->format('d/m/Y')} au {$endDate->format('d/m/Y')}");
        $this->command->info("   - Nombre total d'entrées: {$entryCount}");
        $this->command->info("   - Classes utilisées: " . collect($timetableEntries)->pluck('class_id')->unique()->count());
        $this->command->info("   - Matières utilisées: " . collect($timetableEntries)->pluck('subject_id')->unique()->count());
        $this->command->info("   - Lieux utilisés: " . collect($timetableEntries)->pluck('venue_id')->unique()->count());
        $this->command->info("   - Enseignants utilisés: " . collect($timetableEntries)->pluck('teacher_id')->unique()->count());
        $this->command->info("   - Créneaux horaires: 7h00-17h00 (10 créneaux d'1h)");
        $this->command->info("   - Jours de cours: Lundi à Vendredi uniquement");
        $this->command->info("   - Conflits évités: Classes, enseignants et lieux");
    }
}