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

        // Time slots for different periods
        $timeSlots = [
            ['start' => '08:00:00', 'end' => '09:30:00'],
            ['start' => '09:45:00', 'end' => '11:15:00'],
            ['start' => '11:30:00', 'end' => '13:00:00'],
            ['start' => '14:00:00', 'end' => '15:30:00'],
            ['start' => '15:45:00', 'end' => '17:15:00'],
            ['start' => '17:30:00', 'end' => '19:00:00'],
        ];

        // Days of the week (1 = Monday, 7 = Sunday)
        $weekDays = [1, 2, 3, 4, 5]; // Monday to Friday

        $timetableEntries = [];
        $entryCount = 0;
        $maxEntries = 50;

        // Generate timetable entries from August 1, 2025 to October 31, 2025
        $startDate = Carbon::create(2025, 8, 1);
        $endDate = Carbon::create(2025, 10, 31);

        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate) && $entryCount < $maxEntries) {
            // Skip weekends
            if ($currentDate->isWeekend()) {
                $currentDate->addDay();
                continue;
            }

            // Generate 1-3 entries per day
            $entriesPerDay = rand(1, 3);
            
            for ($i = 0; $i < $entriesPerDay && $entryCount < $maxEntries; $i++) {
                $class = $classes->random();
                $subject = $subjects->random();
                $teacher = $teachers->random();
                $venue = $venues->random();
                $timeSlot = $timeSlots[array_rand($timeSlots)];

                // Check for conflicts (simple check)
                $hasConflict = Timetable::where('class_id', $class->id)
                    ->where('date', $currentDate->format('Y-m-d'))
                    ->where('start_time', $timeSlot['start'])
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

            $currentDate->addDay();
        }

        // Insert timetable entries
        foreach ($timetableEntries as $entry) {
            Timetable::create($entry);
        }

        $this->command->info("✅ {$entryCount} entrées d'emploi du temps créées pour la période août-octobre 2025");
        
        // Display some statistics
        $this->command->info("📊 Statistiques:");
        $this->command->info("   - Période: {$startDate->format('d/m/Y')} au {$endDate->format('d/m/Y')}");
        $this->command->info("   - Nombre d'entrées: {$entryCount}");
        $this->command->info("   - Classes utilisées: " . collect($timetableEntries)->pluck('class_id')->unique()->count());
        $this->command->info("   - Matières utilisées: " . collect($timetableEntries)->pluck('subject_id')->unique()->count());
        $this->command->info("   - Lieux utilisés: " . collect($timetableEntries)->pluck('venue_id')->unique()->count());
        $this->command->info("   - Enseignants utilisés: " . collect($timetableEntries)->pluck('teacher_id')->unique()->count());
    }
}