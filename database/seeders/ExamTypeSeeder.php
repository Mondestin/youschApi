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
        $this->command->info('📝 Creating exam types...');

        $examTypes = [
            [
                'name' => 'midterm',
                'description' => 'Midterm examination covering first half of the course',
                'weight' => 30.00,
            ],
            [
                'name' => 'final',
                'description' => 'Final examination covering the entire course',
                'weight' => 50.00,
            ],
            [
                'name' => 'quiz',
                'description' => 'Short quiz covering specific topics',
                'weight' => 10.00,
            ],
            [
                'name' => 'assignment',
                'description' => 'Take-home assignment or project',
                'weight' => 15.00,
            ],
            [
                'name' => 'practical',
                'description' => 'Practical or laboratory examination',
                'weight' => 25.00,
            ],
        ];

        foreach ($examTypes as $examTypeData) {
            ExamType::create($examTypeData);
        }

        $this->command->info('✅ Exam types created successfully');
    }
} 