<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminAcademics\SubjectPrerequisite;
use App\Models\AdminAcademics\Subject;

class PrerequisiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔗 Seeding Prerequisites...');

        $prerequisites = [
            // Computer Science Prerequisites
            ['subject' => 'CS201', 'prerequisite' => 'CS101'],
            ['subject' => 'CS301', 'prerequisite' => 'CS201'],
            ['subject' => 'CS301', 'prerequisite' => 'MATH101'],
            ['subject' => 'CS401', 'prerequisite' => 'CS301'],
            ['subject' => 'CS401', 'prerequisite' => 'MATH201'],
            ['subject' => 'CS501', 'prerequisite' => 'CS401'],
            ['subject' => 'CS501', 'prerequisite' => 'MATH301'],
            
            // Mathematics Prerequisites
            ['subject' => 'MATH201', 'prerequisite' => 'MATH101'],
            ['subject' => 'MATH301', 'prerequisite' => 'MATH201'],
            ['subject' => 'MATH401', 'prerequisite' => 'MATH301'],
            
            // Business Prerequisites
            ['subject' => 'BUS201', 'prerequisite' => 'BUS101'],
            ['subject' => 'BUS301', 'prerequisite' => 'BUS201'],
            ['subject' => 'BUS301', 'prerequisite' => 'MATH101'],
            ['subject' => 'BUS401', 'prerequisite' => 'BUS301'],
            ['subject' => 'BUS401', 'prerequisite' => 'MATH201'],
            
            // English Prerequisites
            ['subject' => 'ENG201', 'prerequisite' => 'ENG101'],
            ['subject' => 'ENG301', 'prerequisite' => 'ENG201'],
            ['subject' => 'ENG401', 'prerequisite' => 'ENG301'],
            
            // Cross-disciplinary Prerequisites
            ['subject' => 'CS201', 'prerequisite' => 'MATH101'],
            ['subject' => 'BUS201', 'prerequisite' => 'ENG101'],
            ['subject' => 'CS401', 'prerequisite' => 'ENG201'],
        ];

        $createdCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        foreach ($prerequisites as $prereq) {
            $subject = Subject::where('code', $prereq['subject'])->first();
            $prerequisite = Subject::where('code', $prereq['prerequisite'])->first();
            
            if ($subject && $prerequisite) {
                // Check if prerequisite relationship already exists
                $existing = SubjectPrerequisite::where('subject_id', $subject->id)
                    ->where('prerequisite_id', $prerequisite->id)
                    ->first();
                
                if (!$existing) {
                    // Check for circular dependency
                    if ($this->wouldCreateCircularDependency($subject->id, $prerequisite->id)) {
                        $this->command->warn("Circular dependency detected: {$prereq['subject']} -> {$prereq['prerequisite']}. Skipping.");
                        $errorCount++;
                        continue;
                    }
                    
                    SubjectPrerequisite::create([
                        'subject_id' => $subject->id,
                        'prerequisite_id' => $prerequisite->id,
                    ]);
                    $createdCount++;
                } else {
                    $skippedCount++;
                }
            } else {
                $missingSubject = !$subject ? $prereq['subject'] : $prereq['prerequisite'];
                $this->command->warn("Subject '{$missingSubject}' not found. Skipping prerequisite relationship.");
                $errorCount++;
            }
        }

        $this->command->info("✅ Prerequisite seeding completed: {$createdCount} created, {$skippedCount} skipped, {$errorCount} errors");
    }

    /**
     * Check if creating a prerequisite relationship would create a circular dependency
     */
    private function wouldCreateCircularDependency(int $subjectId, int $prerequisiteId): bool
    {
        // If we're trying to make A require B as prerequisite,
        // check if B already requires A (directly or indirectly)
        return $this->hasIndirectPrerequisite($prerequisiteId, $subjectId);
    }

    /**
     * Check if subject A has subject B as an indirect prerequisite
     */
    private function hasIndirectPrerequisite(int $subjectId, int $targetPrerequisiteId): bool
    {
        // Get all direct prerequisites of the subject
        $directPrerequisites = SubjectPrerequisite::where('subject_id', $subjectId)
            ->pluck('prerequisite_id')
            ->toArray();

        // If target is a direct prerequisite, we found a circular dependency
        if (in_array($targetPrerequisiteId, $directPrerequisites)) {
            return true;
        }

        // Check each direct prerequisite recursively
        foreach ($directPrerequisites as $prerequisiteId) {
            if ($this->hasIndirectPrerequisite($prerequisiteId, $targetPrerequisiteId)) {
                return true;
            }
        }

        return false;
    }
}