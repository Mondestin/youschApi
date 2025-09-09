<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminAcademics\Lab;
use App\Models\AdminAcademics\Subject;
use App\Models\User;

class LabSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔬 Seeding Labs...');

        $labs = [
            // Computer Science Labs
            [
                'subject_code' => 'CS101',
                'name' => 'Laboratoire de Programmation 1',
                'description' => 'Laboratoire informatique pour exercices de programmation de base',
                'schedule' => 'Lundi 14h00 - 16h00',
                'assistant_email' => 'sarah.johnson@yousch.edu',
                'start_datetime' => '2025-01-15 14:00:00',
                'end_datetime' => '2025-01-15 16:00:00',
            ],
            [
                'subject_code' => 'CS101',
                'name' => 'Laboratoire de Programmation 2',
                'description' => 'Laboratoire avancé pour projets de programmation',
                'schedule' => 'Mercredi 10h00 - 12h00',
                'assistant_email' => 'michael.chen@yousch.edu',
                'start_datetime' => '2025-01-17 10:00:00',
                'end_datetime' => '2025-01-17 12:00:00',
            ],
            [
                'subject_code' => 'CS201',
                'name' => 'Laboratoire de Structures de Données',
                'description' => 'Laboratoire pour l\'implémentation de structures de données',
                'schedule' => 'Mardi 14h00 - 16h00',
                'assistant_email' => 'sarah.johnson@yousch.edu',
                'start_datetime' => '2025-01-16 14:00:00',
                'end_datetime' => '2025-01-16 16:00:00',
            ],
            [
                'subject_code' => 'CS301',
                'name' => 'Laboratoire de Base de Données',
                'description' => 'Laboratoire pour la conception et manipulation de bases de données',
                'schedule' => 'Jeudi 10h00 - 12h00',
                'assistant_email' => 'michael.chen@yousch.edu',
                'start_datetime' => '2025-01-18 10:00:00',
                'end_datetime' => '2025-01-18 12:00:00',
            ],
            [
                'subject_code' => 'CS301',
                'name' => 'Laboratoire de Base de Données Avancé',
                'description' => 'Laboratoire pour requêtes complexes et optimisation',
                'schedule' => 'Vendredi 14h00 - 16h00',
                'assistant_email' => 'sarah.johnson@yousch.edu',
                'start_datetime' => '2025-01-19 14:00:00',
                'end_datetime' => '2025-01-19 16:00:00',
            ],
            [
                'subject_code' => 'CS401',
                'name' => 'Laboratoire de Développement Web',
                'description' => 'Laboratoire pour le développement d\'applications web',
                'schedule' => 'Lundi 10h00 - 12h00',
                'assistant_email' => 'michael.chen@yousch.edu',
                'start_datetime' => '2025-01-15 10:00:00',
                'end_datetime' => '2025-01-15 12:00:00',
            ],
            [
                'subject_code' => 'CS401',
                'name' => 'Laboratoire de Développement Mobile',
                'description' => 'Laboratoire pour le développement d\'applications mobiles',
                'schedule' => 'Mercredi 14h00 - 16h00',
                'assistant_email' => 'sarah.johnson@yousch.edu',
                'start_datetime' => '2025-01-17 14:00:00',
                'end_datetime' => '2025-01-17 16:00:00',
            ],
            
            // Mathematics Labs
            [
                'subject_code' => 'MATH101',
                'name' => 'Laboratoire de Calcul',
                'description' => 'Laboratoire pour exercices pratiques de calcul',
                'schedule' => 'Lundi 16h00 - 18h00',
                'assistant_email' => 'michael.chen@yousch.edu',
                'start_datetime' => '2025-01-15 16:00:00',
                'end_datetime' => '2025-01-15 18:00:00',
            ],
            [
                'subject_code' => 'MATH201',
                'name' => 'Laboratoire d\'Algèbre Linéaire',
                'description' => 'Laboratoire pour exercices d\'algèbre linéaire',
                'schedule' => 'Mercredi 14h00 - 16h00',
                'assistant_email' => 'michael.chen@yousch.edu',
                'start_datetime' => '2025-01-17 14:00:00',
                'end_datetime' => '2025-01-17 16:00:00',
            ],
            [
                'subject_code' => 'MATH301',
                'name' => 'Laboratoire de Statistiques',
                'description' => 'Laboratoire pour analyses statistiques et probabilités',
                'schedule' => 'Vendredi 10h00 - 12h00',
                'assistant_email' => 'michael.chen@yousch.edu',
                'start_datetime' => '2025-01-19 10:00:00',
                'end_datetime' => '2025-01-19 12:00:00',
            ],
            
            // Business Labs
            [
                'subject_code' => 'BUS101',
                'name' => 'Laboratoire de Gestion',
                'description' => 'Laboratoire pour simulations de gestion d\'entreprise',
                'schedule' => 'Mardi 10h00 - 12h00',
                'assistant_email' => 'emily.rodriguez@yousch.edu',
                'start_datetime' => '2025-01-16 10:00:00',
                'end_datetime' => '2025-01-16 12:00:00',
            ],
            [
                'subject_code' => 'BUS201',
                'name' => 'Laboratoire de Marketing',
                'description' => 'Laboratoire pour études de cas marketing',
                'schedule' => 'Jeudi 14h00 - 16h00',
                'assistant_email' => 'emily.rodriguez@yousch.edu',
                'start_datetime' => '2025-01-18 14:00:00',
                'end_datetime' => '2025-01-18 16:00:00',
            ],
            [
                'subject_code' => 'BUS301',
                'name' => 'Laboratoire de Finance',
                'description' => 'Laboratoire pour analyses financières et modélisation',
                'schedule' => 'Mardi 16h00 - 18h00',
                'assistant_email' => 'emily.rodriguez@yousch.edu',
                'start_datetime' => '2025-01-16 16:00:00',
                'end_datetime' => '2025-01-16 18:00:00',
            ],
            
            // English Labs
            [
                'subject_code' => 'ENG101',
                'name' => 'Laboratoire de Communication',
                'description' => 'Laboratoire pour exercices de communication orale et écrite',
                'schedule' => 'Vendredi 10h00 - 12h00',
                'assistant_email' => 'david.thompson@yousch.edu',
                'start_datetime' => '2025-01-19 10:00:00',
                'end_datetime' => '2025-01-19 12:00:00',
            ],
            [
                'subject_code' => 'ENG201',
                'name' => 'Laboratoire d\'Écriture Avancée',
                'description' => 'Laboratoire pour techniques d\'écriture avancées',
                'schedule' => 'Jeudi 10h00 - 12h00',
                'assistant_email' => 'david.thompson@yousch.edu',
                'start_datetime' => '2025-01-18 10:00:00',
                'end_datetime' => '2025-01-18 12:00:00',
            ],
            [
                'subject_code' => 'ENG301',
                'name' => 'Laboratoire de Littérature',
                'description' => 'Laboratoire pour analyse littéraire et critique',
                'schedule' => 'Mercredi 16h00 - 18h00',
                'assistant_email' => 'david.thompson@yousch.edu',
                'start_datetime' => '2025-01-17 16:00:00',
                'end_datetime' => '2025-01-17 18:00:00',
            ],
            
            // Additional Labs with different assistants
            [
                'subject_code' => 'CS501',
                'name' => 'Laboratoire d\'Intelligence Artificielle',
                'description' => 'Laboratoire pour projets d\'IA et machine learning',
                'schedule' => 'Lundi 14h00 - 16h00',
                'assistant_email' => 'sarah.johnson@yousch.edu',
                'start_datetime' => '2025-01-22 14:00:00',
                'end_datetime' => '2025-01-22 16:00:00',
            ],
            [
                'subject_code' => 'MATH401',
                'name' => 'Laboratoire d\'Analyse Mathématique',
                'description' => 'Laboratoire pour exercices d\'analyse avancée',
                'schedule' => 'Jeudi 16h00 - 18h00',
                'assistant_email' => 'michael.chen@yousch.edu',
                'start_datetime' => '2025-01-25 16:00:00',
                'end_datetime' => '2025-01-25 18:00:00',
            ],
            [
                'subject_code' => 'BUS401',
                'name' => 'Laboratoire de Stratégie d\'Entreprise',
                'description' => 'Laboratoire pour simulations stratégiques',
                'schedule' => 'Vendredi 14h00 - 16h00',
                'assistant_email' => 'emily.rodriguez@yousch.edu',
                'start_datetime' => '2025-01-26 14:00:00',
                'end_datetime' => '2025-01-26 16:00:00',
            ],
        ];

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($labs as $labData) {
            $subject = Subject::where('code', $labData['subject_code'])->first();
            
            if ($subject) {
                // Check if lab already exists
                $existing = Lab::where('subject_id', $subject->id)
                    ->where('name', $labData['name'])
                    ->first();
                
                if (!$existing) {
                    // Find assistant by email
                    $assistant = User::where('email', $labData['assistant_email'])->first();
                    
                    Lab::create([
                        'subject_id' => $subject->id,
                        'name' => $labData['name'],
                        'description' => $labData['description'],
                        'schedule' => $labData['schedule'],
                        'assistant_id' => $assistant ? $assistant->id : null,
                        'start_datetime' => $labData['start_datetime'],
                        'end_datetime' => $labData['end_datetime'],
                    ]);
                    $createdCount++;
                } else {
                    $skippedCount++;
                }
            } else {
                $this->command->warn("Subject with code '{$labData['subject_code']}' not found. Skipping lab '{$labData['name']}'.");
            }
        }

        $this->command->info("✅ Lab seeding completed: {$createdCount} created, {$skippedCount} skipped");
    }
}