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
        // Get teachers using the new role system, with fallback to email-based approach
        $teacherRole = \App\Models\Role::where('slug', 'teacher')->first();
        $teachers = collect();
        
        if ($teacherRole) {
            $teachers = $teacherRole->users;
        }
        
        // Fallback to email-based approach if no teachers found via roles
        if ($teachers->isEmpty()) {
            $this->command->warn('⚠️  No teachers found via role system, trying email-based approach...');
            $teachers = User::whereIn('email', [
                'sarah.johnson@yousch.edu',
                'michael.chen@yousch.edu',
                'emily.rodriguez@yousch.edu',
                'david.thompson@yousch.edu',
                'lisa.wang@yousch.edu'
            ])->get();
        }

        if ($classes->isEmpty() || $subjects->isEmpty() || $venues->isEmpty() || $teachers->isEmpty()) {
            $this->command->warn('⚠️  Certaines données requises sont manquantes:');
            $this->command->warn('   - Classes: ' . ($classes->isEmpty() ? '❌ Manquantes' : '✅ ' . $classes->count()));
            $this->command->warn('   - Matières: ' . ($subjects->isEmpty() ? '❌ Manquantes' : '✅ ' . $subjects->count()));
            $this->command->warn('   - Lieux: ' . ($venues->isEmpty() ? '❌ Manquants' : '✅ ' . $venues->count()));
            $this->command->warn('   - Enseignants: ' . ($teachers->isEmpty() ? '❌ Manquants' : '✅ ' . $teachers->count()));
            $this->command->warn('   Assurez-vous que les données requises sont créées d\'abord.');
            return;
        }

        // Realistic time slots with reduced early morning and lunch time slots
        $timeSlots = [
            // Early morning - reduced slots (7AM-8:30AM)
            ['start' => '07:30:00', 'end' => '08:30:00', 'weight' => 0.3],  // Reduced weight for early morning
            
            // Regular morning sessions (8:30AM-12:00PM)
            ['start' => '08:30:00', 'end' => '09:30:00', 'weight' => 1.0],  // Normal weight
            ['start' => '09:30:00', 'end' => '10:30:00', 'weight' => 1.0],  // Normal weight
            ['start' => '10:30:00', 'end' => '11:30:00', 'weight' => 1.0],  // Normal weight
            ['start' => '11:30:00', 'end' => '12:30:00', 'weight' => 1.0],  // Normal weight
            
            // Lunch break - reduced slots (12:30PM-1:30PM)
            ['start' => '12:30:00', 'end' => '13:30:00', 'weight' => 0.2],  // Reduced weight for lunch time
            
            // Afternoon sessions (1:30PM-5:00PM)
            ['start' => '13:30:00', 'end' => '14:30:00', 'weight' => 1.0],  // Normal weight
            ['start' => '14:30:00', 'end' => '15:30:00', 'weight' => 1.0],  // Normal weight
            ['start' => '15:30:00', 'end' => '16:30:00', 'weight' => 1.0],  // Normal weight
            ['start' => '16:30:00', 'end' => '17:30:00', 'weight' => 0.8],  // Slightly reduced for late afternoon
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
                    
                    // Use weighted selection for time slots
                    $timeSlot = $this->selectWeightedTimeSlot($availableTimeSlots);
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
        $this->command->info("   - Créneaux horaires: 7h30-17h30 (9 créneaux avec distribution réaliste)");
        $this->command->info("   - Périodes réduites: 7h30-8h30 (30%) et 12h30-13h30 (20%)");
        $this->command->info("   - Jours de cours: Lundi à Vendredi uniquement");
        $this->command->info("   - Conflits évités: Classes, enseignants et lieux");
    }

    /**
     * Select a time slot using weighted random selection
     */
    private function selectWeightedTimeSlot(array $availableTimeSlots): array
    {
        $weights = [];
        $slots = [];
        
        foreach ($availableTimeSlots as $slot) {
            $weight = $slot['weight'] ?? 1.0;
            $weights[] = $weight;
            $slots[] = $slot;
        }
        
        // Calculate total weight
        $totalWeight = array_sum($weights);
        
        // Generate random number
        $random = mt_rand() / mt_getrandmax() * $totalWeight;
        
        // Find the selected slot
        $currentWeight = 0;
        for ($i = 0; $i < count($slots); $i++) {
            $currentWeight += $weights[$i];
            if ($random <= $currentWeight) {
                return $slots[$i];
            }
        }
        
        // Fallback to last slot if something goes wrong
        return end($slots);
    }
}