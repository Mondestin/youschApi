<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamsGradings\ExamType;

class ExamTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📝 Création des types d\'examen...');

        $examTypes = [
            [
                'name' => 'mi-parcours',
                'description' => 'Examen de mi-parcours couvrant la première moitié du cours',
                'weight' => 30.00,
            ],
            [
                'name' => 'final',
                'description' => 'Examen final couvrant l\'ensemble du cours',
                'weight' => 50.00,
            ],
            [
                'name' => 'quiz',
                'description' => 'Quiz court couvrant des sujets spécifiques',
                'weight' => 10.00,
            ],
            [
                'name' => 'devoir',
                'description' => 'Devoir à la maison ou projet',
                'weight' => 15.00,
            ],
            [
                'name' => 'pratique',
                'description' => 'Examen pratique ou de laboratoire',
                'weight' => 25.00,
            ],
        ];

        foreach ($examTypes as $examTypeData) {
            ExamType::create($examTypeData);
        }

        $this->command->info('✅ Types d\'examen créés avec succès');
    }
} 